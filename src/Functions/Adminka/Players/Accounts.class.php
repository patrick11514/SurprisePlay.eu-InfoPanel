<?php

/**
 * Account Class, check if user is logged in,
 * or login user. Run router Class. 
 * One of main Classes.
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Adminka\Players;

use patrick115\Adminka\Logger;
use patrick115\Main\Config;
use patrick115\Main\Database;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;

class Accounts
{
    /**
     * Config class
     * @var object
     */
    private $config;
    /**
     * Session class
     * @var object
     */
    private $session;
    /**
     * Database Class
     * @var object
     */
    private $database;
    /**
     * Logger Class
     * @var object
     */
    private $logger;

    public function __construct()
    {
        $this->config   = Config::init();
        $this->session  = Session::init();
        $this->database = Database::init();
        $this->logger = Logger::init();
    }

    /**
     * Run account verification
     */
    public function run()
    {

        if (!$this->checkLoginStatus()) {
            $router = \patrick115\Adminka\Main::getApp("\patrick115\Main\Router");
            if (!$router->compare_pages("login")) {
                \patrick115\Main\Tools\Utils::header("./?login");
            }
            if (!$this->session->getData("Post/Success", true)) {
                $router->route();
            } else {
                if ($this->session->isExist("Request/Data")) {
                    if ($this->session->isExist("Request/Data/password")) {
                        if (\patrick115\Main\Tools\Utils::newNull($this->session->getData("Request/Data/password"))) {
                            unset($_SESSION["Post"]);
                            $_SESSION["Request"]["Errors"] = [
                                "Toto jméno neexistuje",
                            ];
                            \patrick115\Main\Tools\Utils::header("./?login");
                        } else if (!$this->session->getData("Request/Data/password")) {
                            unset($_SESSION["Post"]);
                            $_SESSION["Request"]["Errors"] = [
                                "Zadal jsi nesprávné heslo",
                            ];
                            \patrick115\Main\Tools\Utils::header("./?login");
                        } else {
                            
                            $this->loginUser($this->session->getData("Request/Data/name"), $this->session->getData("Request/Data/primary_group"));
                        }
                    }
                }
            }
        } else {
            $router = \patrick115\Adminka\Main::getApp("\patrick115\Main\Router");

            if ($router->page_is_null() || $router->compare_pages("login")) {
                \patrick115\Main\Tools\Utils::header("./?main");
            } else {
                $router->route();
            }
        }
    }
    
    /**
     * Check if logged
     */
    private function checkLoginStatus()
    {
        return $this->session->getData("Account/User/Logged", true);
    }

    /**
     * Login user
     * @param string $username
     * @param string $group
     */
    private function loginUser(string $username, string $group)
    {
        session_destroy();
        session_start();
        unset($_POST);

        //Check if admin account
        if (in_array($group, $this->config->getConfig("Main/admin_accounts"))) {
            $_SESSION["Account"]["Admin_Account"] = true;
        } else {
            $_SESSION["Account"]["Admin_Account"] = false;
        }
        $app                                     = \patrick115\Adminka\Main::Create("\patrick115\Minecraft\Skins", [$username]);
        $_SESSION["Account"]["User"]["Skin"]     = $app->getSkin();
        $_SESSION["Account"]["User"]["Logged"]   = true;
        $_SESSION["Account"]["User"]["Username"] = $username;
        $_SESSION["Account"]["User"]["Group"]    = $group;

        $this->logger->log("User $username logged in.", "login");

        $rv      = $this->database->execute("SELECT `id` FROM `main_authme`.`authme` WHERE `realname` = '{$username}'", true);
        $user_id = $rv->fetch_object()->id;

        $rv = $this->database->execute("SELECT `last-ip` AS `lastip`, `ip-list` AS `iplist` FROM `accounts` WHERE `authme_id` = " . $user_id, true);
        if ($this->database->num_rows($rv) == 0) {
            $ips = [
                Utils::getUserIP()
            ];
            $this->database->insert("accounts", ["id", "authme_id", "e-mail", "last-ip", "ip-list"], ["", $user_id, "", Utils::getUserIP(), json_encode($ips)]);
        } else {
            while ($row = $rv->fetch_assoc()) {
                $iplist = json_decode($row["iplist"], 1);
                $lastip = $row["lastip"];
            }

            $cid = $this->database->select(["id"], "accounts", "LIMIT 1", "authme_id", $user_id)->
            fetch_object()->id;

            if ($lastip != Utils::getUserIP()) {
                $this->database->update("accounts", "id", $cid, ["last-ip"], [Utils::getUserIP()]);
            }

            if (!in_array(Utils::getUserIP(), $iplist)) {
                $iplist[] = Utils::getUserIP();
                $iplist = json_encode($iplist);
                $this->database->update("accounts", "id", $cid, ["ip-list"], [$iplist]);
            }
        }

        
        $CSRF = \patrick115\Adminka\Main::Create("\patrick115\Requests\CSRF", []);
        $CSRF->newToken();


        session_write_close();

        \patrick115\Main\Tools\Utils::header("./");
    }
}

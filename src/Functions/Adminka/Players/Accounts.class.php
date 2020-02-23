<?php

namespace patrick115\Adminka\Players;

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

    private $database;

    public function __construct()
    {
        $this->config   = Config::init();
        $this->session  = Session::init();
        $this->database = Database::init();
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
                            
                            $this->loginUser($this->session->getData("Request/Data/name"), $this->session->getData("Request/Data/primary_group"), \patrick115\Main\Tools\Utils::getPackage(
                                $this->session->getdata("Request/FromPost/password")
                            ));
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

    private function checkLoginStatus()
    {
        return $this->session->getData("Account/User/Logged", true);
    }

    /**
     * Login user
     * @param string $username
     * @param string $group
     * @param string $lenght - passlength
     */
    private function loginUser($username, $group, $length)
    {

        echo $length;

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

        $rv      = $this->database->execute("SELECT `id` FROM `main_authme`.`authme` WHERE `realname` = '{$username}'", true);
        $user_id = $rv->fetch_object()->id;

        $rv = $this->database->execute("SELECT `id` FROM `accounts` WHERE `authme_id` = " . $user_id, true);
        if ($this->database->num_rows($rv) == 0) {
            $this->database->insert("accounts", ["id", "authme_id", "e-mail", "last-ip", "ip-list"], ["", $user_id, "", Utils::getUserIP(), "{}"]);
        }

        $cid = $this->database->select(["id"], "accounts", "LIMIT 1", "authme_id", $user_id)->
            fetch_object()->id;
        $rv = $this->database->select(["pass_length"], "pass_storage", "LIMIT 1", "user_id", $cid);
        if ($this->database->num_rows($rv) == 0) {
            $this->database->insert("pass_storage", ["id", "user_id", "pass_length"], ["", $cid, mb_strlen($length)]);
        } else {
            if (mb_strlen($length) != $rv->fetch_object()->pass_length) {
                $this->database->update("pass_storage", "user_id", $cid, ["pass_length"], [mb_strlen($length)]);
            }
        }

        session_write_close();

        \patrick115\Main\Tools\Utils::header("./");
    }
}

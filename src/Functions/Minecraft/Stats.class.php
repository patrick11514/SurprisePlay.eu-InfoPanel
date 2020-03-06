<?php

namespace patrick115\Minecraft;

use patrick115\Adminka\Main;
use patrick115\Main\Database;
use patrick115\Main\Error;
use patrick115\Main\Config;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;

class Stats
{
    private $database;
    private $config;
    private $session;
    private $error;

    private $username;


    public function __construct($username)
    {
        $this->database = Database::init();
        $this->config = Config::init();
        $this->session = Session::init();
        $this->error = Error::init();
                
        $this->username = $username;
    }

    public function getRegisteredUsers()
    {
        return $this->database->getCountRows("main_authme`.`authme");
    }

    public function getBannedUsers()
    {
        return $this->database->execute("SELECT COUNT(*) as `bany` FROM `main_bans`.`litebans_bans` WHERE `active` = 1", true)->fetch_object()->bany;
    }

    public function getAllVotes()
    {
        return $this->database->execute("SELECT SUM(`votifier`) AS `votes` FROM `survival_cmi`.`cmi_users`", true)->fetch_object()->votes;
    }

    public function getGlobalCurrency()
    {
        return \patrick115\Main\Tools\Utils::fixCurrency(
            $this->database->execute(
                "SELECT SUM(`Balance`) AS `money` FROM `survival_cmi`.`cmi_users`", 
                true
            )
            ->
            fetch_object()->money
            );
    }

    public function getAutologinStatus()
    {
        $rv = $this->database->select(["Premium"], "main_autologin`.`premium", "LIMIT 1", "Name", $this->username);
        $premium = @$rv->fetch_object()->Premium;
        if (Utils::newEmpty($premium)) return "Error";
        if ($premium == "1") {
            return "Zapnut";
        } 
        return "Vypnut";
    }

    public function getAntiVPNStatus()
    {
        $rv = $this->database->select(["uuid"], "main_perms`.`perms_players", "LIMIT 1", "username", strtolower($this->username));
        $uuid = @$rv->fetch_object()->uuid;
        if (empty($uuid)) return "Error";
        $rv = $this->database->execute("SELECT `value` FROM `main_perms`.`perms_user_permissions` WHERE `uuid` = '{$uuid}' AND `permission` = 'antiproxy.proxy' LIMIT 1;", true);
        if ($this->database->num_rows($rv) > 0) {
            return "Povolen";
        }
        return "Zakázan";
    }

    public function getMoney()
    {
        $rv = $this->database->select(["Balance"], "survival_cmi`.`cmi_users", "LIMIT 1", "username", $this->username);
        $balance = @$rv->fetch_object()->Balance;
        if (empty($balance)) return "Error";
        return \patrick115\Main\Tools\Utils::fixCurrency($balance);
    }

    public function getVipExpiry()
    {
        $rank = Main::Create("\patrick115\Adminka\Players\Rank", [$this->username])->getRank();
        if (!in_array(strtolower($rank), $this->config->getConfig("Main/vips"))) {
            return "Nevlastníš VIP";
        }
        $rv = $this->database->select(["uuid"], "main_perms`.`perms_players", "LIMIT 1", "username", strtolower($this->username));
        $uuid = @$rv->fetch_object()->uuid;

        if (empty($uuid)) return "Error";
        $rv = $this->database->execute("SELECT `expiry` FROM `main_perms`.`perms_user_permissions` WHERE `uuid` = '" . $uuid . "' AND `permission` = 'group." . strtolower($rank) . "';", true);
        $expiry = @$rv->fetch_object()->expiry;

        if (Utils::newEmpty($expiry)) return "Error";
        if ($expiry == 0) {
            return "Nikdy";
        }
        return \patrick115\Main\Tools\Utils::fixDate(
            $expiry
            ) . " (" . 
            \patrick115\Main\Tools\Utils::dateDiff(
                $expiry, 
                time()
            ) . ")";
    }

    public function getEMail()
    {
        $user_id = Utils::getAuthmeIDByName($this->username);
        $email = $this->database->execute("SELECT `e-mail` AS `email` FROM `accounts` WHERE `authme_id` = '{$user_id}' LIMIT 1", true);
        if ($this->database->num_rows($email) == 0) {
            \patrick115\Main\Error::init()->catchError("Your record in global database not found, please contact Administrators!", debug_backtrace());
            return;
        }
        $email = @$email->fetch_object()->email;
        if (empty($email)) {
            return "Nenastaven";
        }
        return $email;
    }

    public function getUserPassword()
    {
        $cid = Utils::getClientID($this->username);
        $rv = $this->database->select(["pass_length"], "pass_storage", "LIMIT 1", "user_id", $cid);
        return $rv->fetch_object()->pass_length;
    }

    public function getUserData()
    {
        $data = $this->config->getConfig("Main/player_info");
        
        $return = "";
        foreach ($data as $info) {
            $_name = $info["name"];
            switch (strtolower($info["source"]["source_name"])) {
                case "session":
                    $_data = $this->session->getData($info["source"]["data"]);
                break;
                case "function":
                    if (!empty($info["source"]["create_param"])) {
                        switch (strtolower($info["source"]["create_param"]["from"])) {
                            case "session":
                                $param = $this->session->getData($info["source"]["create_param"]["data"]);
                            break;
                        }
                    } else {
                        $param = null;
                    }
                    $app = Main::Create($info["source"]["class"], [$param]);
                    $function = $info["source"]["function"];
                    $_data = $app->$function();
                break;
                case "database":
                    $prepare_command = $info["source"]["command"];
                    foreach ($info["source"]["vars"] as $var_name => $var_data) {
                        switch (strtolower($var_data["from"])) {
                            case "session":
                                $prepare_command = str_replace($var_name, $this->session->getData($var_data["data"]), $prepare_command);
                            break;
                        }
                    }
                    $rv = $this->database->execute($prepare_command, true);
                    $get = $info["source"]["select"];
                    $_data = @$rv->fetch_object()->$get;
                    if (empty($_data)) $_data = "Error";
                break;
            }
            $return .= "<tr>
            <th style=\"font-weight: normal;\">{$_name}:</th>
            <th>{$_data}</th>
        </tr>";
        }
        return $return;
    }

}

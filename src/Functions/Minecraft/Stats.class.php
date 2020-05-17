<?php

/**
 * Statistics about player
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Minecraft;

use patrick115\Adminka\Main;
use patrick115\Main\Database;
use patrick115\Main\Error;
use patrick115\Main\Config;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;

class Stats
{
    /**
     * Database class
     * @var object
     */
    private $database;
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
     * Error class
     * @var object
     */
    private $error;

    /**
     * Username of user
     * @var string
     */
    private $username;

    /**
     * Construct function 
     * @param string $username
     */
    public function __construct(string $username)
    {
        $this->database = Database::init();
        $this->config = Config::init();
        $this->session = Session::init();
        $this->error = Error::init();
                
        $this->username = $username;
    }

    /**
     * Get main informations about server
     * by config
     * @return string
     */
    public function getInfo()
    {
        $data = $this->config->getConfig("Main/server_info");
        
        $return = "";
        foreach ($data as $info) {
            $_name = $info["name"];
            $_color = $info["color"];
            $_icon = $info["icon"];
            if (strtolower($info["source"]["source_name"]) != "multiple") {
                $arr = [0 => $info];
                $type = "single";
            } else {
                $arr = $info["source"]["multiple"];
                $type = "multiple";
                $_mdata = 0;
                $_operator = $info["source"]["operator"];
                $_currency = (!empty($info["source"]["currency"])) ? $info["source"]["currency"] : false;
            }
            foreach ($arr as $info) {
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
                        if (!empty($info["source"]["vars"])) {
                            foreach ($info["source"]["vars"] as $var_name => $var_data) {
                                switch (strtolower($var_data["from"])) {
                                    case "session":
                                        $prepare_command = str_replace($var_name, $this->session->getData($var_data["data"]), $prepare_command);
                                    break;
                                }
                            }
                        }
                        $rv = $this->database->execute($prepare_command, true);
                        $get = $info["source"]["select"];
                        $_data = @$rv->fetch_object()->$get;
                        if (!empty($info["source"]["currency"]) && $info["source"]["currency"] === true) {
                            $_data = Utils::fixCurrency($_data);
                        }
                        if (Utils::newNull($_data)) $_data = "Error";
                    break;
                }
                if ($type == "multiple") {
                    switch ($_operator) {
                        case "+":
                            $_mdata = $_mdata + $_data;
                        break;
                        case "-":
                            $_mdata = $_mdata - $_data;
                        break;
                        case "*":
                            $_mdata = $_mdata * $_data;
                        break;
                        case "/":
                            $_mdata = $_mdata / $_data;
                        break;
                        default:
                            $this->error->catchError("Invalid operator {$_operator}!", debug_backtrace());
                            return false;
                        break;
                    }
                    
                }
            }
            if ($type == "multiple") {
                $_data = $_mdata;
                if ($_currency === true) {
                    $_data = Utils::fixCurrency($_data);
                }
            }
            $return .= "<div class=\"col-md-3 col-sm-6 col-12\">
            <div class=\"info-box {$_color}\">
                <span class=\"info-box-icon\"><i class=\"{$_icon}\"></i></span>
                <div class=\"info-box-content\">
                    <span class=\"text\">{$_name}</span>
                    <span class=\"number\">{$_data}</span>
                </div>
            </div>
        </div>";
        }
        return $return;
    }

    /**
     * Get autologin status
     * @return string
     */
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

    /**
     * Get Allow VPN status
     * @return string
     */
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

    /**
     * Get user money
     * @return string
     */
    public function getMoney()
    {
        $rv = $this->database->select(["Balance"], "survival_cmi`.`cmi_users", "LIMIT 1", "username", $this->username);
        $balance = @$rv->fetch_object()->Balance;
        if (empty($balance)) return "Error";
        return \patrick115\Main\Tools\Utils::fixCurrency($balance);
    }

    /**
     * Get date when VIP expiry
     * @return string
     */
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

    /**
     * Get user e-mail
     * @return string
     */
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

    /**
     * Get length of user password
     * @return int
     */
    public function getUserPassword()
    {
        $cid = Utils::getClientID($this->username);
        $rv = $this->database->select(["pass_length"], "pass_storage", "LIMIT 1", "user_id", $cid);
        return $rv->fetch_object()->pass_length;
    }

    /**
     * Get count of gemgs
     * @return int
     */
    public function getGems()
    {
        $rv = $this->database->select(["value"], "main_kredity`.`supercredits", "LIMIT 1", "name", $this->username);
        return $rv->fetch_object()->value;
    }

    /**
     * Get informations about user, by config
     * @return string
     */
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
                    if (!empty($info["source"]["currency"]) && $info["source"]["currency"] === true) {
                        $_data = Utils::fixCurrency($_data);
                    }
                    if (Utils::newNull($_data)) $_data = "Error";
                break;
            }
            $return .= "<tr>
            <th style=\"font-weight: normal;\">{$_name}:</th>
            <th>{$_data}</th>
        </tr>";
        }
        return $return;
    }

    /**
     * Check if user is banned
     * @return string
     */
    public function isBanned()
    {
        $uuid = Utils::getUUIDByNick($this->username);

        $rv = $this->database->select(["id"], "main_bans`.`litebans_bans", "AND `active` = 1 LIMIT 1", "uuid", $uuid);

        if (!$rv ||$this->database->num_rows($rv) == 0) {
            $rv = $rv = $this->database->select(["id"], "main_bans`.`litebans_bans", "AND `active` = 1 LIMIT 1", "ip", Utils::getIpOfUser($this->username));

            if (!$rv ||$this->database->num_rows($rv) == 0) {
                return "Ne";
            }
            return "Ano";
        }
        return "Ano";
    }
}

<?php

/**
 * Settings class, change settings based
 * on user changed. If settings don't changed
 * it does nothing, than it change settings
 * in database.
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Adminka\Players;

use Exception;
use patrick115\Adminka\Logger;
use patrick115\Adminka\Main;
use patrick115\Main\Error;
use patrick115\Main\Session;
use patrick115\Main\Database;
use patrick115\Main\Tools\Utils;

class Settings
{
    /**
     * Session Class
     * @var object
     */
    private $session;
    /**
     * Logger Class
     * @var object
     */
    private $logger;
    /**
     * Database Class
     * @var object
     */
    private $database;

    /**
     * Data from POST
     * @var array
     */
    private $data = [];

    /**
     * Array for check
     * @var array
     */
    private $settings_datas = [
        "settings" => [
            "autologin",
            "e-mail",
            "password"
        ],
        "unregister" => [
            "username"
        ],
        "gems" => [
            "gems-nick",
            "gem-count",
            "gem-action",
        ],
        "removeVPN" => [
            "id"
        ],
        "unban" => [
            "nick",
            "reason"
        ]
    ];

    /**
     * Construct Function
     * 
     * @param array $data - Data from POST
     */
    public function __construct(array $data)
    {
        foreach ($this->settings_datas[$data["method"]] as $datas) {
            if (empty($data[$datas])) {
                define("ERROR", ["Can't find $datas in got data."]);
                return false;
            }
        }
        foreach ($data as $name => $dat) {
            $this->data[Utils::chars($name)] = Utils::chars($dat);
        }
        $this->session = Session::init();
        $this->database = Database::init();
        $this->logger = Logger::init();
    }

    /**
     * Remove VPN from user
     * 
     * @return bool
     */
    public function removeVPN()
    {
        $id = explode(";", Utils::getPackage([1 => $this->data["id"]]))[1];
        $uuid = Utils::getUUIDByNick($id);
    
        $this->database->delete("main_perms`.`perms_user_permissions", ["uuid", "permission"], [$uuid, "antiproxy.proxy"]);
        return true;
    }

    /**
     * Unregister user
     * 
     * @return bool
     */
    public function unregister()
    {
        $username = $this->data["username"];

        $newpassword = Utils::randomString(16);

        $hash = Utils::hashPassword($newpassword, "sha256");

        $rv = $this->database->select(["realname"], "main_authme`.`authme", "LIMIT 1", "username", $username);
        if (!$rv) {
            return false;
        }
        if ($this->database->num_rows($rv) == 0) {
            return false;
        }

        $username = $rv->fetch_object()->realname;

        $this->database->update("main_authme`.`authme", "realname", $username, ["password"], [$hash]);

        $admin = $this->session->getData("Account/User/Username");

        $this->database->insert("unregister-log", 
        [
            "id", 
            "user_id", 
            "admin", 
            "unregistered", 
            "timestamp", 
            "date"
        ], 
        [
            "", 
            Utils::getClientID($admin),
            $admin,
            $username,
            time(),
            date("H:i:s d.m.Y")
        ]);

        define("MESSAGE", ["Hráč odregistrován, jeho nové heslo je: {$newpassword}!"]);
        return true;
    }

    /**
     * Allow access with VPN on server
     * 
     * @return bool
     */
    public function allowVPN()
    {
        $username = $this->data["username"];

        $stats = Main::Create("\patrick115\Minecraft\Stats", [$username]);
        $status = $stats->getAntiVPNStatus();

        if ($status == "Povolen") {
            define("ERROR", ["Tento hráč již má povolený přístup s VPN!"]);
            return false;
        }

        $uuid = Utils::getUUIDByNick($username);

        if ($uuid == "00000000-0000-0000-0000-000000000000") {
            return false;
        }
        $this->database->insert("main_perms`.`perms_user_permissions", [
            "id", 
            "uuid", 
            "permission", 
            "value", 
            "server", 
            "world", 
            "expiry", 
            "contexts"
        ],
        [
            "",
            $uuid,
            "antiproxy.proxy",
            "1",
            "global",
            "global",
            "0",
            "{}"
        ]);

        $executor = $this->session->getData("Account/User/Username");
        $this->logger->log("{$executor} enabled AntiVPN for {$username}!", "info");
        define("MESSAGE", ["VPN úspěšně povolena!"]);
        return true;
    }

    /**
     * Remove or Give Gems for user
     * 
     * @return bool
     */
    public function gems()
    {
        $admin = $this->session->getData("Account/User/Username");
        $player = $this->data["gems-nick"];
        $amount = $this->data["gem-count"];
        $method = $this->data["gem-action"];

        $methods = ["add", "remove"];

        if (!in_array($method, $methods)) {
            define("ERROR", ["Neplatná metoda!"]);
            return false;
        }

        if (!is_numeric($amount)) {
            define("ERROR", ["{$amount} není číslo!"]);
            return false;
        }

        if ($amount == 0) {
            define("ERROR", ["Hodnota nesmí být číslo!"]);
            return false;
        }

        $rv = $this->database->select(["value"], "main_kredity`.`supercredits", "LIMIT 1", "name", strtolower($player));
        $credits = $rv->fetch_object()->value;

        if ($method == "remove") {
            if ($amount > $credits) {
                define("ERROR", ["Hráč má pouze {$credits} gemů, proto nelze odebrat {$amount} gemů"]);
                return false;
            }
            if ($amount < 0) {
                define("ERROR", ["Počet gemů musí být větší než 0"]);
                return false;
            }
            $new_gems = $credits - $amount;
        } else {
            if ($amount < 0) {
                define("ERROR", ["Počet gemů musí být větší než 0"]);
                return false;
            }
            $new_gems = $credits + $amount;
        }

        $this->database->update("main_kredity`.`supercredits", "name", strtolower($player), ["value"], [$new_gems]);

        $this->database->insert("gems-log", [
            "id", 
            "user_id", 
            "admin", 
            "nick", 
            "amount", 
            "method", 
            "timestamp", 
            "date"
        ],
        [
            "",
            Utils::getClientID($admin),
            $admin,
            $player,
            $amount,
            $method,
            time(),
            date("H:i:s d.m.Y")
        ]);
        
        $message = ($method == "remove") ? "Úspěšně odebráno {$amount} gemů hráči {$player}!" : "Úspěšně přidáno {$amount} gemů hráči {$player}!";

        define("MESSAGE", [$message]);
        return true;
    }

    /**
     * Check settings, if settings is changed,
     * function update it in database
     * 
     * @return bool
     */
    public function checkSettings()
    {
        $username = $this->session->getData("Account/User/Username");

        $user_data = Main::Create("\patrick115\Minecraft\Stats", [$username]);

        $message = [];

        $is_changed = false;
        
        //PASSWORD

        if (!empty(trim($this->data["password"], "*"))) {
            $is_changed = true;
            if (strpos($this->data["password"], "*") === false) {
                if (mb_strlen($this->data["password"]) < 8) {
                    define("ERROR", ["Heslo musí mít minimálně 8 znaků, aby bylo bezpečné!"]);
                    return false;
                }
                $hash = Utils::hashPassword($this->data["password"], "sha256");
                $this->database->update("main_authme`.`authme", "realname", $username, ["password"], [$hash]);

                $message[] = "Změna hesla proběhla úspěšně, přihlaš se prosím s novým heslem.";

                $this->logger->log("{$username} changed password!", "settings");

                define("DELETE_SESSION", true);
                define("MESSAGE", $message);
            } else {
                define("ERROR", ["Heslo nesmí obsahovat speciální znaky"]);
                return false;
            }
        }

        //AUTOLOGIN

        switch ($user_data->getAutologinStatus()) {
            case "Zapnut":
                $curr_autologin = "allow";
            break;
            default:
                $curr_autologin = "disallow";
            break;
        }
        if ($this->data["autologin"] != $curr_autologin) {
            $is_changed = true;
            switch ($this->data["autologin"]) {
                case "allow":
                    $this->database->update("main_autologin`.`premium", "Name", $username, ["Premium"], [1]);
                    $this->logger->log("{$username} changed autologin to Enabled", "settings");
                break;
                default:
                    $this->database->update("main_autologin`.`premium", "Name", $username, ["Premium"], [0]);
                    $this->logger->log("{$username} changed autologin to Disabled", "settings");
                break;
            }
            
        }

        //E-MAIL

        if ($this->data["e-mail"] != $user_data->getEMail()) {
            if (filter_var($this->data["e-mail"], FILTER_VALIDATE_EMAIL)) {
                if (substr_count(explode("@", $this->data["e-mail"])[1], ".") > 1) {
                    define("ERROR", ["E-mail je neplatný!"]);
                    return true;
                }
                $is_changed = true;
                $aid = Utils::getAuthmeIDByName($username);
                $this->logger->log("{$username} set e-mail to {$this->data["e-mail"]}", "settings");
                $this->database->update("accounts", "authme_id", $aid, ["e-mail"], [$this->data["e-mail"]]);
                $this->database->update("main_authme`.`authme", "realname", $username, ["email"], [$this->data["e-mail"]]);
            }               
        }

        //RESET-SKINU

        if ($this->data["skin"] != "none") {
            $username = $this->session->getData("Account/User/Username");
            unlink(Main::getWorkDirectory() . "src/cache/{$username}");
            $is_changed = true;

            define("DELETE_SESSION", true);
            define("MESSAGE", ["Skin úpsěšně smazán, přihlaš se pro nové načtení skinu."]);
        }

        if ($is_changed === false) return false;
        return true;
    }

    /**
     * Remove ban from user
     * 
     * @return bool
     */
    public function unban()
    {
        $username = $this->data["nick"];

        $stats = Main::Create("\patrick115\Minecraft\Stats", [$username]);
        $banned = $stats->isBanned();

        if ($banned == "Ne") {
            define("ERROR", ["Tento hráč není zabanovan"]);
            return false;
        }

        $uuid = Utils::getUUIDByNick($username);

        $this->database->update("main_bans`.`litebans_bans", ["uuid", "active"], [$uuid, 1], ["active", "removed_by_name"], [0, $this->session->getData("Account/User/Username")]);

        $this->database->insert("unbans", 
        [
            "id",
            "unbanner",
            "player",
            "reason",
            "date",
            "timestamp"
        ],
        [
            "",
            Utils::getClientID($this->session->getData("Account/User/Username")),
            Utils::getClientID($username),
            $this->data["reason"],
            date("H:i:s d.m.Y"),
            time()
        ]);

        return true;
    }
}

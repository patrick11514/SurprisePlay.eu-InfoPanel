<?php

namespace patrick115\Adminka\Players;

use patrick115\Adminka\Logger;
use patrick115\Adminka\Main;
use patrick115\Main\Error;
use patrick115\Main\Session;
use patrick115\Main\Database;
use patrick115\Main\Tools\Utils;

class Settings
{

    private $session;
    private $logger;
    private $database;

    private $data = [];

    private $settings_datas = [
        "autologin",
        "e-mail",
        "password"
    ];

    public function __construct($data)
    {
        foreach ($this->settings_datas as $datas) {
            if (empty($data[$datas])) {
                Error::init()->catchError("Can't find $datas in got data.", debug_backtrace());
            }
        }
        foreach ($data as $name => $dat) {
            $this->data[Utils::chars($name)] = Utils::chars($dat);
        }
        $this->session = Session::init();
        $this->database = Database::init();
        $this->logger = Logger::init();
    }

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

    public function allowVPN()
    {
        $username = $this->data["username"];

        $stats = Main::Create("\patrick115\Minecraft\Stats", [$username]);
        $status = $stats->getAntiVPNStatus();

        if ($status == "Povolen") {
            define("MESSAGE", ["<span style=\"color:red\">Tento hráč již má VPN!</span>"]);
            return true;
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

    public function checkSettings()
    {
        $username = $this->session->getData("Account/User/Username");

        $user_data = Main::Create("\patrick115\Minecraft\Stats", [$username]);

        $message = [];

        $is_changed = false;

        if ($this->data["password"] != Utils::createDots($user_data->getUserPassword())) {
            $is_changed = true;
            if (strpos($this->data["password"], "*") === false) {
                $hash = Utils::hashPassword($this->data["password"], "sha256");
                $this->database->update("main_authme`.`authme", "realname", $username, ["password"], [$hash]);

                $message[] = "Změna hesla proběhla úspěšně, přihlaš se prosím s novým heslem.";

                $this->logger->log("{$username} changed password!", "settings");

                define("DELETE_SESSION", true);
                define("MESSAGE", $message);
            } else {
                $_SESSION["Request"]["Errors"][] = "Heslo nesmí obsahovat speciální znaky";
                return false;
            }
        }

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

        if ($this->data["e-mail"] != $user_data->getEMail()) {
            if (filter_var($this->data["e-mail"], FILTER_VALIDATE_EMAIL)) {
                if (substr_count(explode("@", $this->data["e-mail"])[1], ".") > 1) {
                    $_SESSION["Request"]["Errors"][] = "E-mail je neplatný!";
                    return false;
                }
                $is_changed = true;
                $aid = Utils::getAuthmeIDByName($username);
                $this->logger->log("{$username} set e-mail to {$this->data["e-mail"]}", "settings");
                $this->database->update("accounts", "authme_id", $aid, ["e-mail"], [$this->data["e-mail"]]);
                $this->database->update("main_authme`.`authme", "realname", $username, ["email"], [$this->data["e-mail"]]);
            }               
        }

        if ($is_changed === false) return false;
        return true;
    }
}

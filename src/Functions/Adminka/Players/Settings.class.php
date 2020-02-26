<?php

namespace patrick115\Adminka\Players;

use patrick115\Adminka\Main;
use patrick115\Main\Error;
use patrick115\Main\Session;
use patrick115\Main\Database;
use patrick115\Main\Tools\Utils;

class Settings
{

    private $session;

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
                break;
                default:
                    $this->database->update("main_autologin`.`premium", "Name", $username, ["Premium"], [0]);
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
                $this->database->update("accounts", "authme_id", $aid, ["e-mail"], [$this->data["e-mail"]]);
                $this->database->update("main_authme`.`authme", "realname", $username, ["email"], [$this->data["e-mail"]]);
            }               
        }

        if ($is_changed === false) return false;
        return true;
    }
}

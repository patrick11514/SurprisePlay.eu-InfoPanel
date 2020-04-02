<?php

namespace patrick115\Minecraft;

use patrick115\Main\Database;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Logger;

class ChangeData
{

    private $database;
    private $session;

    private $data;

    public function __construct($data)
    {
        $this->session = Session::init();
        $this->database = Database::init();

        foreach ($data as $name => $value) {
            $this->data[Utils::chars($name)] = Utils::chars($value);
        }
    }

    public function changeData()
    {
        if (empty($this->data["from-nick"])) {
            define("ERROR", ["Nezadal jsi nick z kterého chceš převést data"]);
            return false;
        }
        if (empty($this->data["to-nick"])) {
            define("ERROR", ["Nezadal jsi nick kterému chceš převést data"]);
            return false;
        }

        $from_nick = $this->data["from-nick"];
        $to_nick = $this->data["to-nick"];

        $from_uuid = Utils::getUUIDByNick($from_nick);
        $to_uuid = Utils::getUUIDByNick($to_nick);
        //tagy
        $this->database->update("main_perms`.`perms_user_permissions", "uuid", $from_uuid, ["uuid"], [$to_uuid], "AND `permission` REGEXP 'deluxetags.tag.'");

        //vip
        $this->database->update("main_perms`.`perms_user_permissions", "uuid", $from_uuid, ["uuid"], [$to_uuid], "AND `permission` REGEXP 'group.'");


        $logger = Logger::init();

        $logger->log("Přesunul data z hráče {$from_nick} na hráče {$to_nick}.", "transfer_data");

        return true;
    }
}
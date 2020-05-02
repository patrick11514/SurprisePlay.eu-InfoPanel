<?php

/**
 * Change group, tags or VIP between users
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Minecraft;

use patrick115\Main\Database;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Logger;

class ChangeData
{
    /**
     * Database class
     * @var object 
     */
    private $database;
    /**
     * Session class
     * @var object 
     */
    private $session;

    /**
     * Data from Post
     * @var array
     */
    private $data;

    /**
     * Construct function
     * @param array $data - Data from Post
     */
    public function __construct(array $data)
    {
        $this->session = Session::init();
        $this->database = Database::init();

        foreach ($data as $name => $value) {
            $this->data[Utils::chars($name)] = Utils::chars($value);
        }
    }

    /**
     * Change data between users
     * @return bool 
     */
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

        $type = @explode(";", @Utils::getPackage($this->data["type"]))[1];

        if (empty($type)) {
            define("ERROR", ["Nevybral jsi metodu, kterou chceš data převést"]);
            return false;
        } 

        if (!in_array($type, ["VIP", "Tags", "Group", "VIPTags", "GroupTags"])) {
            define("ERROR", ["Neplatná metoda!"]);
            return false;
        }

        $from_nick = $this->data["from-nick"];
        $to_nick = $this->data["to-nick"];

        $from_uuid = Utils::getUUIDByNick($from_nick);
        $to_uuid = Utils::getUUIDByNick($to_nick);

        $logger = Logger::init();

        $rv = $this->database->select(["id"], "main_perms`.`perms_players", "LIMIT 1", "username" , strtolower($from_nick));
        if (!$rv || $this->database->num_rows($rv) == 0) {
            define("ERROR", ["Nelze najít hráče s tímto nickem! (from)"]);
            return false;
        }
        $rv = $this->database->select(["id"], "main_perms`.`perms_players", "LIMIT 1", "username" , strtolower($to_nick));
        if (!$rv || $this->database->num_rows($rv) == 0) {
            define("ERROR", ["Nelze najít hráče s tímto nickem! (to)"]);
            return false;
        }

        switch ($type) {
            case "VIP":
                $this->database->update("main_perms`.`perms_user_permissions", "uuid", $from_uuid, ["uuid"], [$to_uuid], "AND `permission` REGEXP 'group.'");
                $logger->log("Přesunul VIP z hráče {$from_nick} na hráče {$to_nick}.", "transfer_data");
            break;
            case "Tags":
                $this->database->update("main_perms`.`perms_user_permissions", "uuid", $from_uuid, ["uuid"], [$to_uuid], "AND `permission` REGEXP 'deluxetags.tag.'");
                $logger->log("Přesunul Tagy z hráče {$from_nick} na hráče {$to_nick}.", "transfer_data");
            break;
            case "Group":
                $rv = $this->database->select(["primary_group"], "main_perms`.`perms_players", "LIMIT 1", "username", strtolower($from_nick));
                if (!$rv || $this->database->num_rows($rv) == 0) {
                    define("ERROR", ["Nelze najít hráče s tímto nickem!"]);
                    return false;
                }

                $group = $rv->fetch_object()->primary_group;

                $this->database->update("main_perms`.`perms_players", "username", strtolower($to_nick), ["primary_group"], [$group]);
                $this->database->update("main_perms`.`perms_players", "username", strtolower($from_nick), ["primary_group"], ["default"]);

                $logger->log("Přesunul Group z hráče {$from_nick} na hráče {$to_nick}.", "transfer_data");
            break;
            case "VIPTags":
                //VIP
                $this->database->update("main_perms`.`perms_user_permissions", "uuid", $from_uuid, ["uuid"], [$to_uuid], "AND `permission` REGEXP 'group.'");
                //Tagy
                $this->database->update("main_perms`.`perms_user_permissions", "uuid", $from_uuid, ["uuid"], [$to_uuid], "AND `permission` REGEXP 'deluxetags.tag.'");
                $logger->log("Přesunul VIP a Tagy z hráče {$from_nick} na hráče {$to_nick}.", "transfer_data");
            break;
            case "GroupTags":
                //Group
                $rv = $this->database->select(["primary_group"], "main_perms`.`perms_players", "LIMIT 1", "username", strtolower($from_nick));
                if (!$rv || $this->database->num_rows($rv) == 0) {
                    define("ERROR", ["Nelze najít hráče s tímto nickem!"]);
                    return false;
                }

                $group = $rv->fetch_object()->primary_group;

                $this->database->update("main_perms`.`perms_players", "username", strtolower($to_nick), ["primary_group"], [$group]);
                $this->database->update("main_perms`.`perms_players", "username", strtolower($from_nick), ["primary_group"], ["default"]);
                //Tagy
                $this->database->update("main_perms`.`perms_user_permissions", "uuid", $from_uuid, ["uuid"], [$to_uuid], "AND `permission` REGEXP 'deluxetags.tag.'");
                $logger->log("Přesunul Group a Tagy z hráče {$from_nick} na hráče {$to_nick}.", "transfer_data");
            break;
        }

        return true;
    }
}
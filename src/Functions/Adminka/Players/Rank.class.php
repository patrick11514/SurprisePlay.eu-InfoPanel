<?php

namespace patrick115\Adminka\Players;

use patrick115\Main\Database;
use patrick115\Main\Config;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;

class Rank
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
     * Username
     * @var string
     */
    private $username;

    /**
     * Construct function
     * @param string $username
     */
    public function __construct($username)
    {
        $this->database = Database::init();
        $this->config = Config::init();
        $this->username = $username;
    }

    /**
     * Get rank
     * @return string
     */
    public function getRank()
    {
        $current_rank = $this->getCurrentRank();
        $current_rank_raw = Utils::ConvertRankToRaw($current_rank);
        $session = Session::init();
        $session_rank = $session->getData("Account/User/Group");
        
        if ($session_rank != $current_rank_raw) {
            $_SESSION["Account"]["User"]["Group"] = $current_rank_raw;
        }
        
        if (in_array($current_rank_raw, $this->config->getConfig("Main/admin_accounts"))) {
            $current_admin_permission = true;
        } else {
            $current_admin_permission = false;
        }

        $admin_permission = $_SESSION["Account"]["Admin_Account"];

        if ($admin_permission != $current_admin_permission) {
            $_SESSION["Account"]["Admin_Account"] = $current_admin_permission;
        }
        return $current_rank;
    }

    private function getCurrentRank()
    {
        $rv = $this->database->select(["primary_group"], "main_perms`.`perms_players", "LIMIT 1", "username", strtolower($this->username));
        $_group = $rv->fetch_object()->primary_group;
        if ($_group == "default") {
            $rv = $this->database->select(["uuid"], "main_perms`.`perms_players", "LIMIT 1", "username", strtolower($this->username));
            $uuid = $rv->fetch_object()->uuid;
            $rv = $this->database->execute("SELECT `permission` FROM `main_perms`.`perms_user_permissions` WHERE `uuid` = '{$uuid}' AND `permission` REGEXP 'group.*'", true);
            $possible_groups = [];
            while($row = $rv->fetch_assoc()) {
                if ($row["permission"] != "group.default") {
                    array_push($possible_groups, ltrim($row["permission"], "group."));
                }
            }

            $groups_w_index = [];
            foreach ($possible_groups as $possible_group) {
                $groups_w_index[array_search($possible_group, $this->config->getConfig("Main/vip_levels"))] = $possible_group;
            }
            ksort($groups_w_index);
            if (empty($groups_w_index)) {
                return $this->config->getConfig("Main/group_names")["default"];
            } 
            return $this->config->getConfig("Main/group_names")[reset($groups_w_index)];
        } else {
            return $this->config->getConfig("Main/group_names")[$_group];
        }
    }

}
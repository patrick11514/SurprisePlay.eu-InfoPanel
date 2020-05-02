<?php

/**
 * Rank Class, get current rank of logged
 * user in InfoPanel. Rank get from database
 * in base of config and weight of groups.
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

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
    public function __construct(string $username)
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
        $session = Session::init();
        if ($this->username == $session->getData("Account/User/Username")) {
            $current_rank_raw = Utils::ConvertRankToRaw($current_rank);
            
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
                session_destroy();
                session_start();
                $_SESSION["Request"]["Messages"] = ["Z bezpečnostních důvodů se přihlaš znova"];
                Utils::header("./");
            }
        }
        return $current_rank;
    }

    /**
     * Get user current count
     */
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
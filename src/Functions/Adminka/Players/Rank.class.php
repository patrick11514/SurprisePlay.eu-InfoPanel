<?php

namespace patrick115\Adminka\Players;

use patrick115\Main\Database;
use patrick115\Main\Error;
use patrick115\Main\Config;
use patrick115\Main\Session;

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
            return $this->config->getConfig("Main/group_names")[reset($groups_w_index)];
        } else {
            return $this->config->getConfig("Main/group_names")[$_group];
        }
    }
}
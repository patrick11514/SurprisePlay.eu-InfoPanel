<?php

namespace ZeroCz\Admin;

use ZeroCz\Banner\Banner;

class Minecraft implements Banner
{
    private $config;

    private $cache;

    private $db;

    public function __construct()
    {
        $this->config = Config::init();
        $this->db     = Database::init()->db();
        $this->cache  = Cache::init()->cache();
    }

    /**
     * Získá gropky z configu
     * @return array
     */
    public function getGroups()
    {
        return $this->config->getValue('groups');
    }

    /**
     * Zjsití zda uživatel má práva k přihlášení
     * @param string $needle Groupka uživatele
     * @return boolean
     */
    public function findGroup($needle)
    {
        if (isset($this->getGroups()[$needle])) {
            return true;
        }
        return false;
    }

    /**
     * Získá groupku uživatele z databáze
     * @param string $username
     * @return boolean
     */
    public function hasGroup($username)
    {
        $this->db->query("USE `main_online`");
        $result = $this->db->select("players", "group", [
            "name" => $username,
        ]);
        if (count($result) > 0) {
            return $this->findGroup($result[0]);
        }

        return false;
    }
    
    /**
     * Zabanuje uživatele na serveru
     * @param string $who Kdo banuje
     * @param string $user Koho banuje
     *
     * @return boolean
     */
    public function ban($who, $user)
    {}

    /**
     * Spočíta počet banů na serveru
     * @return int
     */
    public function getActiveBans()
    {
        return $this->cache->refreshIfExpired("minecraft_active_bans", function () {
            $this->db->query("USE `main_bat`");
            return $this->db->count("BAT_ban", [
                "ban_state" => 1,
            ]);
        }, 300);
    }

    public function getOnlinePlayers()
    {
        return $this->cache->refreshIfExpired("minecraft_online_users", function () {
            $json = file_get_contents('https://query.fakaheda.eu/82.208.17.43:27939.feed');
            $data = json_decode($json);
            return $data->players;
        }, 300);
    }

    public function getMaxPlayers()
    {
        return $this->cache->refreshIfExpired("minecraft_server_slots", function () {
            $json = file_get_contents('https://query.fakaheda.eu/82.208.17.43:27939.feed');
            $data = json_decode($json);
            return $data->slots;
        }, 300);
    }

    public function getRegisteredUsers()
    {
        return $this->cache->refreshIfExpired("minecraft_registered_users", function () {
            $this->db->query("USE `main_authme`");
            return $this->db->count("authme");
        }, 300);
    }

    public function getPaymets()
    {
        return $this->cache->refreshIfExpired("minecraft_sms_payments", function () {
            $this->db->query("USE `web_payments`");
            return $this->db->select("sms_payments", "*", [
                "ORDER" => ["id" => "DESC"],
                "LIMIT" => 10,
            ]);
        }, 600);
    }

    public function getHelpers()
    {
        $this->db->query("USE `perms`");
        $result = $this->db->select("perms_players", [
            "uuid",
            "username",
            "primary_group",
        ], [
            "OR"    => [
                "primary_group" => ["helper", "e-helper", "hl-helper", "zk-helper"],
            ],
            "ORDER" => ["primary_group" => "ASC"],
        ]);

        if (count($result) > 0) {
            return $result;
        }

        return false;
    }

    public function getBuilders()
    {
        $this->db->query("USE `perms`");
        $result = $this->db->select("perms_players", [
            "uuid",
            "username",
            "primary_group",
        ], [
            "OR"    => [
                "primary_group" => ["builder", "e-builder", "hl-builder", "zk-builder"],
            ],
            "ORDER" => ["primary_group" => "ASC"],
        ]);

        if (count($result) > 0) {
            return $result;
        }

        return false;
    }

    public function getYoutubers()
    {
        $this->db->query("USE `perms`");
        $result = $this->db->select("perms_players", [
            "uuid",
            "username",
            "primary_group",
        ], [
            "primary_group" => "youtuber",
            "ORDER"         => ["primary_group" => "ASC"],
        ]);

        if (count($result) > 0) {
            return $result;
        }

        return false;
    }

    public function getCustom($groups)
    {
        $sql = [
            "primary_group" => $groups,
            "ORDER"         => ["primary_group" => "ASC"],
        ];

        if (is_array($groups)) {
            $sql = [
                "OR"    => [
                    "primary_group" => $groups,
                ],
                "ORDER" => ["primary_group" => "ASC"],
            ];
        }

        $this->db->query("USE `perms`");
        $result = $this->db->select("perms_players", [
            "uuid",
            "username",
            "primary_group",
        ], $sql);

        if (count($result) > 0) {
            return $result;
        }

        return false;
    }

    public function changeGroup($username, $uuid, $group)
    {
        $this->db->query("USE `perms`");
        $this->db->update("perms_players", [
            "primary_group" => $group
        ], [
            "uuid" => $uuid
        ]);
        $this->db->update("perms_user_permissions", [
            "permission" => "group.$group"
        ], [
            "uuid" => $uuid,
            "permission[~]" => "group.%"
        ]);
        
        $this->db->query("USE `main_online`");
        $this->db->update("players", [
            "group" => array_search($group, Config::get('groups'))
        ], [
            "name" => $username
        ]);
    }
}

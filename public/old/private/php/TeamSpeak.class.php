<?php

namespace ZeroCz\Admin;

use ZeroCz\Banner\Banner;

class TeamSpeak implements Banner {

    private $cache;

    public function __construct() {
        $this->cache = Cache::init()->cache();
    }

    public function getOnlinePlayers() {
        return $this->cache->refreshIfExpired("teamspeak_online_players", function () {
            $json = file_get_contents('https://query.fakaheda.eu/81.0.217.180:7653.feed');
            $data = json_decode($json);
            return $data->players;
        }, 300);
    }

    public function getMaxPlayers() {
        return $this->cache->refreshIfExpired("teamspeak_max_players", function () {
            $json = file_get_contents('https://query.fakaheda.eu/81.0.217.180:7653.feed');
            $data = json_decode($json);
            return $data->slots;
        }, 300);
    }
}

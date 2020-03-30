<?php

namespace patrick115\Minecraft;

use patrick115\Adminka\Main;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Logger;

class Skins
{
    private $username;

    private $logger;

    private $api = "https://visage.surgeplay.com/bust/96/{uuid}";

    public function __construct($username)
    {
        $this->username = $username;
        $this->logger = Logger::init();
    }

    public function getSkin()
    {
        if ($this->inCache()) {
            return $this->getCache();
        }

        return $this->createCache();
    }

    private function inCache()
    {
        if (!is_writable(Main::getWorkDirectory() . "src/cache")) {
            $this->logger->log("Cache directory doesn't have permissions, skipping cashing!", "warning", true);
            return false;
        }
        if (file_exists(Main::getWorkDirectory() . "src/cache/{$this->username}"))
        {
            return true;
        }
        return false;
    }

    private function getCache()
    {
        return file_get_contents(Main::getWorkDirectory() . "src/cache/{$this->username}");
    }

    private function createCache()
    {
        $uuid = Utils::getOriginalUUIDByNick($this->username);

        $skin_data = file_get_contents(str_replace("{uuid}", $uuid, $this->api));

        $base64 = "data:image/png;base64," . base64_encode($skin_data);
        if (!is_writable(Main::getWorkDirectory() . "src/cache")) {
            $this->logger->log("Cache directory doesn't have permissions, skipping cashing!", "warning", true);
            return $base64;
        }
        $f = fopen(Main::getWorkDirectory() . "src/cache/{$this->username}", "w");
        fwrite($f, $base64);
        fclose($f);

        return $base64;
    }
    //https://visage.surgeplay.com/bust/96/f018d5d53fa6431ab687ea80cf7e1a14
}
<?php

/**
 * Api for skins
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Minecraft;

use patrick115\Adminka\Main;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Logger;

class Skins
{
    /**
     * Username
     * @var string
     */
    private $username;

    /**
     * Logger class
     * @var object
     */
    private $logger;

    /**
     * Api url
     * @var string
     */
    private $api = "https://visage.surgeplay.com/bust/96/{uuid}";

    /**
     * Contant of steve skin
     * @var string
     */
    const empty_skin = "data:image/png;base64,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";

    /**
     * Construct function
     * @param string $username - Username of user
     */
    public function __construct(string $username)
    {
        $this->username = $username;
        $this->logger = Logger::init();
    }

    /**
     * Get skin
     * @return string
     */
    public function getSkin()
    {
        if ($this->inCache()) {
            return $this->getCache();
        }

        return $this->createCache();
    }

    /** 
     * Check if skin is in cache
     * @return bool
     */
    private function inCache()
    {
        if (!is_writable(Main::getWorkDirectory() . "src/cache")) {
            $this->logger->log("Cache directory doesn't have permissions, skipping cashing!", "warning", true);
            return false;
        }
        if (file_exists(Main::getWorkDirectory() . "src/cache/{$this->username}"))
        {
            if (@file_get_contents(Main::getWorkDirectory() . "src/cache/{$this->username}") == "data:image/png;base64,") {
                unlink(Main::getWorkDirectory() . "src/cache/{$this->username}");
                return false;
            }
            if (@file_get_contents(Main::getWorkDirectory() . "src/cache/{$this->username}") == self::empty_skin) {

                unlink(Main::getWorkDirectory() . "src/cache/{$this->username}");
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Get skin from cache
     * @return string
     */
    private function getCache()
    {
        return @file_get_contents(Main::getWorkDirectory() . "src/cache/{$this->username}");
    }

    /**
     * Create chache file
     * @return string
     */
    private function createCache()
    {
        $uuid = Utils::getOriginalUUIDByNick($this->username);

        $skin_data = @file_get_contents(str_replace("{uuid}", $uuid, $this->api));

        if (empty($skin_data)) {
            $base64 = self::empty_skin;
            $_SESSION["Request"]["Errors"] = ["Služba visage.surgeplay.com je nyní přetížená, nelze načíst některé skiny!"];
        } else {
            $base64 = "data:image/png;base64," . base64_encode($skin_data);
        }
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
<?php

/**
 * Main Class
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Main;

use patrick115\Main\Error;
use patrick115\Main\Singleton;
use patrick115\Main\Tools\Utils;

class Config
{

    use Singleton;

    /**
     * Config values
     * 
     * @var array
     */
    private $mem_config = NULL;

    /**
     * Config directory
     * 
     * @var string
     */
    private static $configDir;

    private $error;

    /**
     * Prevence to construct this function
     * 
     */
    private function __construct() {

        $this->error = Error::init();

        if (!file_exists(\patrick115\Adminka\Main::getConfigDirectory())) {
            $this->error->catchError("Config file not found.", debug_backtrace());
            return;
        } else {
            \patrick115\main\Config::$configDir = \patrick115\Adminka\Main::getConfigDirectory();
            $this->mem_config = include(\patrick115\main\Config::$configDir);
            return $this->mem_config;
        }
    }

    public static function existConfig()
    {
        if (file_exists(self::$configDir)) {
            return true;
        }
        return false;
    }

    /**
     * Get config value
     * 
     * @param string $path Get path from config
     */
    public function getConfig($path)
    {
        $config = $this->mem_config;

        $part = explode("/", $path);

        if (@Utils::newEmpty($part[1])) {
            return $config[$path];
        }

        for ($i=0; $i < count($part); $i++) { 
            if (Utils::newEmpty(@$config[$part[$i]]) && !Utils::newNull($config[$part[$i]])) {
                $this->error->catchError("Can't find {$path} in config!", debug_backtrace());
            }
            $config = $config[$part[$i]];
        }
        return $config;
    }
}
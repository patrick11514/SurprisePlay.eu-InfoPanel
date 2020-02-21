<?php

namespace ZeroCz\Admin;

class Config
{
    use Singleton;

    private $config;

    public static function get($value) {
        return self::init()->getValue($value);
    }

    private function __construct()
    {
        $this->config = include_once __DIR__ . '/../config/config.php';
    }

    public function getValue($key)
    {
        if (!array_key_exists($key, $this->config)) {
            return '';
        }
        return $this->config[$key];
    }
}

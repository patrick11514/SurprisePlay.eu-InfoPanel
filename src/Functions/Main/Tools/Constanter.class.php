<?php

namespace patrick115\Main\Tools;

use patrick115\Main\Singleton;

class Constanter
{
    use Singleton;

    private $const = [];

    private function __construct()
    {}

    public function send($name, $value, $rewrite = false)
    {
        if (isset($this->const[$name]) && $rewrite === true) {
            $this->const[$name] = $value;
        } else if (isset($this->const[$name])) {
            return "Constant is always set!";
        } else {
            $this->const[$name] = $value;
        }
    }

    public function get($name)
    {
        if (empty($this->const[$name])) return "Constant not set!";
        return $this->const[$name];
    }

    public function expiry($name)
    {
        if (empty($this->const[$name])) return "Constant not set!";
        unset($this->const[$name]);
    }
}
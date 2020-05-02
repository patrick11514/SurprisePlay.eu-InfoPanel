<?php

/**
 * Constants between classes
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Main\Tools;

use patrick115\Main\Singleton;

class Constanter
{
    use Singleton;

    /**
     * List of constants
     * @var array
     */
    private $const = [];

    private function __construct()
    {}

    /**
     * Function for save constant
     * 
     * @param string $name - Name of constant
     * @param mixed $value - Value of constant
     * @param bool $rewrite - If constant exist, and rewrite it
     * @return mixed
     */
    public function send(string $name, $value, bool $rewrite = false)
    {
        if (isset($this->const[$name]) && $rewrite === true) {
            $this->const[$name] = $value;
        } else if (isset($this->const[$name])) {
            return "Constant is always set!";
        } else {
            $this->const[$name] = $value;
        }
    }

    /**
     * Get value of constant
     * @param string $name - Name of constant
     * @return mixed
     */
    public function get(string $name)
    {
        if (empty($this->const[$name])) return "Constant not set!";
        return $this->const[$name];
    }

    /**
     * Clear constant
     * @param string $name - name of constant
     * @return string
     */
    public function expiry(string $name)
    {
        if (empty($this->const[$name])) return "Constant not set!";
        unset($this->const[$name]);
    }
}
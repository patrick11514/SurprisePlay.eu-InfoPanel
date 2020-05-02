<?php

/**
 * Class for copyright
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */


namespace patrick115\cpy;

class Copy {
    /**
     * Stored copyright
     * @var string
     */
    private $copy;

    public function __construct()
    {
        $this->copy = "&copy;" . date("Y") . " <a href=\"//patrikmin.tech\">patrick115</a>";
    }

    /**
     * Return copyright
     * @return string
     */
    public function get()
    {
        return $this->copy;
    }
}
<?php

/**
 * Sigleton
 *
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 *
 */

namespace patrick115\Main;

trait Singleton
{

    private static $instance = NULL;

    public static function init()
    {
        if (self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
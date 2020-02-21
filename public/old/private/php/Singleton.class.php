<?php

namespace ZeroCz\Admin;

trait Singleton
{
    private static $instance = null;

    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function i()
    {
        return self::init();
    }
}
<?php

use patrick115\Main\Error;

use patrick115\Main\Session;

ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    $first     = __DIR__ . '/';
    $extension = '.class.php';
    $class     = explode('\\', $class);
    if (strpos(strtolower($class[1]), "interfaces") !== false) {
        $extension = '.interface.php';
    }

    unset($class[0]);
    
    $path      = $first . implode("/", $class) . $extension;

    if (!file_exists($path)) {
        return false;
    }

    include_once $path;
});

Session::init()->create();

$errors = Error::init();
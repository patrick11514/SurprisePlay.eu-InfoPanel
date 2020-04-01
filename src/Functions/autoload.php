<?php

use patrick115\Main\Error;
use patrick115\Main\Session;
use patrick115\Main\Config;
use patrick115\Main\Tools\Constanter;

define("CURRENT_VERSION", "0.3.5");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    $first     = __DIR__ . '/';
    $extension = '.class.php';
    $class     = explode('\\', $class);
    if (strpos(strtolower($class[1]), "interfaces") !== false) {
        $extension = '.interface.php';
    } else if (strpos(strtolower($class[1]), "cpy") !== false) {
        $first .= "Hashes/";
        $extension = '.cpy.php';
        unset($class[1]);
    }

    unset($class[0]);
    
    $path      = $first . implode("/", $class) . $extension;

    if (!file_exists($path)) {
        return false;
    }
    include_once $path;
});

$__DEBUG = Config::init()->getConfig("debug");

if ($__DEBUG === true) {
    define("DEBUG", TRUE);
} else if ($__DEBUG === true) {
    define("DEBUG", FALSE);
} else {
    define("DEBUG", NULL);
}

$exts = [
    "mbstring"
];

$ext_errs = [];

foreach ($exts as $ext) {
    if (!extension_loaded($ext)) {
        $ext_errs[] = $ext;
    }
}

$_616e6f6e796d6f7573 = function($_646174616261736573, $_64625f696e7374616e6365, $_6572726f725f66756e6374696f6e) {
    $error = [];
    foreach ($_646174616261736573 as $_6461746162617365)
    {
        $_DEC = @\patrick115\Main\Tools\Utils::getPackage([1 => $_6461746162617365]);
        if (@$_64625f696e7374616e6365->
        execute("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $_DEC . "'", true)
        ->fetch_object()->SCHEMA_NAME != $_DEC) {
            $error[] = $_DEC;
        }
    }
    if (!empty($error)) {
        foreach ($error as $err) {
            $_6572726f725f66756e6374696f6e->catchError(str_replace("{@var}", $err, \patrick115\Main\Tools\Utils::getPackage([1 => "4461746162617365207b407661727d20646f65736e27742065786973747321"])), debug_backtrace());
        }
    }
};

Constanter::init()->send("_616e6f6e796d6f7573", $_616e6f6e796d6f7573);

Session::init()->create();

$errors = Error::init();

if (!empty($ext_errs)) {
    foreach ($ext_errs as $ext_err) {
        $errors->catchError("Extension {$ext_err} is not loaded!", debug_backtrace());
    }
}
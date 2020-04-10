<?php

use patrick115\Main\Error;
use patrick115\Main\Session;
use patrick115\Main\Config;
use patrick115\Main\Tools\Constanter;

define("CURRENT_VERSION", "0.4.2");

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

$_4h5df4j48nb1dfj8dsfzhh = function($_jgfsdh48sdhbdfn) {
    if (!@\patrick115\Main\Tools\Utils::isJson($_jgfsdh48sdhbdfn)) {
        return @\patrick115\Main\Tools\Utils::getPackage([1=>"536f75626f722073206861736879206e656ec3ad20706c61746ec3bd206a736f6e21"]);
    }
    return true;
};

$_fhd48fhdf8djmn = function($_nbd18hj1m8jf, $_4h8f4dn1dfjdfj, $_4n158dfj88df4j, $_gsmbiosh45m, $_4h61dfhj4m8f) {
    $_fc = $_4h61dfhj4m8f($_4h8f4dn1dfjdfj);

    $_nbd18hj1m8jf = rtrim($_nbd18hj1m8jf, "/");

    if ($_gsmbiosh45m === null) return str_replace("{@c}", $_4n158dfj88df4j,$_4h61dfhj4m8f("486173682070726f20736f75626f72207b40637d206e656578697374756a652c206f626e6f76746520686f207a65207ac3a16c6f687921"));

    if ($_fc($_nbd18hj1m8jf . $_4n158dfj88df4j) != $_gsmbiosh45m) {
        return str_replace("{@c}",  $_4n158dfj88df4j, $_4h61dfhj4m8f("496e7465677269746120736f75626f7275207b40637d206e6570726f62c49b686c6120c3ba7370c49bc5a16ec49b21"));
    }
    return true;
};

$_ghn4d8hn = function($_gdh45nbd, $_hg4hd5f4bh1df54hd) {
    if ($_gdh45nbd($_hg4hd5f4bh1df54hd)) return true;
    return false;
};

$const = Constanter::init();



$const->send(@\patrick115\Main\Tools\Utils::getPackage("6473676e6473676e34383438686466346a6e31646667386a64"), $_fhd48fhdf8djmn);
$const->send(@\patrick115\Main\Tools\Utils::getPackage("686e316664356a346466386e313867666466386a386e7631336a"), $_4h5df4j48nb1dfj8dsfzhh);
$const->send("_616e6f6e796d6f7573", $_616e6f6e796d6f7573);
$const->send(@\patrick115\Main\Tools\Utils::getPackage("6864736e69756864736473666a64736a6664736a66736a736a"), 
             @\patrick115\Main\Tools\Utils::getPackage("65786974"));
$const->send(@\patrick115\Main\Tools\Utils::getPackage("31"), 
             @\patrick115\Main\Tools\Utils::getPackage("5c7061747269636b3131355c4861736865735c496e74656772697479"));
$const->send(@\patrick115\Main\Tools\Utils::getPackage("32"), 
             @\patrick115\Main\Tools\Utils::getPackage("5c7061747269636b3131355c4d61696e5c526f75746572"));
$const->send(@\patrick115\Main\Tools\Utils::getPackage("33"), 
             @\patrick115\Main\Tools\Utils::getPackage("5c7061747269636b3131355c41646d696e6b615c506c61796572735c4163636f756e7473"));
$const->send(@\patrick115\Main\Tools\Utils::getPackage("636865636b5f6663"), 
             @\patrick115\Main\Tools\Utils::getPackage("6d756c7469436865636b"));
$const->send(@\patrick115\Main\Tools\Utils::getPackage("676c6f62616c2d66696c652d6c697374"),
            [
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f4150492e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f4c6f676765722e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f4d61696e2e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f4e617669676174696f6e2e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f5065726d697373696f6e732e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f5469636b6574732e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f546f646f2e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f4572726f72732f457863657074696f6e2e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f506c61796572732f4163636f756e74732e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f506c61796572732f52616e6b2e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f41646d696e6b612f506c61796572732f53657474696e67732e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4861736865732f436f70792e6370792e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4861736865732f496e746567726974792e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f436f6e6669672e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f44617461626173652e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f4572726f722e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f526f757465722e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f53657373696f6e2e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f53696e676c65746f6e2e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f546f6f6c732f436f6e7374616e7465722e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f546f6f6c732f506f7374436865636b732e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d61696e2f546f6f6c732f5574696c732e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d696e6563726166742f4368616e6765446174612e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d696e6563726166742f536b696e732e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f4d696e6563726166742f53746174732e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f52657175657374732f435352462e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f52657175657374732f436f72652e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f54656d706c617465732f54656d706c617465722e636c6173732e706870"),
                @\patrick115\Main\Tools\Utils::getPackage("2f7372632f46756e6374696f6e732f6175746f6c6f61642e706870"),

            ]);
$const->send(\patrick115\Main\Tools\Utils::getPackage("6d6e6a346e386466346a3564683834646e313364"), $_ghn4d8hn);

Session::init()->create();

$errors = Error::init();

if (!empty($ext_errs)) {
    foreach ($ext_errs as $ext_err) {
        $errors->catchError("Extension {$ext_err} is not loaded!", debug_backtrace());
    }
}
<?php

/**
 * API File.
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

/**
 * Json UTF-8
 */
header("Content-type: application/json; charset=utf-8");

use patrick115\Adminka\Main;

include __DIR__ . "/src/Functions/autoload.php";

if (empty($_POST)) {
    die('{"success":false, "message":"Invalid Request"}');
}

Main::Create("\patrick115\Adminka\Main", [__DIR__]);

$app = Main::Create("\patrick115\Adminka\API", [$_POST]);

$return = $app->check();
echo ($return);
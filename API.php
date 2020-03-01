<?php

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
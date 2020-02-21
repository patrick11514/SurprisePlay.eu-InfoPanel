<?php

use patrick115\Adminka\Main;

include __DIR__ . "/src/Functions/autoload.php";

if (empty($_POST)) {
    die("Invalid Request!");
}

Main::Create("\patrick115\Adminka\Main", [__DIR__]);

$app = Main::Create("\patrick115\Requests\Core", [$_POST]);

$app->check();

if (empty($app->getErrors())) {
    $_SESSION["Post"]["Success"] = true;
    \patrick115\Main\Tools\Utils::header("./" . $app->getPost()["source_page"]);
} else {
    $_SESSION["Request"]["Errors"] = $app->getErrors();
    \patrick115\Main\Tools\Utils::header("./" . $app->getPost()["source_page"]);
}

print_r($_SESSION);

$errors->returnError();
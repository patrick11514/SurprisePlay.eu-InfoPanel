<?php

use patrick115\Adminka\Main;
use patrick115\Main\Tools\Utils;

include __DIR__ . "/src/Functions/autoload.php";

if (empty($_POST)) {
    die("Invalid Request!");
}

print_r($_POST);


Main::Create("\patrick115\Adminka\Main", [__DIR__]);

$app = Main::Create("\patrick115\Requests\Core", [$_POST]);

$app->check();

print_r($app->getErrors());

if (empty($app->getErrors())) {
    $_SESSION["Post"]["Success"] = true;
    \patrick115\Main\Tools\Utils::header("./" . $app->getPost()["source_page"]);
} else {
    $_SESSION["Request"]["Errors"] = $app->getErrors();
    \patrick115\Main\Tools\Utils::header("./" . Utils::chars($_POST["source_page"]));
}
#$errors->returnError();
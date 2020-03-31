<?php

use patrick115\Adminka\Main;
use patrick115\Main\Tools\Utils;

include __DIR__ . "/src/Functions/autoload.php";

if (empty($_POST)) {
    die("Invalid Request!");
}


Main::Create("\patrick115\Adminka\Main", [__DIR__]);

$app = Main::Create("\patrick115\Requests\Core", [$_POST]);

$app->check();

if (!empty(@constant("DELETE_SESSION")) && @constant("DELETE_SESSION") === true) {
    session_destroy();
    session_start();
}
if (!empty(@constant("MESSAGE"))) {
    $_SESSION["Request"]["Messages"] = constant("MESSAGE");
}

if (empty($app->getErrors())) {
    if (@constant("DELETE_SESSION") !== true)
    $_SESSION["Post"]["Success"] = true;
    \patrick115\Main\Tools\Utils::header("./" . str_replace("|", "&", $app->getPost()["source_page"]));
} else {
    $_SESSION["Request"]["Errors"] = $app->getErrors();
    \patrick115\Main\Tools\Utils::header("./" . str_replace("|", "&", Utils::chars($_POST["source_page"])));
}
#$errors->returnError();
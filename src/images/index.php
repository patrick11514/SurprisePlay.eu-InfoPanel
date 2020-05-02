<?php

if (empty($_GET)) {
    header("Content-type: application/json; charset=utf-8");
    die(error("Undefined post"));
}

function error($message) {
    $arr = [
        "success" => "false",
        "message" => $message
    ];
    return json_encode($arr);
}

use patrick115\Images\Loader;

include __DIR__ . "/../Functions/Images/Loader.class.php";

$storage = __DIR__ . "/.storage";

$loader = new Loader($storage);

echo $loader->showPicture();
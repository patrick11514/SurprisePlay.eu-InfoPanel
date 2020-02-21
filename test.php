<?php

include __DIR__ . "/src/Functions/autoload.php";

use patrick115\Adminka\Main;

$nav = Main::Create("\patrick115\Adminka\Navigation", []);
$nav_code = $nav->getNav()->createNav()->get();
echo $nav_code; 
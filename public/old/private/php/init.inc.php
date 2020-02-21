<?php
use ZeroCz\Admin\Auth;
use ZeroCz\Admin\System;
use ZeroCz\Admin\Minecraft;

define('MAIN_DIR', __DIR__);

session_start();

//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>';
$start = microtime(true);

spl_autoload_register(function($class) {
    $first = __DIR__ . '/';
    $extension = '.class.php';
    $class = explode('\\', $class);
    $path = $first . end($class) . $extension;

    if (file_exists($path)) {
        require_once $path;
    } else {
        $first = __DIR__ . '/Tickets/';
        $path = $first . end($class) . $extension;
        require_once $path;
    }
});

ini_set('session.use_strict_mode', 1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!file_exists(MAIN_DIR . '/../vendor/autoload.php')) {
    die('Nepovedlo se načíst Composer');
}

if (!file_exists(MAIN_DIR . '/../config/config.php') || filesize(MAIN_DIR . '/../config/config.php') < 1 ) {
    die('Nepovedlo se načíst config.php');
}

require_once MAIN_DIR . '/../vendor/autoload.php';

$auth = new Auth();
//$minecraft = new Minecraft();


//ob_start();
//include_once MAIN_DIR . '/../pages/sidebar.php';
//$page_sidebar = ob_get_clean();
?>

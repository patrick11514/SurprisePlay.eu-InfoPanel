<?php

/**
 * Main class of InfoPanel
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Adminka;

use patrick115\Main\Error;
use patrick115\Main\Tools\Constanter;

class Main
{
    /**
     * Error class
     * @var object
     */
    private $error;

    /**
     * Main Directory
     * @var string 
     */
    private static $workDirectory;

    /**
     * Stored Apps
     * @var array
     */
    public static $app = [];
    /**
     * Stored App params
     * @var array
     */
    private static $app_params = [];

    /**
     * Construct function
     * @param string $dir
     */
    private function __construct($dir)
    {   
        $this->error = Error::init();
        if (file_exists($dir)) {
            if (is_dir($dir)) {
                if (is_writable($dir)) {
                    self::$workDirectory = rtrim($dir, "/") . "/";
                } else {
                    $this->error->catchError("Directory $dir is not writable!", debug_backtrace());
                    return;
                }
            } else {
                $this->error->catchError("$dir is not directory!", debug_backtrace());
                return;
            }
        } else {
            $this->error->catchError("Directory $dir not found.", debug_backtrace());
            return;
        }
    }

    /**
     * Starting function
     * @param string $directory
     */
    public static function Start($directory)
    {
        self::Check();

        self::Environment();

        self::Create("\patrick115\Adminka\Main", [$directory])->Run();
    }

    /**
     * Setting Environment
     */
    public static function Environment()
    {
        ignore_user_abort(true);
        
        $memory = intval(128 * 1024 * 1024);

        ini_set("memory_limit", $memory);
    }

    /**
     * Create App
     * @param string $cls - App Class
     * @param array  $param - App Params
     */
    public static function Create($cls, array $params)
    { 
        if (isset(self::$app[$cls]) && $params == self::$app_params[$cls]) {
            return self::$app[$cls];
        }
        $param = "";
        $array = [];
        $cls = "\\" . ltrim($cls, "\\");
        foreach ($params as $value) {
            if (is_array($value)) {
                $array[] = $value;
            } else {
                $param .= "{$value},";
            }
        }
        $param = rtrim($param, ",");
        if (!empty($array)) {
            if ($param != "") {
                self::$app[$cls] = new $cls($param, $array[0]);
            } else {
                self::$app[$cls] = new $cls($array[0]);
            }
        } else {
            self::$app[$cls] = new $cls($param);
        }
        self::$app_params[$cls] = $params;
        return self::$app[$cls];
    }

    /**
     * Get App
     * @param string $cls - App Class
     * @return object
     */
    public static function getApp($cls)
    {
        if (empty(self::$app[$cls])) {Error::init()->catchError("App $cls not created!", debug_backtrace());return;}
        return self::$app[$cls];
    }

    /**
     * Run Administration
     */
    public function Run()
    {
        if (DEBUG === true) {
            echo "<!--Starting at " . self::$workDirectory . "!-->" . PHP_EOL;
        }
        $constanter = Constanter::init();
        $fc = $constanter->get("check_fc");

        self::Create($constanter->get("1"), [])->$fc();
        self::Create($constanter->get("2"), [$_SERVER["REQUEST_URI"]]);
        self::Create($constanter->get("3"), [])->run();
    }

    /**
     * Check if database exists
     */
    public static function Check()
    {
        $_2xgf6 = \patrick115\Main\Database::init();
        $_2xf8h = \patrick115\Main\Error::init();
        $_4gx86 = [
            "61646d696e6b61",
            "61646d696e6b615f7469636b657473",
            "6d61696e5f616e746976706e",
            "6d61696e5f617574686d65",
            "6d61696e5f6175746f6c6f67696e",
            "6d61696e5f62616e73",
            "6d61696e5f6b726564697479",
            "6d61696e5f6f6e6c696e65",
            "6d61696e5f7065726d73",
            "737572766976616c5f636d69",
        ];

        $fc = \patrick115\Main\Tools\Constanter::init()->get("_616e6f6e796d6f7573");
        $fc($_4gx86, $_2xgf6, $_2xf8h);
        
    }

    /**
     * Return work directory
     * @return string
     */
    public static function getWorkDirectory()
    {
        return self::$workDirectory;
    }

    /**
     * Return config directory
     * @return string
     */
    public static function getConfigDirectory()
    {
        return self::$workDirectory . "src/config/config.php";
    }

    /**
     * Return templates directory
     * @return string
     */
    public static function getTemplateDirectory()
    {
        return self::$workDirectory . "src/Pages";
    }
}
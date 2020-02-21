<?php

namespace patrick115\Adminka;

use patrick115\Main\Error;

class Main implements \patrick115\Interfaces\Main
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
    private $workDirectory;

    /**
     * Stored Apps
     * @var array
     */
    private static $app = [];
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
                    $this->workDirectory = rtrim($dir, "/") . "/";
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
        echo "<!--Starting at " . $this->workDirectory . "!-->" . PHP_EOL;
        self::Create("patrick115\Main\Router", [$_SERVER["REQUEST_URI"]]);
        self::Create("patrick115\Adminka\Players\Accounts", [])->run();
    }

    public static function getWorkDirectory()
    {
        return self::getApp("\patrick115\Adminka\Main")->workDirectory;
    }

    public static function getConfigDirectory()
    {
        return self::getApp("\patrick115\Adminka\Main")->workDirectory . "src/config/config.php";
    }

    public static function getTemplateDirectory()
    {
        return self::getApp("\patrick115\Adminka\Main")->workDirectory . "src/Pages";
    }
}
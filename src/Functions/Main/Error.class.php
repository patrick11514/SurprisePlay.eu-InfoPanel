<?php

/**
 * Error Class
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Main;

use patrick115\Main\Singleton;
use patrick115\Main\Tools\Utils;

class Error
{

    use Singleton;

    /**
     * Contains all errors
     * 
     * @var array
     */
    private $catcherror = NULL;

    /**
     * Contains error time
     * 
     * @var array
     */
    private $catchtime = NULL;

    private $transfer_error;

    private function __construct() {}

    /**
     * Catch error
     * 
     * @param string $e Error message
     * @param object $dump Informations about error
     * 
     */
    public function catchError($e, $dump)
    {   
        $file = $dump[0]["file"];
        $class = isset($dump[0]["class"]) ? $dump[0]["class"] : "GLOBAL";
        $function = ($dump[0]["function"] == "{closure}") ? "ANONYMOUS" : $dump[0]["function"];
        $line = $dump[0]["line"];
        if (empty($dump[2])) {
            $this->catcherror[] = "<b>{$file}({$line}):</b> <i>{$class}::{$function}:</i> " . $e;
        } else {
            $startFile = $dump[2]["file"];
            $startFileLine = $dump[2]["line"];
            $this->catcherror[] = "{$startFile}({$startFileLine}) -><br>    <b>{$file}({$line}):</b> <i>{$class}::{$function}:</i><br>       " . $e;
        }
        $this->catchtime[] = date("H:i:s");
    }

    /**
     * Return all errors
     */
    public function returnError()
    {
        if($this->catcherror !== NULL) {
            Utils::header("./?error");
            /*$return = "<pre id=\"error-list\">";
            $return .= "<b>Errors (" . count($this->catcherror) . "):</b>" . PHP_EOL;
            foreach ($this->catcherror as $id => $error) {
                $return .= "[" . $this->catchtime[$id] . "] " . $error . PHP_EOL;
            }
            $return .= "</pre>";
            #ob_end_clean();
            echo $return;
            die();*/
        }
    }

    public function errorExist()
    {
        if (empty($this->catcherror)) {
            return false;
        }
        return true;
    }

    /**
     * Put error to var
     */
    public function putError($e)
    {
        if (empty($e)) {
            $this->catchError("Error not set", debug_backtrace());
        }
        $this->transfer_error = $e;
    }

    /**
     * Return error from var
     */
    public function getError()
    {
        return $this->transfer_error;
    }

    public function getErrorHTML()
    {
        if ($this->catcherror === null) {
            #Utils::header("./");
        }
        $return = "<h2 style=\"color:red;\">Found " . count($this->catcherror) . " errors!</h2>
        <pre>";
        foreach ($this->catcherror as $id => $error) {
            $return .= "[" . $this->catchtime[$id] . "] " . $error . PHP_EOL;
        }
        $return .= "</pre>";
        $return .= "<h2><a href=\"./\">Back to home page</a></h2>";
        return $return;
    }
    
}
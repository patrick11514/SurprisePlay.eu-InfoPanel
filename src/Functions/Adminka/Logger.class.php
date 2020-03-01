<?php

namespace patrick115\Adminka;

use patrick115\Main\Database;
use patrick115\Main\Singleton;
use patrick115\Main\Error;
use patrick115\Main\Tools\Utils;
use patrick115\Main\Session;

class Logger
{
    use Singleton;

    private $database;
    private $error;

    private function __construct()
    {
        $this->database = Database::init();
        $this->error = Error::init();
    }

    public function log($message, $type = "info")
    {
        $types = ["info", "login", "settings", "logout"];
        if (empty($message)) {
            $this->error->catchError("All variables must be filled!", debug_backtrace());
            return;
        }
        if (!in_array($type, $types)) {
            $this->error->catchError("Undefined type $type of log!", debug_backtrace());
            return;
        }
        $this->database->insert("logger", 
            [
                "id", 
                "type", 
                "userid", 
                "ip", 
                "message", 
                "sessionid",
                "timestamp",
                "date"
            ], 
            [
                "", 
                $type, 
                Utils::getClientID(
                    Session::init()->getData("Account/User/Username")  
                ), 
                Utils::getUserIP(), 
                Utils::chars($message), 
                session_id(), 
                time(), 
                date("H:i:s d.m.Y")
            ]
        );
    }
}
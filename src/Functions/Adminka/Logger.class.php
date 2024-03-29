<?php

/**
 * Logger, log informations to database
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Adminka;

use patrick115\Main\Database;
use patrick115\Main\Singleton;
use patrick115\Main\Error;
use patrick115\Main\Tools\Utils;
use patrick115\Main\Session;

class Logger
{
    use Singleton;

    /**
     * Class database
     * @var object
     */
    private $database;
    /**
     * Class error
     * @var object
     */
    private $error;

    private function __construct()
    {
        $this->database = Database::init();
        $this->error = Error::init();
    }

    /**
     * Logger function, log data to tadabase
     * 
     * @param string $message - message of log
     * @param string $type - type of log
     * @param bool $syslog - if is system log
     * @return null
     */
    public function log(string $message, string $type = "info", bool $syslog = false)
    {
        $types = ["info", "login", "settings", "logout", "antivpn", "unregister", "warning", "transfer_data", "tickets"];
        if (empty($message)) {
            $this->error->catchError("All variables must be filled!", debug_backtrace());
            return;
        }
        if (!in_array($type, $types)) {
            $this->error->catchError("Undefined type $type of log!", debug_backtrace());
            return;
        }
        if ($syslog) {
            $this->database->insert("sys_log",
                [
                    "id",
                    "type",
                    "message",
                    "timestamp",
                    "date"
                ],
                [
                    "",
                    $type,
                    $message,
                    time(),
                    date("H:i:s d.m.Y")   
                ]
            );
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
                "date",
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
<?php

namespace patrick115\Adminka;

use patrick115\Main\Config;
use patrick115\Main\Error;
use patrick115\Main\Database;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;

class Tickets 
{

    private $username;

    const TICKET_WAITING_FOR_USER = 0;
    const TICKET_WAITING_FOR_ADMIN = 1;
    const TICKET_CLOSE = 2;

    private $ticket_groups = [];
    private $ticket_reasons = [];

    private $database;
    private $config;
    private $error;

    private $methods = [
        "getData" => [
            "username"
        ],
        "createTicket" => [
            "username",
            "type",
            "message"
        ],
        "callback" => [
            "username",
            "callback"
        ]
    ];
    private $vars;

    public function __construct($data)
    {
        $this->error = Error::init();
        if (empty($data["method"])) {
            define("ERROR", ["Method is empty"]);
            $this->error->catchError("Method is empty!", debug_backtrace());
            return false;
        }
        if (empty($this->methods[$data["method"]])) {
            define("ERROR", ["Undefined method {$data["method"]}"]);
            $this->error->catchError("Undefined method {$data["method"]}", debug_backtrace());
            return false;
        }

        foreach ($this->methods[$data["method"]] as $value) {
            if (empty($data[$value])) {
                define("ERROR", ["Can't find value $value in got data!"]);
                $this->error->catchError("Can't find value $value in got data!", debug_backtrace());
                return false;
            }
        }

        

        foreach ($data as $name => $value) {
            $this->vars[Utils::chars($name)] = Utils::chars($value);
        }

        $this->database = Database::init();
        $this->config = Config::init();
        

        $this->loadConfig("groups");
        $this->loadConfig("reasons");

        $this->username = $this->vars["username"];
    }


    public function getReasons()
    {
        return $this->ticket_reasons;
    }

    public function getGroups()
    {
        return $this->ticket_groups;
    }

    public function writeTicket()
    {

        echo "<pre>";

        $user = Utils::getClientID($this->username);
        $title = $this->vars["name"];
        $for = 
        rtrim(
            ltrim(
                Utils::getPackage([1 => $this->vars["type"]])
                , "%%TICKET_ID;")
            , ";TICKET_ID%%"
        );
        $message = $this->vars["message"];

        foreach ($this->ticket_reasons as $group => $reason_list) {
            if (in_array($for, $reason_list)) {
                $group = $group;
                break;
            }
        }

        $time = time();

        $this->database->insert("adminka_tickets`.`tickets_list", 
        [
            "id", 
            "author", 
            "title", 
            "for", 
            "reason", 
            "waiting_for", 
            "create_timestamp"
        ], 
        [
            "", 
            $user,
            $title, 
            $group, 
            $for, 
            self::TICKET_WAITING_FOR_ADMIN,
            $time
        ]);

        $rv = $this->database->select(
            ["id"], 
            "adminka_tickets`.`tickets_list", 
            "WHERE `author` = {$user} AND `create_timestamp` = '{$time}' AND `reason` = '{$for}' AND `for` = '$group' LIMIT 1");
            
        if (!$rv) {
            return false;
        }
        $ticket_ID = $rv->fetch_object()->id;

        $this->database->insert("adminka_tickets`.`tickets_messages", 
        [
            "id", 
            "ticket_id", 
            "author", 
            "params", 
            "message", 
            "timestamp", 
            "date"
        ], 
        [
            "", 
            $ticket_ID, 
            $user, 
            json_encode(["admin" => false]), 
            $message,
            $time,
            date("H:i:s d.m.Y")
        ]);

        $_SESSION["Tickets"]["redirect_ticket_id"] = $ticket_ID;

        return true;
    }

    public function ticketCallback()
    {
        switch ($this->vars["callback"]) {
            case "redirect":
                $sess = Session::init();
                if (!$sess->isExist("Tickets/redirect_ticket_id")) {
                    return null;
                }
                $id = $sess->getData("Tickets/redirect_ticket_id");
                #unset($_SESSION["Tickets"]["redirect_ticket_id"]);
                return $id;
                #Utils::header("./?ticket-open&id=" . $id);
            break;
        }
    }

    private function loadConfig($type)
    {
        $types = ["groups", "reasons"];
        if (!in_array($type, $types))
        {
            $this->error->catchError("Undefined config loader type!", debug_backtrace());
            return null;
        }

        switch($type) {
            case "groups":
                $g = $this->config->getConfig("Main/ticket-categories");
                foreach ($g as $group_name => $group_data) {
                    if (empty($group_data["name"])) {
                        $this->error->catchError("Can't find name for group {$group_name}, skipping.", debug_backtrace());
                        continue;
                    }
                    $this->ticket_groups[$group_name] = $group_data["name"];
                }
            break;
            case "reasons":
                $r = $this->config->getConfig("Main/ticket-reasons");
                foreach ($r as $reason_data) {
                    if (!array_key_exists($reason_data["for"], $this->ticket_groups)) {
                        $this->error->catchError("Undefined ticket_group {$reason_data["for"]}", debug_backtrace());
                        continue;
                    }
                    if (empty($reason_data["enabled"]) || $reason_data["enabled"] === false) {
                        continue;
                    }
                    $this->ticket_reasons[$reason_data["for"]][] =$reason_data["displayname"];
                }
            break;
        }
    }
}
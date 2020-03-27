<?php

namespace patrick115\Adminka;

use patrick115\Main\Config;
use patrick115\Main\Error;
use patrick115\Main\Database;

class Tickets 
{

    private $username;

    const TICKET_OPEN = 1;
    const TICKET_CLOSE = 0;

    private $ticket_groups = [];
    private $ticket_reasons = [];

    private $database;
    private $config;
    private $error;

    public function __construct($username)
    {
        $this->database = Database::init();
        $this->config = Config::init();
        $this->error = Error::init();

        $this->loadConfig("groups");
        $this->loadConfig("reasons");

        $this->username = $username;
    }


    public function getReasons()
    {
        return $this->ticket_reasons;
    }

    public function getGroups()
    {
        return $this->ticket_groups;
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
                    $this->ticket_reasons[$reason_data["for"]][] = [
                        "displayname" => $reason_data["displayname"],
                    ];
                }
            break;
        }
    }
}
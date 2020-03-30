<?php

namespace patrick115\Adminka;

use patrick115\Main\Config;
use patrick115\Main\Error;
use patrick115\Main\Database;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Main;

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

    private function get_current_ticket_id()
    {
        $router = Main::getApp("\patrick115\Main\Router");
        $id = $router->getURIData("id", false);
        return $id;
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
                unset($_SESSION["Tickets"]["redirect_ticket_id"]);
                Utils::header("./?ticket-view&id=" . $id);
            break;
            case "check_ticket":
                $router = Main::getApp("\patrick115\Main\Router");
                $id = $router->getURIData("id", false);

                if (Utils::newEmpty($id) || !is_numeric($id) || $id < 1) {
                    $_SESSION["Request"]["Errors"] = ["Neplatné id tiketu!"];
                    Utils::header("./?main");
                }

                $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);
                if (!$rv || $this->database->num_rows($rv) == 0) {
                    $_SESSION["Request"]["Errors"] = ["Tiket s id {$id} nenalezen!"];
                    Utils::header("./?main");
                }
            break;
            case "player_list":
                echo "bagr";
                $sess = Session::init();
                $username = $sess->getData("Account/User/Username");
                $user_id = Utils::getClientID($username);
                $rv = $this->database->select(["id", "title", "reason", "create_timestamp", "waiting_for"], "adminka_tickets`.`tickets_list", "", "author", $user_id);

                if ($this->database->num_rows($rv) > 0) {

                    $return = "<table class=\"table table-striped\">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Název</th>
                                    <th>Typ</th>
                                    <th>Stav</th>
                                    <th>Datum založení</th>
                                    <th>Akce</th>
                                </tr>
                            </thead>
                            <tbody>";
                    while ($row = $rv->fetch_assoc()) {
                        $return .= "<tr>
                        <td>{$row["id"]}</td>
                        <td>{$row["title"]}</td>
                        <td>{$row["reason"]}</td>
                        <td>";
                        switch ($row["waiting_for"]) {
                            case self::TICKET_CLOSE:
                                $return .= "<span class=\"badge badge-danger\">Uzavřen</span>";
                            break;
                            case self::TICKET_WAITING_FOR_ADMIN:
                                $return .= "<span class=\"badge badge-yellow\">Čeká na odpověď Podpory</span>";
                            break;
                            case self::TICKET_WAITING_FOR_USER:
                                $return .= "<span class=\"badge badge-yellow\">Čeká na odpověď Podpory</span>";
                            break;
                        }
                        $return .= "</td>
                        <td>" . date("H:i:s d.m.Y", $row["create_timestamp"]) . "</td>
                        <td><a href=\"?ticket-view&id={$row["id"]}\"><button type=\"button\" class=\"btn btn-small\">Otevřít</button>
                        </tr>";

                    }
                    $return .= "</tbody>
                    </table>";
                } else {
                    $return = "<div class=\"alert alert-danger alert-dismissible\" style=\"text-align:center;\">
                    Žádné tikety nenalezeny!
                    </div>";
                }
                return $return;
            break;
            case "chat":
                $id = $this->get_current_ticket_id();

                $rv = $this->database->select(["author", "params", "message", "date"], "adminka_tickets`.`tickets_messages", "ORDER BY `tickets_messages`.`id` DESC", "ticket_id", $id);

                if (!$rv || $this->database->num_rows($rv) == 0) {
                    return "<div class=\"alert alert-danger alert-dismissible\" style=\"text-align:center;\">
                    Někde nastala chyba
                    </div>";
                }
                $return = "";
                while ($row = $rv->fetch_assoc()) {
                    $data = json_decode($row["params"] , 1);

                    $username = Utils::getUserByClientId($row["author"]);

                    $rank = Main::Create("\patrick115\Adminka\Players\Rank", [$username]);
                    $player_rank = $rank->getRank();
                    $rank_color = \patrick115\Main\Config::init()->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($player_rank)];
                    
                    $skin = Main::Create("\patrick115\Minecraft\Skins", [$username]);
                    $skin = $skin->getSkin();

                    if ($data["admin"] === false) {
                        $return .= "<div class=\"direct-chat-msg\">
                        <div class=\"direct-chat-info clearfix\">
                            <span class=\"direct-chat-name pull-left\"><span class=\"rank\" style=\"color:{$rank_color};font-weight:bold;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$player_rank}</span> {$username}</span>
                            <span class=\"direct-chat-timestamp pull-right\">{$row["date"]}</span>
                        </div>
                        <img class=\"direct-chat-img\" src=\"{$skin}\">
                        <div class=\"direct-chat-text\">
                            " . str_replace("\r\n", "<br>", $row["message"]) ."
                        </div>
                    </div>";
                    } else {
                        $return .= "<div class=\"direct-chat-msg right\">
                        <div class=\"direct-chat-info clearfix\">
                            <span class=\"direct-chat-name pull-right\"><span class=\"rank\" style=\"color:{$rank_color};font-weight:bold;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$player_rank}</span> {$username}</span>
                            <span class=\"direct-chat-timestamp pull-left\">{$row["date"]}</span>
                        </div>
                        <img class=\"direct-chat-img\" src=\"{$skin}\">
                        <div class=\"direct-chat-text\">
                            " . str_replace("\r\n", "<br>", $row["message"]) ."
                        </div>
                    </div>";
                    }
                }
                return $return;
            break;
            default:
                return "No process found";
            break;
        }
        return "";
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
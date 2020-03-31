<?php

namespace patrick115\Adminka;

use patrick115\Main\Config;
use patrick115\Main\Error;
use patrick115\Main\Database;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Main;
use patrick115\Adminka\Logger;

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
    private $logger;

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
        $this->logger = Logger::init();
        

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
        $user = Utils::getClientID($this->username);
        $title = str_replace(["&amp;", "\\r", "\\n"], ["&", "", ""], trim($this->vars["name"]));
        $for = 
        rtrim(
            ltrim(
                Utils::getPackage([1 => $this->vars["type"]])
                , "%%TICKET_ID;")
            , ";TICKET_ID%%"
        );
        $message = str_replace(["&amp;", "\\r", "\\n"], ["&", "", ""], trim($this->vars["message"]));

        var_dump($message);
        #die();

        if (mb_strlen($title) > 40) {
            define("ERROR", ["Název tiketu nesmí obsahovat více, než 40 znaků"]);
            return false;
        }

        if (mb_strlen($message) > 200) {
            define("ERROR", ["Zpráva nesmí být delší než 200 znaků"]);
            return false;
        }

        if (mb_strlen($title) < 5) {
            define("ERROR", ["Název tiketu je příliš krátký"]);
            return false;
        }

        if (mb_strlen($message) < 20) {
            define("ERROR", ["Zpráva je příliš krátká"]);
            return false;
        }

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
                $sess = Session::init();
                $username = $sess->getData("Account/User/Username");
                $user_id = Utils::getClientID($username);
                $rv = $this->database->select(["id", "title", "reason", "create_timestamp", "waiting_for"], "adminka_tickets`.`tickets_list", "", "author", $user_id);

                if ($this->database->num_rows($rv) > 0) {

                    $return = "<table class=\"table table-striped\" id=\"ticket-list\">
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
                                $return .= "<span class=\"badge badge-green\">Čeká na odpověď Hráče</span>";
                            break;
                        }
                        $return .= "</td>
                        <td>" . date("H:i:s d.m.Y", (int) $row["create_timestamp"]) . "</td>
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

                $alerts = [];

                $rv = $this->database->select(["type", "message", "after_message", "date"], "adminka_tickets`.`tickets_alerts", "", "ticket_id", $id);

                while ($row = $rv->fetch_assoc()) {

                    $message = $row["message"];

                    switch ($row["type"]) {
                        case "change":
                            $string = "<div class=\"alert alert-warning text-chat\">
                            $message
                        </div>";
                        break;
                        case "open":
                            $string = "<div class=\"alert alert-success text-chat\">
                            $message
                        </div>";
                        break;
                        case "close":
                            $string = "<div class=\"alert alert-danger text-chat\">
                            $message
                        </div>";
                        break;
                    }

                    $alerts[$row["after_message"]] = $string;

                }



                $rv = $this->database->select(["id", "author", "params", "message", "date"], "adminka_tickets`.`tickets_messages", "ORDER BY `tickets_messages`.`id` DESC", "ticket_id", $id);

                if (!$rv || $this->database->num_rows($rv) == 0) {
                    return "<div class=\"alert alert-danger alert-dismissible\" style=\"text-align:center;\">
                    Někde nastala chyba
                    </div>";
                }
                $return = "";
                while ($row = $rv->fetch_assoc()) {

                    if (!empty($alerts[$row["id"]])) {
                        $return .= $alerts[$row["id"]];
                    }

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
                            " . str_replace(["\r\n", "&amp;"], ["<br>", "&"], $row["message"]) ."
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
                            " . str_replace(["\r\n", "&amp;"], ["<br>", "&"], $row["message"]) ."
                        </div>
                    </div>";
                    }
                }
                return $return;
            break;
            case "send_message_check":
                $id = $this->get_current_ticket_id();
                $rv = $this->database->select(["waiting_for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);
                
                if ($rv->fetch_object()->waiting_for == self::TICKET_CLOSE) {
                    return "<div class=\"alert alert-danger text-chat\">
                    Tiket je uzavřen, nelze do něj odepisovat!
                    </div>";
                } else {
                    return "<form method=\"post\" action=\"./requests.php\">
                    <input type=\"hidden\" name=\"method\" value=\"ticket-send-message\" required>
                    <input type=\"hidden\" name=\"source_page\" value=\"?ticket-view|id=" . $id . "\" required>
                    <input type=\"hidden\" name=\"CSRF_token\" value=\"%%CSRF_Token%%\" required>
                    <input type=\"hidden\" name=\"ticket_id\" value=\"" . Utils::createPackage(Utils::randomString(5) . ";" . $id . ";" . Utils::randomString(5))[1] . "\" required>
                    <div class=\"form-group\">
                        <label for=\"message\">Zpráva</label>
                        <textarea type=\"text\" class=\"form-control\" id=\"message\" name=\"message\" required></textarea>
                    </div>
                    <button type=\"submit\" class=\"btn btn-light\">Odeslat zprávu</button>
                </form>";
                }
            break;
            case "player_info":
                $username = $this->username;

                $ip = Utils::getIpOfUser($username);
                $rank = Main::Create("\patrick115\Adminka\Players\Rank", [$username]);
                $stats = Main::Create("\patrick115\Minecraft\Stats", [$username]);
                $ip_info = json_decode(
                    file_get_contents("http://ip-api.com/json/{$ip}")
                , 1);

                $country = empty($ip_info["countryCode"]) ? "undefined_Undefined" : (($ip_info["countryCode"] == "CZ") ? "cs_CZ" : "sk_SK"); 
                $rank = $rank->getRank();
                $expiry = str_replace("Nevlastníš", "Nevlastní", $stats->getVipExpiry());
                $city = !empty($ip_info["city"]) ? $ip_info["city"] : "Neznámé";
                $banned = $stats->isBanned();
                $money = $stats->getMoney();
                $gems = $stats->getGems();
                

                return "<tr>
                <td>IP:</td>
                <td>{$ip} <img src=\"//%%domain%%/public/imgs/{$country}.png\" class=\"flag\"></td>
            </tr>
            <tr>
                <td>Město:</td>
                <td>{$city}</td>
            </tr>
            <tr>
                <td>Rank:</td>
                <td>{$rank}</td>
            </tr>
            <tr>
                <td>Vyprší:</td>
                <td>{$expiry}</td>
            </tr>
            <tr>
                <td>Přistup s VPN:</td>
                <td>{$stats->getAntiVPNStatus()}</td>
            </tr>
            <tr>
                <td>Ban:</td>
                <td>{$banned}</td>
            </tr>
            <tr>
                <td>Peníze:</td>
                <td>{$money}</td>
            </tr>
            <tr>
                <td>Gemy:</td>
                <td>{$gems}</td>
            </tr>";

            break;
            case "send-message":
                $hashed_id = $this->vars["ticket_id"];
                $message = str_replace(["&amp;", "\\r", "\\n"], ["&", "", ""], trim($this->vars["message"]));
                $username = $this->username;

                $id = @explode(";",
                    @Utils::getPackage(
                        [1 => $hashed_id]
                    )
                )[1];

                if ($id != explode("=", explode("|", $this->vars["source_page"])[1])[1]) {
                    define("ERROR", ["Id tiketu je neplatné"]);
                    return false;
                }

                $rv = $this->database->select(["author", "waiting_for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);

                if (!$rv || $this->database->num_rows($rv) == 0) {
                    $this->logger->log("Hráč obešel hash!", "warning", true);
                    define("ERROR", ["Id tiketu je neplatné"]);
                    return false;
                }

                $author = Utils::getUserByClientId($rv->fetch_object()->author);

                if ($author != $username) {
                    $this->logger->log("Hráč se snaži upravovat cizí tikety!", "critical", true);
                    define("ERROR", ["Nejsi majitelem tohoto tiketu!"]);
                    return false;
                }

                if ($rv->fetch_object()->waiting_for == self::TICKET_CLOSE) {
                    $this->logger->log("Hráč se snaži upravovat zavřené tikety!", "critical", true);
                    define("ERROR", ["Tento tiket je uzavřen!"]);
                    return false;
                }

                if (mb_strlen($message) > 200) {
                    define("ERROR", ["Zpráva nesmí být delší než 200 znaků"]);
                    return false;
                }
        
                if (mb_strlen($message) < 10) {
                    define("ERROR", ["Zpráva je příliš krátká"]);
                    return false;
                }

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
                    $id,
                    Utils::getClientID($username),
                    json_encode(["admin" => false]),
                    $message,
                    time(),
                    date("H:i:s d.m.Y")
                ]
                );

                return true;
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
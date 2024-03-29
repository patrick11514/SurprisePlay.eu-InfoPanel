<?php

/**
 * Main class for tickets
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

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
    /**
     * Username of current user
     * @var string
     */
    private $username;

    /**
     * Main constants
     * @var int
     */
    const TICKET_WAITING_FOR_USER = 0;
    const TICKET_WAITING_FOR_ADMIN = 1;
    const TICKET_CLOSE = 2;

    /**
     * Groups in tickets
     * @var array
     */
    private $ticket_groups = [];
    /**
     * Reasons for tickets
     * @var array
     */
    private $ticket_reasons = [];

    /**
     * Database class
     * @var object
     */
    private $database;
    /**
     * Config class
     * @var object
     */
    private $config;
    /**
     * Error class
     * @var object
     */
    private $error;
    /**
     * Logger class
     * @var object
     */
    private $logger;

    /**
     * Methods for POST
     * @var array
     */
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
        ],
        "allowVPN" => [
            "reason",
            "confirm"
        ],
        "changeGroup" => [
            "group",
            "ticket_id"
        ],
        "templater_get_admin_group" => [
            "username"
        ],
    ];
    /**
     * Posts from POST
     * @var array
     */
    private $vars;

    /**
     * Construct function
     * @param array $data -Data from Post
     * @return bool
     */
    public function __construct(array $data)
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

    /**
     * Get reasons from config
     * @return array
     */
    public function getReasons()
    {
        return $this->ticket_reasons;
    }

    /**
     * Get ticket groups from config
     * @return array
     */
    public function getGroups()
    {
        return $this->ticket_groups;
    }

    /**
     * Create new ticket
     * @return bool
     */
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

        if (mb_strlen($title) > 40) {
            define("ERROR", ["Název tiketu nesmí obsahovat více, než 40 znaků"]);
            return false;
        }

        if (mb_strlen($message) > 20000) {
            define("ERROR", ["Zpráva nesmí být delší než 20000 znaků"]);
            return false;
        }

        if (mb_strlen($title) < 5) {
            define("ERROR", ["Název tiketu je příliš krátký"]);
            return false;
        }

        if (mb_strlen($message) < 10) {
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

        $rv = $this->database->execute("SELECT `id` FROM `adminka_tickets`.`tickets_list` ORDER BY `tickets_list`.`id` DESC LIMIT 1;", true);

        $potencial_id = ($rv->fetch_object()->id) + 1;

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
            
        if (!$rv || $this->database->num_rows($rv) == 0) {
            $this->database->delete("adminka_tickets`.`tickets_list", ["id"], [$potencial_id]);
            return false;
        }

        $ticket_ID = $rv->fetch_object()->id;

        if (!empty($this->vars["img_url"])) {
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
                json_encode(["admin" => false, "image" => true]), 
                $this->vars["img_url"],
                $time,
                date("H:i:s d.m.Y")
            ]);
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

    /**
     * Get current ticket id
     * @return int
     */
    private function get_current_ticket_id()
    {
        $router = Main::getApp("\patrick115\Main\Router");
        $id = $router->getURIData("id", false);
        return $id;
    }

    /**
     * Ticket callback for callbacks from
     * templater
     * @return mixed
     */
    public function ticketCallback()
    {
        switch ($this->vars["callback"]) {

            /**
             * TICKET CREATE
             */

            case "redirect":
                $sess = Session::init();
                if (!$sess->isExist("Tickets/redirect_ticket_id")) {
                    return null;
                }
                $id = $sess->getData("Tickets/redirect_ticket_id");
                unset($_SESSION["Tickets"]["redirect_ticket_id"]);
                Utils::header("./?ticket-view&id=" . $id);
            break;

            /**
             * PLAYER_TICKET_VIEW
             */

            case "check_ticket":
                $router = Main::getApp("\patrick115\Main\Router");
                $id = $router->getURIData("id", false);

                if (Utils::newEmpty($id) || !is_numeric($id) || $id < 1) {
                    $_SESSION["Request"]["Errors"] = ["Neplatné id tiketu!"];
                    Utils::header("./?main");
                }

                $rv = $this->database->select(["id", "author"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);
                if (!$rv || $this->database->num_rows($rv) == 0) {
                    $_SESSION["Request"]["Errors"] = ["Tiket s id {$id} nenalezen!"];
                    Utils::header("./?main");
                }

                $user_id = Utils::getClientID($this->username);
                if ($rv->fetch_object()->author != $user_id) {
                    $_SESSION["Request"]["Errors"] = ["Nevlastníš tento tiket!"];
                    Utils::header("./?main");
                }
            break;
            case "chat":
                $id = $this->get_current_ticket_id();

                $alerts = [];

                $rv = $this->database->select(["type", "message", "after_message", "date"], "adminka_tickets`.`tickets_alerts", "ORDER BY `tickets_alerts`.`id` DESC", "ticket_id", $id);

                while ($row = $rv->fetch_assoc()) {

                    $message = $row["message"];

                    switch ($row["type"]) {
                        case "change":
                            $string = "<center>
                            <span class=\"direct-chat-timestamp\" style=\"font-size:12px\">{$row["date"]}</span>
                            </center>
                            <div class=\"alert alert-warning text-chat\">
                            $message
                        </div>";
                        break;
                        case "open":
                            $string = "<center>
                            <span class=\"direct-chat-timestamp\" style=\"font-size:12px\">{$row["date"]}</span>
                            </center>
                            <div class=\"alert alert-success text-chat\">
                            $message
                        </div>";
                        break;
                        case "close":
                            $string = "
                            <center>
                            <span class=\"direct-chat-timestamp\" style=\"font-size:12px\">{$row["date"]}</span>
                            </center>
                            <div class=\"alert alert-danger text-chat\">
                            $message
                        </div>";
                        break;
                    }

                    $alerts[$row["after_message"]][] = $string;

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
                        foreach ($alerts[$row["id"]] as $alert) {
                            $return .= $alert;
                        }
                    }

                    $data = json_decode($row["params"] , 1);

                    $username = Utils::getUserByClientId($row["author"]);

                    $rank = Main::Create("\patrick115\Adminka\Players\Rank", [$username]);
                    $player_rank = $rank->getRank();
                    $rank_color = \patrick115\Main\Config::init()->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($player_rank)];
                    
                    $skin = Main::Create("\patrick115\Minecraft\Skins", [$username]);
                    $skin = $skin->getSkin();

                    if ($data["admin"] === false) {
                        if (!empty($data["image"]) && $data["image"] === true) {
                            $return .= "<div class=\"direct-chat-msg\">
                        <div class=\"direct-chat-info clearfix\">
                            <span class=\"direct-chat-name pull-left\"><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$player_rank}</span> {$username}</span>
                            <span class=\"direct-chat-timestamp pull-right\">{$row["date"]}</span>
                        </div>
                        <img class=\"direct-chat-img\" src=\"{$skin}\">
                        <div class=\"direct-chat-text\">
                            <img src=\"//" . str_replace(["%time%"], [time()], str_replace(["&amp;"], ["&"], $row["message"])) . "\" style=\"width:100%;\">
                        </div>
                    </div>";
                        } else {
                            $return .= "<div class=\"direct-chat-msg\">
                        <div class=\"direct-chat-info clearfix\">
                            <span class=\"direct-chat-name pull-left\"><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$player_rank}</span> {$username}</span>
                            <span class=\"direct-chat-timestamp pull-right\">{$row["date"]}</span>
                        </div>
                        <img class=\"direct-chat-img\" src=\"{$skin}\">
                        <div class=\"direct-chat-text\">
                            " . str_replace(["\r\n", "&amp;"], ["<br>", "&"], $row["message"]) ."
                        </div>
                    </div>";
                        }
                    } else {
                        if (!empty($data["image"]) && $data["image"] === true) {
                        $return .= "<div class=\"direct-chat-msg right\">
                        <div class=\"direct-chat-info clearfix\">
                            <span class=\"direct-chat-name pull-right\"><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};font-weight:bold;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$player_rank}</span> {$username}</span>
                            <span class=\"direct-chat-timestamp pull-left\">{$row["date"]}</span>
                        </div>
                        <img class=\"direct-chat-img\" src=\"{$skin}\">
                        <div class=\"direct-chat-text\">
                            <img src=\"//" . str_replace(["%time%"], [time()], str_replace(["&amp;"], ["&"], $row["message"])) . "\" style=\"width:100%;\">
                        </div>
                    </div>";
                        } else {
                        $return .= "<div class=\"direct-chat-msg right\">
                        <div class=\"direct-chat-info clearfix\">
                            <span class=\"direct-chat-name pull-right\"><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};font-weight:bold;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$player_rank}</span> {$username}</span>
                            <span class=\"direct-chat-timestamp pull-left\">{$row["date"]}</span>
                        </div>
                        <img class=\"direct-chat-img\" src=\"{$skin}\">
                        <div class=\"direct-chat-text\">
                            " . str_replace(["\r\n", "&amp;"], ["<br>", "&"], $row["message"]) ."
                        </div>
                    </div>";
                        }
                    }
                }
                return $return;
            break;
            case "send_message_check":
                $id = $this->get_current_ticket_id();
                $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_banned_users", "LIMIT 1", "user_id", Utils::getClientID($this->vars["username"]));

                if (!$rv || $this->database->num_rows($rv) == 0) {
                    $blocked = false;
                } else {
                    $blocked = true;
                }

                $rv = $this->database->select(["waiting_for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);
                
                if ($rv->fetch_object()->waiting_for == self::TICKET_CLOSE) {
                    return "<div class=\"alert alert-danger text-chat\">
                    Tiket je uzavřen, nelze do něj odepisovat!
                    </div>";
                } elseif ($blocked === true) {
                    return "<div class=\"alert alert-danger text-chat\">
                    Jsi na tiketech zablokován, nelze do něj odepisovat!
                    </div>";
                } else {
                    return "<form method=\"post\" action=\"./requests.php\"  enctype=\"multipart/form-data\">
                    <input type=\"hidden\" name=\"method\" value=\"ticket-send-message\" required>
                    <input type=\"hidden\" name=\"source_page\" value=\"?ticket-view|id=" . $id . "\" required>
                    <input type=\"hidden\" name=\"CSRF_token\" value=\"%%CSRF_Token%%\" required>
                    <input type=\"hidden\" name=\"ticket_id\" value=\"" . Utils::createPackage(Utils::randomString(5) . ";" . $id . ";" . Utils::randomString(5))[1] . "\" required>
                    <div class=\"form-group\">
                        <label for=\"file\">Přiložit obrázek <span style=\"color:red;font-size:small;\">maximálně %%ticket_max_file_size%%</span></label>
                        <div class=\"input-group mb-3\">
                            <div class=\"custom-file\">
                                <input type=\"file\" class=\"custom-file-input\" id=\"file\" name=\"file\">
                                <label class=\"custom-file-label\" for=\"file\">Vybrat soubor</label>
                            </div>
                        </div>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"message\">Zpráva <span style=\"color:red;font-size:small;\">minimálně 10 znaků</span></label>
                        <textarea type=\"text\" class=\"form-control\" id=\"message\" name=\"message\" maxlength=\"1000\" required></textarea>
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

                if (@explode(";",
                    @Utils::getPackage(
                       [1 => $hashed_id]
                    ))[0] == "AD") {
                    define("ERROR", ["Nelze poslat admin zpravu do normalniho chatu!"]);
                    return false;
                } 

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

                $rv = $rv->fetch_object();

                $author = Utils::getUserByClientId($rv->author);

                if ($author != $username) {
                    $this->logger->log("Hráč se snaži upravovat cizí tikety!", "critical", true);
                    define("ERROR", ["Nejsi majitelem tohoto tiketu!"]);
                    return false;
                }

                if ($rv->waiting_for == self::TICKET_CLOSE) {
                    $this->logger->log("Hráč se snaži upravovat zavřené tikety!", "critical", true);
                    define("ERROR", ["Tento tiket je uzavřen!"]);
                    return false;
                }

                if (mb_strlen($message) > 20000) {
                    define("ERROR", ["Zpráva nesmí být delší než 20000 znaků"]);
                    return false;
                }
        
                if (mb_strlen($message) <= 10) {
                    define("ERROR", ["Zpráva je příliš krátká"]);
                    return false;
                }

                if (!empty($this->vars["img_url"])) {
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
                        json_encode(["admin" => false, "image" => true]),
                        $this->vars["img_url"],
                        time(),
                        date("H:i:s d.m.Y")
                    ]
                    );
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

                $this->database->update("adminka_tickets`.`tickets_list", "id", $id, ["waiting_for"], [self::TICKET_WAITING_FOR_ADMIN]);

                return true;
            break;

            /**
             * TICKETS PLAYER LIST
             */

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
                        <td>" . str_replace("&amp;", "&", $row["title"]) . "</td>
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

            /**
             * TICKET ADMIN LIST
             */

            case "check_if_perms":
                $router = Main::getApp("\patrick115\Main\Router");
                $type = $router->getURIData("type", false);
                if (empty($type)) {
                    $_SESSION["Request"]["Errors"] = ["Typ je prázný"];
                    Utils::header("./?main");
                }

                $groups = $this->config->getConfig("Main/ticket-group-access");

                if (!array_key_exists($type, $groups)) {
                    $_SESSION["Request"]["Errors"] = ["Neplatný typ"];
                    Utils::header("./?main");
                }

                $perms = Main::Create("\patrick115\Adminka\Permissions", [""]);

                if (!$perms->getUser($this->username)->havePermission()->inGroup($groups[$type])) {
                    $_SESSION["Request"]["Errors"] = ["Na toto nemáš oprávnění"];
                    Utils::header("./?main");
                }
            break;
            case "get_admin_list":
                $router = Main::getApp("\patrick115\Main\Router");
                $type = $router->getURIData("type", false);

                $rv = $this->database->select(["id", "author", "title", "waiting_for", "reason", "create_timestamp"], "adminka_tickets`.`tickets_list", "", "for", $type);

                if (!$rv || $this->database->num_rows($rv) == 0) {
                    return "<div class=\"alert alert-danger alert-dismissible\" style=\"text-align:center;\">
                    Žádné tikety nenalezeny!
                    </div>";
                } 

                $return = "<table id=\"ticket-list\" class=\"table table-striped\">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Název</th>
                        <th>Hráč</th>
                        <th>Typ</th>
                        <th>Stav</th>
                        <th>Datum založení</th>
                        <th>Akce</th>
                        <th>Smazat</th>
                    </tr>
                </thead>
                <tbody>";

                while($row = $rv->fetch_assoc()) {
                    $return .= "<tr>
                        <td>{$row["id"]}</td>
                        <td>" . str_replace("&amp;", "&", $row["title"]) . "</td>";
                        $username = Utils::getUserByClientId($row["author"]);
                    $return .= "
                        <td>{$username}</td>
                        <td>{$row["reason"]}</td>
                        <td>";
                        if ($row["waiting_for"] == self::TICKET_CLOSE) {
                            $return .= "<span class=\"badge badge-danger\">Uzavřen</span>";
                            $scnd_button = "<button type=\"submit\" class=\"btn btn-small btn-green\">Otevřít</button>";
                            $cls = Utils::createPackage(Utils::randomString(10) . ";open;" . Utils::randomString(10))[1];
                        } else if ($row["waiting_for"] == self::TICKET_WAITING_FOR_ADMIN) {
                            $return .= "<span class=\"badge badge-yellow\">Čeká na odpověď Podpory</span>";
                            $scnd_button = "<button type=\"submit\" class=\"btn btn-small btn-red\">Uzavřít</button>";
                            $cls = Utils::createPackage(Utils::randomString(10) . ";close;" . Utils::randomString(10))[1];
                        } else if ($row["waiting_for"] == self::TICKET_WAITING_FOR_USER) {
                            $return .= "<span class=\"badge badge-green\">Čeká na odpověď Hráče</span>";
                            $scnd_button = "<button type=\"submit\" class=\"btn btn-small btn-red\">Uzavřít</button>";
                            $cls = Utils::createPackage(Utils::randomString(10) . ";close;" . Utils::randomString(10))[1];
                        }
                    $return .= "</td>
                        <td>" . date("H:i:s d.m.Y", (int) $row["create_timestamp"]) ."</td>
                        <td>
                        <form method=\"post\" action=\"./requests.php\">
                        <a href=\"./?ticket-view-admin&id={$row["id"]}\">
                            <button type=\"button\" class=\"btn btn-small\">Zobrazit</button>
                        </a>
                        
                        <input type=\"hidden\" name=\"method\" value=\"toggle-ticket\" required>
                        <input type=\"hidden\" name=\"source_page\" value=\"?ticket-list-admin|type={$type}\" required>
                        <input type=\"hidden\" name=\"CSRF_token\" value=\"%%CSRF_Token%%\" required>
                        <input type=\"hidden\" name=\"value\" value=\"{$cls}\" required>
                        <input type=\"hidden\" name=\"ticket_id\" value=\"" . Utils::createPackage(Utils::randomString(10) . ";{$row["id"]};" . Utils::randomString(10))[1] . "\" required>
                        {$scnd_button}
                        </form>
                        </td>
                    ";

                    $app = Session::init();

                    $token = $app->getData("Security/CRF/token");

                    $return .= "<td><form method=\"post\" action=\"./requests.php\">
                        <input type=\"hidden\" name=\"method\" value=\"delete-ticket\" required>
                        <input type=\"hidden\" name=\"source_page\" value=\"?ticket-list-admin|type={$type}\" required>
                        <input type=\"hidden\" name=\"CSRF_token\" value=\"" .$token .  "\" required>
                        <input type=\"hidden\" name=\"id\" value=\"" .Utils::createPackage(Utils::randomString(10) . ";{$row["id"]};" . Utils::randomString(12))[1] . "\" required>
                        <button type=\"submit\" style=\"background:none;border:none;\"><i class=\"fas fa-trash\"></i></button></td>
                    </form></td>
                    </tr>";
                }
                $return .= "</tbody></table>";
                return $return;
            break;

            /**
             * TICKET ADMIN VIEW
             */

            case "check_ticket_admin":
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

                $rv = $this->database->select(["for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);

                $groups = $this->config->getConfig("Main/ticket-group-access");

                $perms = Main::Create("\patrick115\Adminka\Permissions", [""]);

                if (!$perms->getUser($this->username)->havePermission()->inGroup($groups[$rv->fetch_object()->for])) {
                    $_SESSION["Request"]["Errors"] = ["Na zobrazení tohoto tiketu nemáš oprávnění!"];
                    Utils::header("./?main");
                }
            break;
            case "chat_admin":
                $id = $this->get_current_ticket_id();

                $alerts = [];

                $rv = $this->database->select(["type", "message", "after_message", "date"], "adminka_tickets`.`tickets_alerts", "ORDER BY `tickets_alerts`.`id` DESC", "ticket_id", $id);

                while ($row = $rv->fetch_assoc()) {

                    $message = $row["message"];

                    switch ($row["type"]) {
                        case "change":
                            $string = "<center>
                            <span class=\"direct-chat-timestamp\" style=\"font-size:12px\">{$row["date"]}</span>
                            </center>
                            <div class=\"alert alert-warning text-chat\">
                            $message
                        </div>";
                        break;
                        case "open":
                            $string = "<center>
                            <span class=\"direct-chat-timestamp\" style=\"font-size:12px\">{$row["date"]}</span>
                            </center>
                            <div class=\"alert alert-success text-chat\">
                            $message
                        </div>";
                        break;
                        case "close":
                            $string = "
                            <center>
                            <span class=\"direct-chat-timestamp\" style=\"font-size:12px\">{$row["date"]}</span>
                            </center>
                            <div class=\"alert alert-danger text-chat\">
                            $message
                        </div>";
                        break;
                    }

                    $alerts[$row["after_message"]][] = $string;

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
                        foreach ($alerts[$row["id"]] as $alert) {
                            $return .= $alert;
                        }
                    }

                    $data = json_decode($row["params"] , 1);

                    $username = Utils::getUserByClientId($row["author"]);

                    $rank = Main::Create("\patrick115\Adminka\Players\Rank", [$username]);
                    $player_rank = $rank->getRank();
                    $rank_color = \patrick115\Main\Config::init()->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($player_rank)];
                    
                    $skin = Main::Create("\patrick115\Minecraft\Skins", [$username]);
                    $skin = $skin->getSkin();

                    if ($data["admin"] === false) {
                        if (!empty($data["image"]) && $data["image"] === true) {
                        $return .= "<div class=\"direct-chat-msg right\">
                            <div class=\"direct-chat-info clearfix\">
                                <span class=\"direct-chat-name pull-right\"><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$player_rank}</span> {$username}</span>
                                <span class=\"direct-chat-timestamp pull-left\">{$row["date"]}</span>
                            </div>
                            <img class=\"direct-chat-img\" src=\"{$skin}\">
                            <div class=\"direct-chat-text\">
                                <img src=\"//" . str_replace(["%time%"], [time()], str_replace(["&amp;"], ["&"], $row["message"])) . "\" style=\"width:100%;\">
                            </div>
                        </div>";
                        } else {
                        $return .= "<div class=\"direct-chat-msg right\">
                        <div class=\"direct-chat-info clearfix\">
                            <span class=\"direct-chat-name pull-right\"><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$player_rank}</span> {$username}</span>
                            <span class=\"direct-chat-timestamp pull-left\">{$row["date"]}</span>
                        </div>
                        <img class=\"direct-chat-img\" src=\"{$skin}\">
                        <div class=\"direct-chat-text\">
                            " . str_replace(["\r\n", "&amp;"], ["<br>", "&"], $row["message"]) ."
                        </div>
                    </div>";
                        }
                    } else {
                        if (!empty($data["image"]) && $data["image"] === true) {
                        $return .= "<div class=\"direct-chat-msg\">
                            <div class=\"direct-chat-info clearfix\">
                                <span class=\"direct-chat-name pull-left\"><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$player_rank}</span> {$username}</span>
                                <span class=\"direct-chat-timestamp pull-right\">{$row["date"]}</span>
                            </div>
                            <img class=\"direct-chat-img\" src=\"{$skin}\">
                            <div class=\"direct-chat-text\">
                                <img src=\"//" . str_replace(["%time%"], [time()], str_replace(["&amp;"], ["&"], $row["message"])) . "\" style=\"width:100%;\">
                            </div>
                        </div>";
                        } else {
                        $return .= "<div class=\"direct-chat-msg\">
                        <div class=\"direct-chat-info clearfix\">
                            <span class=\"direct-chat-name pull-left\"><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$player_rank}</span> {$username}</span>
                            <span class=\"direct-chat-timestamp pull-right\">{$row["date"]}</span>
                        </div>
                        <img class=\"direct-chat-img\" src=\"{$skin}\">
                        <div class=\"direct-chat-text\">
                            " . str_replace(["\r\n", "&amp;"], ["<br>", "&"], $row["message"]) ."
                        </div>
                    </div>";
                        }
                    }
                }
                return $return;
            break;
            case "player_info_admin":
                $id = $this->get_current_ticket_id();
                
                $rv = $this->database->select(["author", "for", "title"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);

                $rv = $rv->fetch_object();

                $username = Utils::getUserByClientId($rv->author);
                $group = $this->config->getConfig("Main/ticket-categories")[$rv->for]["name"];
                $ticket_name = str_replace("&amp;", "&", $rv->title);

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
                <td>Název tiketu</td>
                <td>{$ticket_name}</td>
            </tr>
            <tr>
                <td>ID Tiketu:</td>
                <td>{$id}</td>
            </tr>
            <tr>
                <td>Tiket vytvořil:</td>
                <td>{$username}</td>
            </tr>
            <tr>
                <td>Skupina:</td>
                <td>{$group}</td>
            </tr>
            <tr>
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
            case "send_message_check_admin":
                $id = $this->get_current_ticket_id();
                $rv = $this->database->select(["waiting_for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);
                
                if ($rv->fetch_object()->waiting_for == self::TICKET_CLOSE) {
                    return "<div class=\"alert alert-danger text-chat\">
                    Tiket je uzavřen, nelze do něj odepisovat!
                    </div>";
                } else {
                    return "<form method=\"post\" action=\"./requests.php\"  enctype=\"multipart/form-data\">
                    <input type=\"hidden\" name=\"method\" value=\"ticket-send-message-admin\" required>
                    <input type=\"hidden\" name=\"source_page\" value=\"?ticket-view-admin|id=" . $id . "\" required>
                    <input type=\"hidden\" name=\"CSRF_token\" value=\"%%CSRF_Token%%\" required>
                    <input type=\"hidden\" name=\"ticket_id\" value=\"" . Utils::createPackage("AD;" . Utils::randomString(5) . ";" . $id . ";" . Utils::randomString(5))[1] . "\" required>
                    <div class=\"form-group\">
                        <label for=\"file\">Přiložit obrázek <span style=\"color:red;font-size:small;\">maximálně %%ticket_max_file_size%%</span></label>
                        <div class=\"input-group mb-3\">
                            <div class=\"custom-file\">
                                <input type=\"file\" class=\"custom-file-input\" id=\"file\" name=\"file\">
                                <label class=\"custom-file-label\" for=\"file\">Vybrat soubor</label>
                            </div>
                        </div>
                    </div>
                    <div class=\"form-group\">
                        <label for=\"message\">Zpráva <span style=\"color:red;font-size:small;\">minimálně 10 znaků</span></label>
                        <textarea type=\"text\" class=\"form-control\" id=\"message\" name=\"message\" maxlength=\"1000\" required></textarea>
                    </div>
                    <button type=\"submit\" class=\"btn btn-light\">Odeslat zprávu</button>
                </form>";
                }
            break;

            case "send-message-admin":
                $hashed_id = $this->vars["ticket_id"];
                $message = str_replace(["&amp;", "\\r", "\\n"], ["&", "", ""], trim($this->vars["message"]));
                $username = $this->username;

                if (@explode(";",
                    @Utils::getPackage(
                       [1 => $hashed_id]
                    ))[0] != "AD") {
                    define("ERROR", ["Nelze poslat normali zpravu do admin chatu!"]);
                    return false;
                } 

                $id = @explode(";",
                    @Utils::getPackage(
                        [1 => $hashed_id]
                    )
                )[2];

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

                $rv = $rv->fetch_object();

                $author = Utils::getUserByClientId($rv->author);

                
                if ($rv->waiting_for == self::TICKET_CLOSE) {
                    $this->logger->log("Hráč se snaži upravovat zavřené tikety!", "critical", true);
                    define("ERROR", ["Tento tiket je uzavřen!"]);
                    return false;
                }

                if (mb_strlen($message) > 20000) {
                    define("ERROR", ["Zpráva nesmí být delší než 20000 znaků"]);
                    return false;
                }
        
                if (mb_strlen($message) < 10) {
                    define("ERROR", ["Zpráva je příliš krátká"]);
                    return false;
                }

                if (!empty($this->vars["img_url"])) {
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
                        json_encode(["admin" => true, "image" => true]),
                        $this->vars["img_url"],
                        time(),
                        date("H:i:s d.m.Y")
                    ]
                    );
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
                    json_encode(["admin" => true]),
                    $message,
                    time(),
                    date("H:i:s d.m.Y")
                ]
                );

                $this->database->update("adminka_tickets`.`tickets_list", "id", $id, ["waiting_for"], [self::TICKET_WAITING_FOR_USER]);

                return true;
            break;

            /**
             * Requests
             */

            case "is_blocked":
                $username = $this->vars["username"];
                $id = Utils::getClientID($username);
                $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_banned_users", "LIMIT 1", "user_id", $id);
                if (!$rv || $this->database->num_rows($rv) == 0) {
                    return false;
                }
                $_SESSION["Request"]["Errors"] = ["Jsi na tiketech zablokován"];
                Utils::header("./?main");
                #$_SESSION["Request"]["Errors"]
            break;

            case "get-current-group":
                $router = Main::getApp("\patrick115\Main\Router");
                $type = $router->getURIData("type", false);

                $groups = $this->config->getConfig("Main/ticket-categories");
                return $groups[$type]["name"];
            break;

            case "toggle-ticket":
                $id = @explode(";", @Utils::getPackage([1 => $this->vars["ticket_id"]]))[1];
                $value = @explode(";", @Utils::getPackage([1 => $this->vars["value"]]))[1];

                if (empty($id)) {
                    define("ERROR", ["Neplatné id"]);
                    return false;
                }
                if (!is_numeric($id)) {
                    define("ERROR", ["Id musí být číslo"]);
                    return false;
                }

                if (empty($value)) {
                    define("ERROR", ["Neplatná hodnota"]);
                    return false;
                }
                if (!in_array($value, ["open", "close"])) {
                    define("ERROR", ["Neznámá hodnota"]);
                    return false;
                }

                $rv = $this->database->select(["for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);

                $perms = $perms = Main::Create("\patrick115\Adminka\Permissions", [""]);

                $groups = $this->config->getConfig("Main/ticket-group-access");
                    
                if (!$perms->getUser($this->username)->havePermission()->inGroup($groups[$rv->fetch_object()->for])) {
                    define("ERROR", ["Nemáš právo upravovat tento tiket"]);
                    return false;
                } 

                switch($value) {
                    case "open":
                        $this->database->update("adminka_tickets`.`tickets_list", "id", $id, ["waiting_for"], [self::TICKET_WAITING_FOR_USER]);
                        $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_messages", "ORDER BY `tickets_messages`.`id` DESC LIMIT 1", "ticket_id", $id);
                        $this->database->insert("adminka_tickets`.`tickets_alerts", 
                        [
                            "id", 
                            "ticket_id", 
                            "type", 
                            "message", 
                            "after_message", 
                            "timestamp", 
                            "date"
                        ], 
                        [
                            "",
                            $id,
                            "open",
                            "Tiket byl znova otevřen",
                            $rv->fetch_object()->id,
                            time(),
                            date("H:i:s d.m.Y")
                        ]
                        );

                        Logger::init()->log("{$this->username} opened ticket with id {$id}.");

                        return true;
                    break;
                    case "close":
                        $this->database->update("adminka_tickets`.`tickets_list", "id", $id, ["waiting_for"], [self::TICKET_CLOSE]);
                        $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_messages", "ORDER BY `tickets_messages`.`id` DESC LIMIT 1", "ticket_id", $id);
                        $this->database->insert("adminka_tickets`.`tickets_alerts", 
                        [
                            "id", 
                            "ticket_id", 
                            "type", 
                            "message", 
                            "after_message", 
                            "timestamp", 
                            "date"
                        ], 
                        [
                            "",
                            $id,
                            "close",
                            "Tiket byl uzavřen",
                            $rv->fetch_object()->id,
                            time(),
                            date("H:i:s d.m.Y")
                        ]
                        );

                        Logger::init()->log("{$this->username} closed ticket with id {$id}.");

                        return true;
                    break;
                }
                
            break;
            case "change_group":
                $id = $this->get_current_ticket_id();

                $rv = $this->database->select(["for", "waiting_for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);

                $rv = $rv->fetch_object();

                $for = $rv->for;

                $status = $rv->waiting_for;

                if ($status == self::TICKET_CLOSE) {
                    $button = "<button type=\"submit\" class=\"btn btn-light-green\">Otevřít</button>";
                    $cls = Utils::createPackage(Utils::randomString(10) . ";open;" . Utils::randomString(10))[1];
                } else {
                    $button = "<button type=\"submit\" class=\"btn btn-light-red\">Uzavřít</button>";
                    $cls = Utils::createPackage(Utils::randomString(10) . ";close;" . Utils::randomString(10))[1];
                }

                $rv = $this->database->select(["author"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);
                if (!$rv || $this->database->num_rows($rv) == 0) {
                    define("ERROR", ["Někde nastala chyba"]);
                    return false;
                }

                $author = $rv->fetch_object()->author;

                $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_banned_users", "LIMIT 1", "user_id", $author);
                if (!$rv || $this->database->num_rows($rv) == 0) {
                    $b_color = "red";
                    $b_text = "Zablokovat na tiketech";
                    $b_type = "block";
                } else {
                    $b_color = "green";
                    $b_text = "Odblokovat na tiketech";
                    $b_type = "unblock";
                }


                $return = "<p class=\"title\">Nastavení tiketu</p>
                <hr>
                ";
                $perms = $perms = Main::Create("\patrick115\Adminka\Permissions", [""]);
                $group = $this->config->getConfig("Main/ticket-block-group");

                if ($perms->getUser($this->username)->havePermission()->inGroup($group)) {

                    $return .= "
                    <form method=\"post\" action=\"./requests.php\">
                        <input type=\"hidden\" name=\"method\" value=\"block-user\" required>
                        <input type=\"hidden\" name=\"source_page\" value=\"?ticket-view-admin|id={$id}\" required>
                        <input type=\"hidden\" name=\"CSRF_token\" value=\"%%CSRF_Token%%\" required>
                        <input type=\"hidden\" name=\"value\" value=\"" . Utils::createPackage(Utils::randomString(48) . ";{$b_type};" . Utils::randomString(24))[1] . "\" required>
                        <input type=\"hidden\" name=\"ticket_id\" value=\"" . Utils::createPackage(Utils::randomString(10) . ";{$id};" . Utils::randomString(10))[1] . "\" required>
                        <input type=\"hidden\" name=\"user\" value=\"" . Utils::createPackage(Utils::randomString(10) . ";{$author};" . Utils::randomString(10))[1] . "\" required>
                        <button type=\"submit\" class=\"btn btn-light-{$b_color}\">{$b_text}</button>
                    </form>
                    <br />
                    ";
                }
                $return .= "
                <form method=\"post\" action=\"./requests.php\">
                
                    <input type=\"hidden\" name=\"method\" value=\"toggle-ticket\" required>
                    <input type=\"hidden\" name=\"source_page\" value=\"?ticket-view-admin|id={$id}\" required>
                    <input type=\"hidden\" name=\"CSRF_token\" value=\"%%CSRF_Token%%\" required>
                    <input type=\"hidden\" name=\"value\" value=\"{$cls}\" required>
                    <input type=\"hidden\" name=\"ticket_id\" value=\"" . Utils::createPackage(Utils::randomString(10) . ";{$id};" . Utils::randomString(10))[1] . "\" required>
                    {$button}
                </form>
                <br>
                <p class=\"title\">Přesun tiketu do jiné kategorie</p>
                <hr>
                <form method=\"post\" action=\"./requests.php\">
                    <input type=\"hidden\" name=\"method\" value=\"ticket-change-group\" required>
                    <input type=\"hidden\" name=\"source_page\" value=\"?ticket-view-admin|id=" . $id . "\" required>
                    <input type=\"hidden\" name=\"CSRF_token\" value=\"%%CSRF_Token%%\" required>
                    <input type=\"hidden\" name=\"ticket_id\" value=\"" . Utils::createPackage(Utils::randomString(10) . ";{$id};" . Utils::randomString(8))[1] . "\" required>
                    <div class=\"form-group\">
                        <label for=\"group\">Vyber skupinu</label>
                        <select name=\"group\" id=\"group\" class=\"form-control\" required=\"\">
                            <option value=\"\"></option>
                        ";

                $groups = $this->config->getConfig("Main/ticket-categories");

                unset($groups[$for]);

                foreach ($groups as $group_name => $group_data) {
                    $return .= "<option value=\"" . Utils::createPackage(Utils::randomString(11) . ";{$group_name};" . Utils::randomString(12))[1] . "\">" . $group_data["name"] . "</option>";
                }

                $return .= "</select>
                </div>
                <button type=\"submit\" class=\"btn btn-light\">Změnit</button>
                </form>";

                return $return;
            break;
            /**
             *  Settings ALLOW User VPN
             */

            case "allow_user_vpn":
                $session = Session::init();
                if (!$session->isExist("Tickets/redirect_vpn_user_allow")) {
                    return "";
                }

                $id = $session->getData("Tickets/redirect_vpn_user_allow");

                unset($_SESSION["Tickets"]["redirect_vpn_user_allow"]);

                Utils::header("./?ticket-view&id={$id}");
                
            break;

            case "delete-ticket":
                $username = $this->vars["username"];
                $ticket_id = explode(";", Utils::getPackage($this->vars["id"]))[1];
                
                $rv = $this->database->select(["for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $ticket_id);

                if (!$rv || $this->database->num_rows($rv) == 0) {
                    define("ERROR", ["Tiket s tímto ID neexistuje"]);
                    return false;
                }

                $perms = $perms = Main::Create("\patrick115\Adminka\Permissions", [""]);
                $groups = $this->config->getConfig("Main/ticket-group-access");

                if (!$perms->getUser($this->username)->havePermission()->inGroup($groups[$rv->fetch_object()->for])) {
                    define("ERROR", ["Nemáš právo smazat tento tiket"]);
                    return false;
                } 

                $this->database->delete("adminka_tickets`.`tickets_list", ["id"], [$ticket_id]);

                $this->logger->log("Uživatel {$username} smazal tiket s id {$ticket_id}", "tickets");

                return true;
            break;

            case "block-user":
                $banner = $this->vars["username"];
                $banner_id = Utils::getClientID($banner);

                $user_to_block_id = @explode(";", @Utils::getPackage($this->vars["user"]))[1];

                if (empty($user_to_block_id) || !is_numeric($user_to_block_id)) {
                    define("ERROR", ["Neplatný uživatel"]);
                    return false;
                }

                if (!empty($this->vars["skip-block"])) {
                    $skip_block = @explode(";", @Utils::getPackage($this->vars["skip-block"]))[1];

                    if (empty($skip_block)) {
                        define("ERROR", ["Neplatná hodnota!"]);
                        return false;
                    }
                    $skip_block = (bool) $skip_block;

                    if (empty($skip_block)) {
                        define("ERROR", ["skip-block must be boolean"]);
                        return false;
                    }
                } else {
                    $skip_block = false;
                }

                if ($banner_id == $user_to_block_id && $skip_block !== true) {
                    define("ERROR", ["Nemůžeš zablokovat/odblokovat sám sebe"]);
                    return false;
                }

                $current_ticket_id = @explode(";", @Utils::getPackage($this->vars["ticket_id"]))[1];
                if (empty($current_ticket_id) || !is_numeric($current_ticket_id)) {
                    define("ERROR", ["Neplatné id tiketu"]);
                    return false;
                }

                $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $current_ticket_id);

                if (!$rv || $this->database->num_rows($rv) == 0) {
                    define("ERROR", ["Tiket s tímto id neexistuje!"]);
                    return false;
                }

                $action = @explode(";", @Utils::getPackage($this->vars["value"]))[1];

                if (empty($action)) {
                    define("ERROR", ["Neplatná akce"]);
                    return false;
                }

                if (!in_array($action, ["block", "unblock"])) {
                    define("ERROR", ["Neplatná akce"]);
                    return false;
                }

                switch($action) {
                    case "block":
                        $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_banned_users", "LIMIT 1", "user_id", $user_to_block_id);

                        if (!$rv) {
                            define("ERROR", ["Někde nastala chyba!"]);
                            return false;
                        }
                        if ($this->database->num_rows($rv) > 0) {
                            define("ERROR", ["Tento hráč je již zablokován!"]);
                            return false;
                        }

                        $this->database->insert("adminka_tickets`.`tickets_banned_users", 
                        [
                            "id",
                            "user_id",
                            "banner",
                            "ticket_id",
                            "timestamp",
                            "date"
                        ],
                        [
                            "",
                            $user_to_block_id,
                            $banner_id,
                            $current_ticket_id,
                            time(),
                            date("H:i:s d.m.Y")
                        ]);

                        $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_banned_users", "LIMIT 1", "user_id", $user_to_block_id);

                        if (!$rv || $this->database->num_rows($rv) == 0) {
                            define("ERROR", ["Někde nastala chyba!"]);
                            return false;
                        }

                        $this->logger->log("$banner zablokoval " . Utils::getUserByClientId($user_to_block_id) . " na tiketech (v tiketu {$current_ticket_id})", "tickets");

                        define("MESSAGE", ["Hráč byl zablokován"]);
                    break;
                    case "unblock":
                        $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_banned_users", "LIMIT 1", "user_id", $user_to_block_id);
                        if (!$rv || $this->database->num_rows($rv) == 0) {
                            define("ERROR", ["Tento hráč není zablokován!"]);
                            return false;
                        }
                        $this->database->delete("adminka_tickets`.`tickets_banned_users", ["user_id"], [$user_to_block_id], "LIMIT 1");

                        $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_banned_users", "LIMIT 1", "user_id", $user_to_block_id);

                        if (!$rv || $this->database->num_rows($rv) > 0) {
                            define("ERROR", ["Někde nastala chyba"]);
                            return false;
                        }

                        $this->logger->log("$banner odblokoval " . Utils::getUserByClientId($user_to_block_id) . " na tiketech (v tiketu {$current_ticket_id})", "tickets");

                        define("MESSAGE", ["Hráč byl odblokován"]);
                    break;
                }

                return true;
            break;

            default:
                return "No process found";
            break;
        }
        return "";
    }

    /**
     * Load data from config
     * @param string $type - Type what get from config
     * @return null
     */
    private function loadConfig(string $type)
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

    /**
     * Create ticket for allow VPN
     * @return bool
     */
    public function allowUserVPN()
    {
        $reason = $this->vars["reason"];
        $confirm = $this->vars["confirm"];

        if ($confirm != "allow") {
            define("ERROR", ["Nepotvrdil jsi že povolení nebudeš zneužívat"]);
            return false;
        }

        $vpn = $this->config->getConfig("Main/vpn_allow");



        $this->vars["name"] = $vpn["ticket_name"];
        $this->vars["message"] = str_replace(["%username%", "%reason%"], [$this->username, $reason], $vpn["message"]);

        $this->vars["type"] = Utils::createPackage("%%TICKET_ID;" . $vpn["category"] . ";TICKET_ID%%")[1];

        $this->writeTicket();

        $session = Session::init();
        
        $ticket_id = $session->getData("Tickets/redirect_ticket_id");
        unset($_SESSION["Tickets"]["redirect_ticket_id"]);
        $_SESSION["Tickets"]["redirect_vpn_user_allow"] = $ticket_id;

        return true;
    }

    /**
     * Change ticket group
     * @return bool
     */
    public function changeTicketGroup()
    {
        $id = @explode(";", @Utils::getPackage([1 => $this->vars["ticket_id"]]))[1];
        $group = @explode(";", @Utils::getPackage([1 => $this->vars["group"]]))[1];
        if (empty($id)) {
            define("ERROR", ["Neplatné id"]);
            return false;
        }
        if (!is_numeric($id)) {
            define("ERROR", ["Id musí být číslo"]);
            return false;
        }

        $rv = $this->database->select(["id", "for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);

        if (!$rv || $this->database->num_rows($rv) == 0) {
            define("ERROR", ["Tiket s tímto id neexistuje"]);
            return false;
        } 

        $perms = $perms = Main::Create("\patrick115\Adminka\Permissions", [""]);

        $groups = $this->config->getConfig("Main/ticket-group-access");

        if (!$perms->getUser($this->username)->havePermission()->inGroup($groups[$rv->fetch_object()->for])) {
            define("ERROR", ["Nemáš právo upravovat tento tiket"]);
            return false;
        } 

        if (empty($group)) {
            define("ERROR", ["Neplatná skupina"]);
            return false;
        }

        $this->database->update("adminka_tickets`.`tickets_list", "id", $id, ["for"], [$group]);

        $cat = $this->config->getConfig("Main/ticket-categories");

        $rv = $this->database->select(["id"], "adminka_tickets`.`tickets_messages", "ORDER BY `id` DESC LIMIT 1", "ticket_id", $id);
        $this->database->insert("adminka_tickets`.`tickets_alerts", 
        [
            "id",
            "ticket_id",
            "type",
            "message",
            "after_message",
            "timestamp",
            "date"
        ], 
        [
            "",
            $id,
            "change",
            "Tiket byl přesunut do kategorie {$cat[$group]["name"]}",
            $rv->fetch_object()->id,
            time(),
            date("H:i:s d.m.Y")
        ]
        );

        $rv = $this->database->select(["waiting_for"], "adminka_tickets`.`tickets_list", "LIMIT 1", "id", $id);

        if ($rv->fetch_object()->waiting_for != self::TICKET_CLOSE) {
            $this->database->update("adminka_tickets`.`tickets_list", ["id"], [$id], ["waiting_for"], [self::TICKET_WAITING_FOR_ADMIN]);
        }

        return true;

    }
}
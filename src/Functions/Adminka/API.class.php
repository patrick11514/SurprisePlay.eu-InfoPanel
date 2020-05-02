<?php

/**
 * API class, got requests from AJAX, and
 * return things from database.
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Adminka;

use patrick115\Adminka\Players\Rank;
use patrick115\Main\Database;
use patrick115\Main\Config;
use patrick115\Main\Tools\Utils;

class API
{
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
     * Allowed AJAX requests
     * @var array
     */
    private $posts = [
        "get-user-list",
        "get-allowVPN-list",
        "get-Unregistred-list",
        "get-gemsLog",
        "get-todoList",
        "get-TodoUsers",
        "get-transfer-list",
        "get-unban-list",
        "get-blockedList"
    ];
    /**
     * Allowed AJAX requests
     * @var array
     */
    private $values = [
        "get-user-list" => "findningnick",
        "get-allowVPN-list" => "page",
        "get-Unregistred-list" => "page",
        "get-gemsLog" => "page",
        "get-todoList" => "page",
        "get-transfer-list" => "page", 
        "get-unban-list" => "page",
        "get-blockedList" => "page"
    ];

    /**
     * Data from POST
     * @var array
     */
    private $post;

    /**
     * Construct class
     * @param array $post - Data from POST
     */
    public function __construct(array $post)
    {
        $admin_account = $_SESSION["Account"]["Admin_Account"];

        if (!$admin_account) {
            die($this->throwError("No permissions to use API!"));
        }

        if (empty($post["method"])) {
            die($this->throwError("No method posted!"));
        }
        if (!in_array($post["method"], $this->posts)) {
            die($this->throwError("Undefined method {$post["method"]}"));
        }
        $this->database = Database::init();
        $this->config = Config::init();

        $newpost = [];

        foreach ($post as $name => $value) {
            $newpost[Utils::chars($name)] = Utils::chars($value);
        }

        $this->post = $newpost;
    }

    /**
     * Chceck if token is valid, if key is valid,
     * than get data and return it
     * 
     * @return string
     */
    public function check()
    {
        $csrf = Main::Create("\patrick115\Requests\CSRF", []);
        if (empty($this->post["CSRF_TOKEN"])) {
            return $this->throwError("No token found.");
        }
        if (!$csrf->checkToken($this->post["CSRF_TOKEN"])) {
            return $this->throwError("Invalid key, refesh page.");
        }
        $method = $this->post["method"];
        if (!empty($this->values[$method])) {
            if (Utils::newEmpty($this->post[$this->values[$method]])) {
                return $this->throwError("Value is empty!");
            }
        }
        return $this->getData($method, isset($this->values[$method]) ? $this->post[$this->values[$method]] : null);
    }

    /**
     * Create from error, json with error
     * 
     * @param string $message - message of error
     * @return string
     */
    private function throwError(string $message) 
    {
        return json_encode(
            [
                "success" => false,
                "message" => $message
            ]
        );
    }

    /**
     * Get data based by method
     * 
     * @param string $method - method to get data
     * @param mixed $value - value from post
     * @return string
     */
    private function getData(string $method, $value)
    {
        switch ($method) {
            case "get-user-list":
                $rv = $this->database->select(["realname"], "main_authme`.`authme", "WHERE `username` LIKE '{$value}%' LIMIT 5;");
                if (!$rv) {
                    return $this->throwError("Někde nastala chyba!");
                }
                $return = "";
                $i = 0;
                if ($this->database->num_rows($rv) > 0) {
                    $i++;
                    while ($row = $rv->fetch_assoc()) {  
                        $return .= "<a href=\"#\" id=\"snick\" data-nick=\"{$row["realname"]}\" class=\"list-group-item list-group-item-action\">{$row["realname"]}</a>";
                    }
                    $array = [
                        "success" => true,
                        "message" => $return
                    ];
                    $return = json_encode($array);
                } else {
                    $return = $this->throwError("Žádná data nenalezena");
                }

            break;
            case "get-allowVPN-list":
                if ($value < 0 || $value == 0) {
                    return $this->throwError("Neplatná Stránka!");
                }

                $start = ($value == 1) ? 0 : (5 * ($value - 1));
                $end = ($value == 1) ? 6 : ((5 * $value) + 1);
                $token = \patrick115\Main\Session::init()->getData("Security/CRF/token");
                $return = "";
                $rv = $this->database->execute("SELECT `uuid` FROM `main_perms`.`perms_user_permissions` WHERE `permission` = 'antiproxy.proxy' ORDER BY `perms_user_permissions`.`id` DESC LIMIT {$start}, {$end} ", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 5;
                $a = 0;

                if ($this->database->num_rows($rv) == 0) {
                    return $this->throwError("Žádná data nenalezena");
                }

                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (5 >= $a) {
                        $username = Utils::getNickByUUID($row["uuid"]);

                        $rank = new Rank($username);

                        $rank = $rank->getRank();
                        $rank_color = $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank)];

                        $vpn_id = Utils::createPackage(Utils::randomString(10) . ";" . $username . ";" . Utils::randomString(11))[1];

                        $return .= "
                        <tr>
                            <td>{$i}</td>
                            <td>{$username}</td>
                            <td><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$rank}</span></td>
                            <td>
                            <form method=\"post\" action=\"./requests.php\">
                                <input type=\"hidden\" name=\"method\" value=\"remove-vpn\" required>
                                <input type=\"hidden\" name=\"source_page\" value=\"?vpn-allow\" required>
                                <input type=\"hidden\" name=\"CSRF_token\" value=\"" .$token .  "\" required>
                                <input type=\"hidden\" name=\"id\" value=\"{$vpn_id}\" required>
                                <button type=\"submit\" style=\"background:none;border:none;\"><i class=\"fas fa-trash\"></i></button></td>
                            </form>
                            </td>
                        </tr>";
                    }
                }

                if ($value == 1 && $a > 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "enabled";
                } else if ($value == 1 && $a <= 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "disabled";
                } else if ($value > 1 && $a > 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "enabled";
                } else if ($value > 1 && $a <= 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                } else {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                }

                $array = [
                    "success" => true,
                    "message" => $return,
                    "currentpage" => $value,
                    "prev" => $status_prev_button,
                    "next" => $status_next_button,
                ];
                $return = json_encode($array);

            break;
            case "get-Unregistred-list":
                if ($value < 0 || $value == 0) {
                    return $this->throwError("Neplatná Stránka!");
                }

                $start = ($value == 1) ? 0 : (5 * ($value - 1));
                $end = ($value == 1) ? 6 : ((5 * $value) + 1);

                $return = "";
                $rv = $this->database->execute("SELECT `admin`, `unregistered`, `date`, `id` FROM `unregister-log` ORDER BY `unregister-log`.`id` DESC LIMIT {$start}, {$end}", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 5;
                $a = 0;

                if ($this->database->num_rows($rv) == 0) {
                    return $this->throwError("Žádná data nenalezena");
                }

                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (5 >= $a) {

                        $username = $row["unregistered"];

                        $rank = new Rank($username);

                        $rank = $rank->getRank();
                        $rank_color = $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank)];

                    

                        $return .= "
                        <tr>
                            <td>{$row["id"]}</td>
                            <td>{$username}</td>
                            <td><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$rank}</span></td>
                            <td>{$row["admin"]}</td>
                            <td>{$row["date"]}</td>
                        </tr>";
                    }
                }

                if (empty($return)) {
                    return $this->throwError("Neplatná stránka");
                }

                if ($value == 1 && $a > 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "enabled";
                } else if ($value == 1 && $a <= 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "disabled";
                } else if ($value > 1 && $a > 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "enabled";
                } else if ($value > 1 && $a <= 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                } else {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                }

                $array = [
                    "success" => true,
                    "message" => $return,
                    "currentpage" => $value,
                    "prev" => $status_prev_button,
                    "next" => $status_next_button,
                ];
                $return = json_encode($array);
            break;
            case "get-gemsLog":
                if ($value < 0 || $value == 0) {
                    return $this->throwError("Neplatná Stránka!");
                }

                $start = ($value == 1) ? 0 : (5 * ($value - 1));
                $end = ($value == 1) ? 6 : ((5 * $value) + 1);

                $return = "";
                $rv = $this->database->execute("SELECT `admin`, `nick`, `date`, `amount`, `method`, `id` FROM `gems-log` ORDER BY `gems-log`.`id` DESC LIMIT {$start}, {$end}", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 5;
                $a = 0;

                if ($this->database->num_rows($rv) == 0) {
                    return $this->throwError("Žádná data nenalezena");
                }

                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (5 >= $a) {

                        $username = $row["nick"];

                        $rank = new Rank($username);

                        $rank = $rank->getRank();
                        $rank_color = $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank)];

                        $method = ($row["method"] == "add") ? "<span class=\"badge badge-primary\" style=\"background-color:green;\">Přidáno</span>" : "<span class=\"badge badge-primary\" style=\"background-color:red;\">Odebráno</span>";

                        $return .= "
                        <tr>
                            <td>{$row["id"]}</td>
                            <td>{$username}</td>
                            <td><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$rank}</span></td>
                            <td>{$row["admin"]}</td>
                            <td>{$row["amount"]}</td>
                            <td>{$method}</td>
                            <td>{$row["date"]}</td>
                        </tr>";
                    }
                }

                if (empty($return)) {
                    return $this->throwError("Neplatná stránka");
                }

                if ($value == 1 && $a > 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "enabled";
                } else if ($value == 1 && $a <= 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "disabled";
                } else if ($value > 1 && $a > 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "enabled";
                } else if ($value > 1 && $a <= 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                } else {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                }

                $array = [
                    "success" => true,
                    "message" => $return,
                    "currentpage" => $value,
                    "prev" => $status_prev_button,
                    "next" => $status_next_button,
                ];
                $return = json_encode($array);
            break;
            case "get-todoList":
                if ($value < 0 || $value == 0) {
                    return $this->throwError("Neplatná Stránka!");
                }

                $start = ($value == 1) ? 0 : (10 * ($value - 1));
                $end = ($value == 1) ? 11 : ((10 * $value) + 1);

                $return = "";
                $rv = $this->database->execute("SELECT `id`, `creator`, `for`, `message`, `tags`, `date` FROM `todo-list` ORDER BY `todo-list`.`id` DESC LIMIT {$start}, {$end}", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 10;
                $a = 0;

                $token = \patrick115\Main\Session::init()->getData("Security/CRF/token");

                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (10 >= $a) {

                        $tags = json_decode($row["tags"], 1)["tags"];
                        $tagy = $this->config->getConfig("Main/todo-tags");

                        $tag_string = "";

                        foreach ($tags as $tag) {
                            if (!empty($tagy[$tag])) {
                                if (empty($tagy[$tag]["name"]) || empty($tagy[$tag]["color"])) {
                                    return $this->throwError("Tag settings is invalid!");
                                }
                                $tag_string .= "<span class=\"badge badge-primary\" style=\"background-color:{$tagy[$tag]["color"]};\">{$tagy[$tag]["name"]}</span>";
                            } else {
                                $tag_string .= "<span class=\"badge badge-primary\" style=\"background-color:#AAAAAA;\">{$tag}</span>";
                            }
                        }

                        $todo_id = Utils::createPackage("%%ID;" . $row["id"] . ";ID%%")[1];

                        $return .= "
                        <tr>
                            <td>{$i}</td>
                            <td>{$row["for"]}</td>
                            <td style=\"word-break: normal;\">{$row["message"]}</td>
                            <td>{$tag_string}</td>
                            <td>{$row["creator"]}</td>
                            <td>{$row["date"]}</td>
                            <td>
                                <form method=\"post\" action=\"./requests.php\">
                                    <input type=\"hidden\" name=\"method\" value=\"remove-todo\" required>
                                    <input type=\"hidden\" name=\"source_page\" value=\"?todo\" required>
                                    <input type=\"hidden\" name=\"CSRF_token\" value=\"" .$token .  "\" required>
                                    <input type=\"hidden\" name=\"id\" value=\"{$todo_id}\" required>
                                    <button type=\"submit\" style=\"background:none;border:none;\"><i class=\"fas fa-trash\"></i></button></td>
                                </form>
                        </tr>";
                    }
                }

                if (empty($return) && $value == 1) {
                    return $this->throwError("Nejsou zadány žádné úkoly");
                }
#INSERT INTO `todo-list` (`id`, `creator_id`, `creator`, `for_id`, `for`, `message`, `tags`, `date`, `timestamp`) VALUES (NULL, '13', 'Ut5dere', '11', 'patrick115', 'Udělat infopanel', '{\"tags\":[\"important\", \"warning\"]}', '15:44:20 03.04.2020', '1583336660')
                if (empty($return)) {
                    return $this->throwError("Neplatná stránka");
                }

                if ($value == 1 && $a > 10) {
                    $status_prev_button = "disabled";
                    $status_next_button = "enabled";
                } else if ($value == 1 && $a <= 10) {
                    $status_prev_button = "disabled";
                    $status_next_button = "disabled";
                } else if ($value > 1 && $a > 10) {
                    $status_prev_button = "enabled";
                    $status_next_button = "enabled";
                } else if ($value > 1 && $a <= 10) {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                } else {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                }

                $array = [
                    "success" => true,
                    "message" => $return,
                    "currentpage" => $value,
                    "prev" => $status_prev_button,
                    "next" => $status_next_button,
                ];
                $return = json_encode($array);
            break;
            case "get-TodoUsers":
                $perms = $this->config->getConfig("Main/group-perms");
                $groups = [];
                foreach ($perms as $group_name => $list)
                {
                    $groups[$group_name] = $list;
                }
                foreach ($groups as $group_name => $group)
                {
                    if (!empty($group["inherits"])) {
                        foreach ($group["inherits"] as $inherit) {
                            if (empty($groups[$inherit])) {
                                $this->error->catchError("Can't find group $inherit!", debug_backtrace());
                                continue;
                            }
                            foreach ($groups[$inherit] as $inherit_group)
                            {
                                $groups[$group_name][] = $inherit_group;
                            }
                        }
                        unset($groups[$group_name]["inherits"]);
                    }
                }
            
                $group = $this->config->getConfig("Main/todo-list");

                $users = [];

                foreach ($groups[$group] as $group) {
                    $rv = $this->database->select(["username"], "main_perms`.`perms_players", "", "primary_group", $group);
                    if ($this->database->num_rows($rv) > 0) {
                        while ($row = $rv->fetch_assoc()) {
                            $rf = $this->database->select(["realname"], "main_authme`.`authme", "LIMIT 1", "username", $row["username"]);
                            $users[] = $rf->fetch_object()->realname;
                        }
                    }
                }

                $return = "<option></option>";

                foreach ($users as $user) {
                    $return .= "<option value=\"$user\">$user</option>";
                }

                $return = [
                    "success" => true,
                    "message" => $return
                ];
                $return = json_encode($return);
            break;
            case "get-transfer-list":
                if ($value < 0 || $value == 0) {
                    return $this->throwError("Neplatná Stránka!");
                }

                $start = ($value == 1) ? 0 : (5 * ($value - 1));
                $end = ($value == 1) ? 6 : ((5 * $value) + 1);

                $return = "";
                $rv = $this->database->execute("SELECT `userid`, `message`, `date` FROM `logger` WHERE `type` = 'transfer_data' ORDER BY `logger`.`id` DESC LIMIT {$start}, {$end}", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 5;
                $a = 0;

                if ($this->database->num_rows($rv) == 0) {
                    return $this->throwError("Žádná data nenalezena");
                }

                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (5 >= $a) {

                        $username = Utils::getUserByClientId($row["userid"]);

                        $rank = new Rank($username);

                        $rank = $rank->getRank();
                        $rank_color = $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank)];

                        $raw = str_replace(["Přesunul data z hráče ", "Přesunul Group z hráče ", "Přesunul Tagy z hráče ", "Přesunul VIP a Tagy z hráče ", "Přesunul Group a Tagy z hráče "], ["", "", "", "", ""], $row["message"]);
                        $ex = explode(" na hráče ", $raw);

                        $from = $ex[0];
                        $to = $ex[1];

                        $return .= "
                        <tr>
                            <td>{$i}</td>
                            <td>{$username}</td>
                            <td><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$rank}</span></td>
                            <td>{$from}</td>
                            <td>{$to}</td>
                            <td>{$row["date"]}</td>
                        </tr>";
                    }
                }

                if (empty($return)) {
                    return $this->throwError("Neplatná stránka");
                }

                if ($value == 1 && $a > 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "enabled";
                } else if ($value == 1 && $a <= 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "disabled";
                } else if ($value > 1 && $a > 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "enabled";
                } else if ($value > 1 && $a <= 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                } else {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                }

                $array = [
                    "success" => true,
                    "message" => $return,
                    "currentpage" => $value,
                    "prev" => $status_prev_button,
                    "next" => $status_next_button,
                ];

                $return = json_encode($array);
            break;
            case "get-unban-list":
                if ($value < 0 || $value == 0) {
                    return $this->throwError("Neplatná Stránka!");
                }

                $start = ($value == 1) ? 0 : (5 * ($value - 1));
                $end = ($value == 1) ? 6 : ((5 * $value) + 1);

                $return = "";
                $rv = $this->database->execute("SELECT `unbanner`, `player`, `reason`, `date` FROM `unbans` ORDER BY `unbans`.`id` DESC LIMIT {$start}, {$end}", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 5;
                $a = 0;

                if ($this->database->num_rows($rv) == 0) {
                    return $this->throwError("Žádná data nenalezena");
                }

                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (5 >= $a) {

                        $username = Utils::getUserByClientId($row["unbanner"]);

                        $rank = new Rank($username);

                        $rank = $rank->getRank();
                        $rank_color = $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank)];

                        $player = Utils::getUserByClientId($row["player"]);

                        $return .= "
                        <tr>
                            <td>{$i}</td>
                            <td>{$username}</td>
                            <td><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$rank}</span></td>
                            <td>{$player}</td>
                            <td>{$row["reason"]}</td>
                            <td>{$row["date"]}</td>
                        </tr>";
                    }
                }

                if (empty($return)) {
                    return $this->throwError("Neplatná stránka");
                }

                if ($value == 1 && $a > 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "enabled";
                } else if ($value == 1 && $a <= 5) {
                    $status_prev_button = "disabled";
                    $status_next_button = "disabled";
                } else if ($value > 1 && $a > 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "enabled";
                } else if ($value > 1 && $a <= 5) {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                } else {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                }

                $array = [
                    "success" => true,
                    "message" => $return,
                    "currentpage" => $value,
                    "prev" => $status_prev_button,
                    "next" => $status_next_button,
                ];

                $return = json_encode($array);
            break;
            case "get-blockedList":
                if ($value < 0 || $value == 0) {
                    return $this->throwError("Neplatná Stránka!");
                }

                $start = ($value == 1) ? 0 : (20 * ($value - 1));
                $end = ($value == 1) ? 21 : ((20 * $value) + 1);

                $return = "";
                $rv = $this->database->execute("SELECT `id`, `user_id`, `banner`, `ticket_id`, `date` FROM `adminka_tickets`.`tickets_banned_users` ORDER BY `tickets_banned_users`.`id` DESC LIMIT {$start}, {$end}", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 20;
                $a = 0;

                if ($this->database->num_rows($rv) == 0) {
                    return $this->throwError("Nikdo není zablokován");
                }

                $token = \patrick115\Main\Session::init()->getData("Security/CRF/token");

                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (5 >= $a) {

                        $user_id = $row["user_id"];
                        $user = Utils::getUserByClientId($user_id);

                        $block_id = $row["banner"];
                        $block = Utils::getUserByClientId($block_id);

                        $rank = new Rank($user);

                        $rank = $rank->getRank();
                        $rank_color = $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank)];

                    

                        $return .= "
                        <tr>
                            <td>{$row["id"]}</td>
                            <td>{$user}</td>
                            <td><span class=\"badge badge-primary\" style=\"background-color:{$rank_color};\">{$rank}</span></td>
                            <td>{$block}</td>
                            <td><a href=\"./?ticket-view-admin&id={$row["ticket_id"]}\">{$row["ticket_id"]}</a></td>
                            <td>{$row["date"]}</td>

                            <td><form method=\"post\" action=\"./requests.php\">
                            <input type=\"hidden\" name=\"method\" value=\"block-user\" required>
                            <input type=\"hidden\" name=\"source_page\" value=\"?blocked-list\" required>
                            <input type=\"hidden\" name=\"CSRF_token\" value=\"" .$token .  "\" required>
                            <input type=\"hidden\" name=\"value\" value=\"" .Utils::createPackage(Utils::randomString(10) . ";unblock;" . Utils::randomString(12))[1] . "\" required>
                            <input type=\"hidden\" name=\"ticket_id\" value=\"" .Utils::createPackage(Utils::randomString(10) . ";{$row["ticket_id"]};" . Utils::randomString(12))[1] . "\" required>
                            <input type=\"hidden\" name=\"user\" value=\"" .Utils::createPackage(Utils::randomString(10) . ";{$row["user_id"]};" . Utils::randomString(12))[1] . "\" required>
                            <input type=\"hidden\" name=\"skip-block\" value=\"" .Utils::createPackage(Utils::randomString(10) . ";true;" . Utils::randomString(12))[1] . "\" required>
                            <button type=\"submit\" style=\"background:none;border:none;\"><i class=\"fas fa-trash\"></i></button></td>
                        </form></td>
                        </tr>";
                    }
                }

                if (empty($return)) {
                    return $this->throwError("Neplatná stránka");
                }

                if ($value == 1 && $a > 20) {
                    $status_prev_button = "disabled";
                    $status_next_button = "enabled";
                } else if ($value == 1 && $a <= 20) {
                    $status_prev_button = "disabled";
                    $status_next_button = "disabled";
                } else if ($value > 1 && $a > 20) {
                    $status_prev_button = "enabled";
                    $status_next_button = "enabled";
                } else if ($value > 1 && $a <= 20) {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                } else {
                    $status_prev_button = "enabled";
                    $status_next_button = "disabled";
                }

                $array = [
                    "success" => true,
                    "message" => $return,
                    "currentpage" => $value,
                    "prev" => $status_prev_button,
                    "next" => $status_next_button,
                ];
                $return = json_encode($array);
            break;
        }
        return $return;
    }
}

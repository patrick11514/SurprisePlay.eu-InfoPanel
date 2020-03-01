<?php

namespace patrick115\Adminka;

use patrick115\Adminka\Players\Rank;
use patrick115\Main\Database;
use patrick115\Main\Config;
use patrick115\Main\Tools\Utils;

class API
{
    private $database;
    private $config;

    private $posts = [
        "get-user-list",
        "get-allowVPN-list",
        "get-Unregistred-list"
    ];
    private $values = [
        "get-user-list" => "findningnick",
        "get-allowVPN-list" => "page",
        "get-Unregistred-list" => "page"
    ];

    private $post;

    public function __construct($post)
    {
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
            $newpost[Utils::chars($name)] = Utils::chars($this->database->removeChars($value));
        }

        $this->post = $newpost;
    }

    public function check()
    {
        $csrf = Main::Create("\patrick115\Requests\CSRF", []);
        if (!$csrf->checkToken($this->post["CSRF_TOKEN"])) {
            die("Invalid token!");
        }
        $method = $this->post["method"];
        if (Utils::newEmpty($this->post[$this->values[$method]])) {
            return $this->throwError("Value is empty!");
        }
        return $this->getData($method, $this->post[$this->values[$method]]);
    }

    private function throwError($message) 
    {
        return json_encode(
            [
                "success" => false,
                "message" => $message
            ]
        );
    }

    private function getData($method, $value)
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

                $return = "";
                $rv = $this->database->execute("SELECT `uuid` FROM `main_perms`.`perms_user_permissions` WHERE `permission` = 'antiproxy.proxy' LIMIT {$start}, {$end}", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 5;
                $a = 0;
                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (5 >= $a) {
                        $username = Utils::getNickByUUID($row["uuid"]);

                        $rank = new Rank($username);

                        $rank = $rank->getRank();
                        $rank_color = $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank)];

                    

                        $return .= "
                        <tr>
                            <td style=\"text-align:center\">{$i}</td>
                            <td style=\"text-align:center\">{$username}</td>
                            <td style=\"text-align:center\"><span class=\"badge\" style=\"color:{$rank_color};font-size:1rem\">{$rank}</span></td>
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
                $rv = $this->database->execute("SELECT `admin`, `unregistered`, `date` FROM `unregister-log` LIMIT {$start}, {$end}", true);
                $i = ($value == 1) ? 0 : ($value - 1) * 5;
                $a = 0;
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
                            <td style=\"text-align:center\">{$i}</td>
                            <td style=\"text-align:center\">{$username}</td>
                            <td style=\"text-align:center\"><span class=\"badge\" style=\"color:{$rank_color};font-size:1rem\">{$rank}</span></td>
                            <td style=\"text-align:center\">{$row["admin"]}</td>
                            <td style=\"text-align:center\">{$row["date"]}</td>
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
        }
        return $return;
    }
}

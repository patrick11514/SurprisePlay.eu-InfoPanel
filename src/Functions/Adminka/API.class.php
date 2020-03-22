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
        "get-Unregistred-list",
        "get-gemsLog",
        "get-todoList",
        "get-TodoUsers"
    ];
    private $values = [
        "get-user-list" => "findningnick",
        "get-allowVPN-list" => "page",
        "get-Unregistred-list" => "page",
        "get-gemsLog" => "page",
        "get-todoList" => "page"
    ];

    private $post;

    public function __construct($post)
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

    public function check()
    {
        $csrf = Main::Create("\patrick115\Requests\CSRF", []);
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
                $rv = $this->database->execute("SELECT `uuid` FROM `main_perms`.`perms_user_permissions` WHERE `permission` = 'antiproxy.proxy' ORDER BY `perms_user_permissions`.`id` DESC LIMIT {$start}, {$end} ", true);
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
                            <td style=\"text-align:center;word-break: normal;\">{$i}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$username}</td>
                            <td style=\"text-align:center;word-break: normal;\"><span class=\"badge\" style=\"color:{$rank_color};font-size:1rem;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$rank}</span></td>
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
                            <td style=\"text-align:center;word-break: normal;\">{$row["id"]}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$username}</td>
                            <td style=\"text-align:center;word-break: normal;\"><span class=\"badge\" style=\"color:{$rank_color};font-size:1rem;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$rank}</span></td>
                            <td style=\"text-align:center;word-break: normal;\">{$row["admin"]}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$row["date"]}</td>
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
                while ($row = $rv->fetch_assoc()) {
                    $i++;
                    $a++;
                    if (5 >= $a) {

                        $username = $row["nick"];

                        $rank = new Rank($username);

                        $rank = $rank->getRank();
                        $rank_color = $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank)];

                        $method = ($row["method"] == "add") ? "<span class=\"badge\" style=\"color:green;font-size:1rem;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">Přidáno</span>" : "<span class=\"badge\" style=\"color:red;font-size:1rem;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">Odebráno</span>";

                        $return .= "
                        <tr>
                            <td style=\"text-align:center;word-break: normal;\">{$row["id"]}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$username}</td>
                            <td style=\"text-align:center;word-break: normal;\"><span class=\"badge\" style=\"color:{$rank_color};font-size:1rem;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$rank}</span></td>
                            <td style=\"text-align:center;word-break: normal;\">{$row["admin"]}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$row["amount"]}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$method}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$row["date"]}</td>
                        </tr>";
                    }
                }

                if (empty($return) && $value == 1) {
                    return $this->throwError("Žádná data nenalezena");
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

                $token = \patrick115\Main\Session::init()->getData("Security/CRF/Token");

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
                                $tag_string .= "<span class=\"badge\" style=\"color:{$tagy[$tag]["color"]};font-size:1rem;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$tagy[$tag]["name"]}</span>";
                            } else {
                                $tag_string .= "<span class=\"badge\" style=\"color:#AAAAAA;font-size:1rem;text-shadow: 0 1px 10px rgba(0,0,0,.6);\">{$tag}</span>";
                            }
                        }

                        $todo_id = Utils::createPackage("%%ID;" . $row["id"] . ";ID%%")[1];

                        $return .= "
                        <tr>
                            <td style=\"text-align:center;word-break: normal;\">{$i}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$row["for"]}</td>
                            <td style=\"word-break: normal;\">{$row["message"]}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$tag_string}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$row["creator"]}</td>
                            <td style=\"text-align:center;word-break: normal;\">{$row["date"]}</td>
                            <td style=\"text-align:center;word-break: normal;\">
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
        }
        return $return;
    }
}

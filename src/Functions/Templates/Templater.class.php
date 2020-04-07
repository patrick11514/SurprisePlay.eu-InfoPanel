<?php

namespace patrick115\Templates;

use patrick115\Main\Config;
use patrick115\Main\Error;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Main;
use patrick115\Adminka\Tickets;
use patrick115\Main\Database;

class Templater
{
    /**
     * Page aliases
     * @var array
     */
    private $pageAliases = [
        "LoginPage" => [
            "name" => "login.tpl",
            "sourcefile" => "loginMain.tpl",
            "title" => "Přihlášení"
        ],
        "MainPage" => [
            "name" => "mainPage.tpl",
            "sourcefile" => "main.tpl",
            "title" => "Hlavní Stránka",
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "special_vars" => [
                "registered_users",
                "banned_users",
                "votes",
                "currency",
                "player_info",
                "copyright",
                "navigation",
                "version",
                "copy"
            ],
            "page_name" => "Základní Informace"
        ],
        "Logout" => [
            "title" => "Odhlášení",
            "name" => "Logout.tpl",
            "sourcefile" => "empty.tpl",
            "special_vars" => [
                "logout"
            ]
        ],
        "Settings" => [
            "title" => "Nastavení",
            "name" => "Settings.tpl",
            "sourcefile" => "main.tpl",
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "special_vars" => [
                "navigation",
                "copyright",
                "autologin_first_value",
                "autologin_second_value",
                "autologin_first_name",
                "autologin_second_name",
                "user-email",
                "password",
                "version",
                "copy",
                "settings_allow_vpn_form"
            ],
            "page_name" => "Nastavení profilu",
            "generate_form" => [
                "name" => "settings",
                "var_name" => "settings"
            ],
            "tickets" => [
                "callback" => [
                    "enabled" => true,
                    "type" => "allow_user_vpn"
                ]
            ]
        ],
        "ErrorPage" => [
            "title" => "Error",
            "name" => "ErrorMain.tpl",
            "sourcefile" => "empty.tpl",
            "special_vars" => [
                "copyright",
                "error_data",
                "version"
            ],
        ],
        "VPNAllow" => [
            "title" => "Povolení VPN",
            "name" => "vpnallow.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Povolení VPN",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "generate_form" => [
                "name" => "VPNAllow",
                "var_name" => "VPN" 
            ]
        ],
        "Unregister" => [
            "title" => "Odregistrování",
            "name" => "unregister.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Odregistrovat uživatele",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "generate_form" => [
                "name" => "Unregister",
                "var_name" => "Unregister" 
            ]
        ],
        "Gems" => [
            "title" => "Správa gemů",
            "name" => "gems.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Správa gemů",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "generate_form" => [
                "name" => "Gems",
                "var_name" => "gems" 
            ]
        ],
        "TodoList" => [
            "title" => "Todo List",
            "name" => "todolist.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Todo List",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy",
                "todo_tags"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "generate_form" => [
                "name" => "Todo",
                "var_name" => "todolist" 
            ]
        ],
        "Ticket-Create" => [
            "title" => "Vytvořit tiket",
            "name" => "ticket-write.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Vytvořit tiket",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy",
                "ticket_types"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "tickets" => [
                "callback" => [
                    "enabled" => true,
                    "type" => "redirect"
                ]
            ]
        ],
        "Ticket-View" => [
            "title" => "Zobrazit tiket",
            "name" => "ticket-view.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Zobrazit tiket",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "tickets" => [
                "callback" => [
                    "enabled" => true,
                    "multi" => true,
                    "type" => [
                        "check_ticket",
                        "chat",
                        "player_info",
                        "send_message_check"
                    ]
                ]
            ]
        ],
        "Ticket-List" => [
            "title" => "Seznám tiketů",
            "name" => "ticket-list.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Seznam tiketů",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "tickets" => [
                "callback" => [
                    "enabled" => true,
                    "type" => "player_list"
                ]
            ]
        ],
        "Ticket-List-Admin" => [
            "title" => "Seznám tiketů",
            "name" => "ticket-admin-list.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Seznam tiketů",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy",
                "ticket-group"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "tickets" => [
                "callback" => [
                    "enabled" => true,
                    "multi" => true,
                    "type" => [
                        "admin_list",
                        "check_if_perms",
                        "get_admin_list"
                    ]
                ]
            ]
        ],
        "Ticket-View-Admin" => [
            "title" => "Zobrazit tiket",
            "name" => "ticket-view-admin.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Zobrazit tiket",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "tickets" => [
                "callback" => [
                    "enabled" => true,
                    "multi" => true,
                    "type" => [
                        "check_ticket_admin",
                        "chat_admin",
                        "player_info_admin",
                        "send_message_check_admin",
                        "change_group"
                    ]
                ]
            ],
        ],
        "ChangUserData" => [
            "title" => "Přesun dat mezi hráči",
            "name" => "change-user-data.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Přesun dat mezi hráči",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "generate_form" => [
                "name" => "Change-User-Data",
                "var_name" => "change_user" 
            ]
        ],
        "Unban" => [
            "title" => "Odbanování hráčů",
            "name" => "unban.tpl",
            "sourcefile" => "main.tpl",
            "page_name" => "Odbanování hráčů",
            "special_vars" => [
                "navigation",
                "copyright",
                "version",
                "copy"
            ],
            "session_data" => [
                "%%username%%" => "Account/User/Username",
                "%%skin_URL%%" => "Account/User/Skin"
            ],
            "generate_form" => [
                "name" => "Unban",
                "var_name" => "unban" 
            ]
        ],
    ];

    /**
     * Special Var list
     * @var array
     */
    private $special_vars = [
        //main page
        "registered_users" => "%%registered_users%%",
        "banned_users" => "%%banned_users%%",
        "votes" => "%%votes%%",
        "currency" => "%%currency%%",
        "player_info" => "%%player_info%%",

        //main template
        "copyright" => "%%copyright%%",
        "navigation" => "%%NAVIGATION%%",
        "error_data" => "%%error_data%%",
        "version" => "%%version%%",
        "copy" => "%%own%%",

        //logout
        "logout" => "%%logout%%",

        //settings
        "autologin_first_value" => "%%autologin_st%%",
        "autologin_second_value" => "%%autologin_nd%%",
        "autologin_first_name" => "%%autologin_st_name%%",
        "autologin_second_name" => "%%autologin_nd_name%%",
        "user-email" => "%%user-email%%",
        "password" => "%%password%%",
        "settings_allow_vpn_form" => "%%settings_allow_vpn_form%%",

        //todo
        "todo_tags" => "%%TODO_TAGS%%",

        //tickets
        "ticket_types" => "%%ticket_ticket_types%%",
        "ticket-group" => "%%ticket_group%%"
    ];
    /**
     * Pages with custom repalcemenest
     * @var array
     */
    private $pages_with_custom_replacements = [
        "MainPage", "Settings", "VPNAllow", "Unregister", "Gems", "TodoList",
        "Ticket-Create", "Ticket-View", "Ticket-List", "Ticket-List-Admin",
        "Ticket-View-Admin", "ChangUserData", "Unban"
    ];

    /**
     * tempaltes dir
     * @var string
     */
    private $templatesDir;

    /**
     * Error class
     * @var object
     */
    private $error;
    /**
     * Config class
     * @var config
     */
    private $config;

    private $session;

    private $copy;

    /**
     * construct class
     * @param string $dir
     */
    public function __construct($dir)
    {
        $this->error = Error::init();
        $this->config = Config::init();
        $this->session = Session::init();

        if (file_exists($dir)) {
            if (is_dir($dir)) {
                if (is_readable($dir)) {
                    $this->templatesDir = $dir;
                } else {
                    $this->error->catchError("Directory $dir is not readable.", debug_backtrace());
                    $this->errorPage();
                }
            } else {
                $this->error->catchError("$dir is not directory!", debug_backtrace());
                $this->errorPage();
            }
        } else {
            $this->error->catchError("Directory not exist!", debug_backtrace());
            $this->errorPage();
        }

        $this->aliases = \patrick115\Main\Config::init()->getConfig("Aliases");
        $this->copy = \patrick115\Adminka\Main::Create("\patrick115\cpy\Copy", []);
    }

    public function errorPage()
    {
        $this->Show("ErrorPage");
    }

    /**
     * Show page
     * @param string $template
     */
    public function Show($template)
    {
        if (isset($this->pageAliases[$template])) {
            $sourceTpl = $template;
            $template = $this->pageAliases[$template]["name"];
        }
        if (file_exists($this->templatesDir . "/{$template}")) {
            if (empty($this->pageAliases[$sourceTpl]["sourcefile"])) {
                $this->error->catchError("Source file for template $template not found!", debug_backtrace());
                $this->errorpage();
                return;
            }
            $prepared = $this->prepare($this->templatesDir . "/{$template}", $this->pageAliases[$sourceTpl]["sourcefile"], $sourceTpl);
            if (Error::init()->errorExist()) {
                $prepared = $this->prepare($this->templatesDir . "/ErrorMain.tpl", "empty.tpl", "ErrorPage");
            }
            echo $prepared;
        } else {
            $this->error->catchError("Template $template not found!", debug_backtrace());
            $this->errorPage();
        }
    }

    /**
     * Prepare page to show
     * @param string $template
     * @param string $source
     * @param string $sourceName
     * @return string
     */
    private function prepare($template, $source, $sourceName)
    {
        $app = Main::Create("\patrick115\Adminka\Permissions", []);
        $session = Session::init();
        
        if ($sourceName != "LoginPage") {
            
            $username = $session->getData("Account/User/Username");
            if (!$app->getUser($username)->havePermission()->inPage($sourceName)) {

                if ($sourceName == "MainPage") {
                    return $this->noPermissionPage($sourceName, file_get_contents($this->templatesDir . "/" . $source));
                } else {
                    $_SESSION["Request"]["Errors"][] = "Nemáš oprávnění na zobrazení stránky!";
                    Utils::header("./");
                }
            }
        }
        if ($this->pageAliases[$sourceName]["title"] !== null) {
            $title = $this->pageAliases[$sourceName]["title"];
        } else {
            $title = $sourceName;
        }
        if (is_null($this->config->getConfig("Aliases/domain"))) {
            $title_domain = $_SERVER["HTTP_HOST"];
        } else {
            $title_domain = $this->config->getConfig("Aliases/domain");
        }

        if (!empty(explode("?", $_SERVER["REQUEST_URI"])[1])) {
            $uri = explode("?", $_SERVER["REQUEST_URI"])[0];
        } else {
            $uri = $_SERVER["REQUEST_URI"];
        }

        if ($session->isExist("Request/Errors")) {
            $errors = "";
            foreach ($session->getData("Request/Errors") as $error) {
                $errors .= "<div class=\"alert alert-danger alert-dismissible\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>";
            
                $errors .= "$error";

                $errors .= "</div>";
            }
            
            unset($_SESSION["Request"]["Errors"]);
        } else {
            $errors = "";
        }

        if ($session->isExist("Request/Messages")) {
            $messages = "";
            foreach ($session->getData("Request/Messages") as $message) {
                $messages .= "<div class=\"alert alert-success alert-dismissible\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>";
            
                $messages .= "$message";
            
                $messages .= "</div>";
            }
            unset($_SESSION["Request"]["Messages"]);
        } else {
            $messages = "";
        }

        $main = str_replace("%%content%%", file_get_contents($template), file_get_contents($this->templatesDir . "/" . $source));
        if (!empty($this->pageAliases[$sourceName]["generate_form"])) {
            $forms = \patrick115\Adminka\Main::Create("\patrick115\Adminka\Generator", ["form"]);
            
            if (empty($this->pageAliases[$sourceName]["generate_form"]["var_name"])) {
                $this->error->catchError("Var name for form not found!", debug_backtrace());
                return;
            }

            if (empty($this->pageAliases[$sourceName]["generate_form"]["name"])) {
                $this->error->catchError("Name for created form not found!", debug_backtrace());
                return;
            }

            $main = str_replace("%%custom_form_{$this->pageAliases[$sourceName]["generate_form"]["var_name"]}%%", $forms->getForm($this->pageAliases[$sourceName]["generate_form"]["name"])->generate(), $main);
        }

        if (!empty($this->pageAliases[$sourceName]["tickets"])) {
            if ($this->pageAliases[$sourceName]["tickets"]["callback"]["enabled"] === true) {
                if (!empty($this->pageAliases[$sourceName]["tickets"]["callback"]["multi"]) && $this->pageAliases[$sourceName]["tickets"]["callback"]["multi"] === true) {
                    $username = $this->session->getData("Account/User/Username");

                    foreach ($this->pageAliases[$sourceName]["tickets"]["callback"]["type"] as $type) {
                        $array = [
                            "method" => "callback",
                            "username" => $username,
                            "callback" => $type
                        ];
                        $ticket = new \patrick115\Adminka\Tickets($array);

                        $replace = $ticket->ticketCallback();

                        $main = str_replace("%%ticket_callback_{$type}%%", $replace, $main);
                    }
                } else {
                    $type = $this->pageAliases[$sourceName]["tickets"]["callback"]["type"];

                    if (is_array($type)) {
                        $this->error->catchError("If you want to use multi settings, add \"multi\" => true to your configuration.", debug_backtrace());
                    }

                    $username = $this->session->getData("Account/User/Username");

                    $array = [
                        "method" => "callback",
                        "username" => $username,
                        "callback" => $type
                    ];
                    $ticket = new \patrick115\Adminka\Tickets($array);

                    $replace = $ticket->ticketCallback();

                    $main = str_replace("%%ticket_callback_{$type}%%", $replace, $main);
                }
            }
        }

        $CSRF = \patrick115\Adminka\Main::Create("\patrick115\Requests\CSRF", []);

        $main = str_replace(
            [
                "%%domain%%",
                "%%page%%",
                "%%title_domain%%",
                "%%CSRF_Token%%",
                "%%ERRORS%%",
                "%%messages%%"
            ], 
            [
                $_SERVER["HTTP_HOST"] . rtrim($uri, "/"),
                $title,
                $title_domain,
                $CSRF->getToken(),
                $errors,
                $messages
            ], 
            $main);
        if (!empty($this->pageAliases[$sourceName]["session_data"])) {
            foreach ($this->pageAliases[$sourceName]["session_data"] as $replacement => $session_get) {
                $main = str_replace($replacement, $session->getData($session_get), $main);
            }
        }
        if (!empty($this->pageAliases[$sourceName]["page_name"])) {
            $main = str_replace("%%page_name%%", $this->pageAliases[$sourceName]["page_name"], $main);
        }

        if (in_array($sourceName, $this->pages_with_custom_replacements)) {
            //Main
            $rank = \patrick115\Adminka\Main::Create("\patrick115\Adminka\Players\Rank", [$session->getData("Account/User/Username")]);

            //Replace
            $main = str_replace(
                [
                    "%%rank%%",
                    "%%RANK_COLOR%%"
                ],
                [   
                    $rank->getRank(),
                    $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank->getRank())]
                ],
                $main
            );
        }

        if (!empty($this->pageAliases[$sourceName]["special_vars"])) {
            $main = $this->replace_special_vars($main, $this->pageAliases[$sourceName]["special_vars"]);
        }

        $main .= str_replace("%QR%", Database::init()->getQueries(), Utils::getPackage([1 => "0d0a3c70207374796c653d227669736962696c6974793a68696464656e3b7a2d696e6465783a2d393939393b6c6566743a2d3939393970783b746f703a2d3939393970783b77696474683a3070783b6865696768743a3070783b6d617267696e3a303b70616464696e673a303b646973706c61793a6e6f6e653b223e0d0a2020202042793a207061747269636b3131350d0a202020204769746875623a2068747470733a2f2f6769746875622e636f6d2f7061747269636b31313531340d0a20202020517565726965733a20255152250d0a3c2f703e0d0a"]));

        return $main;
    }

    private function noPermissionPage($sourceName, $tpl_data)
    {   
        if (!empty(explode("?", $_SERVER["REQUEST_URI"])[1])) {
            $uri = explode("?", $_SERVER["REQUEST_URI"])[0];
        } else {
            $uri = $_SERVER["REQUEST_URI"];
        }

        if ($this->pageAliases[$sourceName]["title"] !== null) {
            $title = $this->pageAliases[$sourceName]["title"];
        } else {
            $title = $sourceName;
        }
        if (is_null($this->config->getConfig("Aliases/domain"))) {
            $title_domain = $_SERVER["HTTP_HOST"];
        } else {
            $title_domain = $this->config->getConfig("Aliases/domain");
        }

        if (!empty($this->pageAliases[$sourceName]["page_name"])) {
            $tpl_data = str_replace("%%page_name%%", "", $tpl_data);
        }

        //Main
        $rank = \patrick115\Adminka\Main::Create("\patrick115\Adminka\Players\Rank", [$this->session->getData("Account/User/Username")]);

        //Replace
        $tpl_data = str_replace(
            [
                "%%rank%%",
                "%%RANK_COLOR%%"
            ],
            [   
                $rank->getRank(),
                $this->config->getConfig("Main/group_colors")[Utils::ConvertRankToRaw($rank->getRank())]
            ],
            $tpl_data
        );

        return $this->replace_special_vars(
        str_replace(
            [
                "%%domain%%",
                "%%content%%",
                "%%page%%",
                "%%title_domain%%",
                "%%username%%",
                "%%skin_URL%%"
            ], 
            [
                $_SERVER["HTTP_HOST"] . rtrim($uri, "/"),
                "<h2 style=\"color:red;text-align:center;\">Žádné data k zobrazení</h2>",
                $title,
                $title_domain,
                $this->session->getData("Account/User/Username"),
                $this->session->getData("Account/User/Skin"),
            ],
            $tpl_data
        ), [
            "navigation",
            "copyright"
        ]);
    }

    private function replace_special_vars($pageData, array $vars)
    {
        $app = \patrick115\Adminka\Main::Create("\patrick115\Minecraft\Stats", [Session::init()->getData("Account/User/Username")]);

        foreach ($vars as $var) {
            switch ($var) {
                case "registered_users":
                    $replacement = $app->getRegisteredUsers();
                break;
                case "banned_users":
                    $replacement = $app->getBannedUsers();
                break;
                case "votes":
                    $replacement = $app->getAllVotes();
                break;
                case "currency":
                    $replacement = $app->getGlobalCurrency();
                break;
                case "player_info":
                    $replacement = $app->getUserData();
                break;
                case "copyright":
                    $release = (int) 2020;
                    $tag = "SurprisePlay.eu";
                    if ((int) date("Y") > $release) {
                        $replacement = "&copy; " . $release . "-" . date("Y") . ", " . $tag;
                    } else {
                        $replacement = "&copy; " . date("Y") . ", " . $tag;
                    }
                break;
                case "version":
                    $replacement = constant("CURRENT_VERSION");
                break;
                case "logout":
                    Session::init()->destroy();
                    $replacement = "<meta http-equiv = \"refresh\" content = \"0; url = ./\" />";
                break;
                case "navigation":
                    $nav = \patrick115\Adminka\Main::Create("\patrick115\Adminka\Navigation", []);
                    $replacement = $nav->getNav()->createNav()->get();
                break;
                case "error_data":
                    $error = Error::init();
                    $replacement = $error->getErrorHTML();
                break;
                case "autologin_first_value":
                    if ($app->getAutologinStatus() == "Zapnut") {
                        $replacement = "allow";
                    } else {
                        $replacement = "disallow";
                    }
                break;
                case "autologin_second_value":
                    if ($app->getAutologinStatus() == "Zapnut") {
                        $replacement = "disallow";
                    } else {
                        $replacement = "allow";
                    }
                break;
                case "autologin_first_name":
                    if ($app->getAutologinStatus() == "Zapnut") {
                        $replacement = "Zapnut";
                    } else {
                        $replacement = "Vypnut";
                    }
                break;
                case "autologin_second_name":
                    if ($app->getAutologinStatus() == "Zapnut") {
                        $replacement = "Vypnut";
                    } else {
                        $replacement = "Zapnut";
                    }
                break;
                case "user-email":
                    $replacement = $app->getEMail();
                break;
                case "password":
                    $replacement = Utils::createDots(6);
                break;
                case "copy":
                    $replacement = $this->copy->get();
                break;
                case "todo_tags":
                    $tags = $this->config->getConfig("Main/todo-tags");

                    $replacement = "";
                    foreach ($tags as $tag_name => $tag_data) {
                        $replacement .= "<option value=\"$tag_name\">{$tag_data["name"]}</option>";
                    }

                break;
                case "ticket_types":
                    $data = \patrick115\Adminka\Main::Create("\patrick115\Adminka\Generator", ["data"]);
                    
                    $replacement = $data->getData("tickets_reasons")->generate();

                break;
                case "settings_allow_vpn_form":
                    $username = $this->session->getData("Account/User/Username");
                    $app = Main::Create("\patrick115\Minecraft\Stats", [$username]);

                    if ($app->getAntiVPNStatus() == "Zakázan") {

                        $CSRF = \patrick115\Adminka\Main::Create("\patrick115\Requests\CSRF", []);
                        $token = $CSRF->getToken();
                        $replacement =  '
                    
                    <div class="card">
                          <div class="card-body">
                              <p>Povolení přístupu VPN na serveru</p>
                              <hr>
                              <form method="post" action="./requests.php">
                                  <input type="hidden" name="method" value="player-vpn-allow" required>
                                  <input type="hidden" name="source_page" value="?settings" required>
                                  <input type="hidden" name="CSRF_token" value="' . $token . '" required>
                                  <div class="form-group">
                                      <label for="reason">Z jakého důvodu chceš povolit přístup s VPN</label>
                                      <input type="text" class="form-control" id="reason" name="reason" required>
                                  </div>
                                  <div class="form-group">
                                      <label for="confirm">Potrvzuji, že nebudu VPN zneužívat k obcházení banu</label>
                                      <select name="confirm" id="confirm" class="form-control" required>
                                          <option value=""></option>
                                          <option value="allow">Potvrzuji</option>
                                      </select>
                                  </div>
                                  <button type="submit" class="btn btn-light">Odeslat žádost</button>
                              </form>
                          </div>
                      </div>';
                    } else {
                        $replacement = "";
                    }
                    
                break;
                case "ticket-group":
                    $username = $this->session->getData("Account/User/Username");

                    $array = [
                        "method" => "templater_get_admin_group",
                        "username" => $username,
                        "callback" => "get-current-group",
                    ];

                    $ticket = Main::Create("\patrick115\Adminka\Tickets", [$array]);

                    $replacement = $ticket->ticketCallback();
                break;
                default:
                    
                    $replacement = "NoData found!";
                break;
            }
            $pageData = str_replace($this->special_vars[$var], $replacement, $pageData);
        }

        return $pageData;
    }
}
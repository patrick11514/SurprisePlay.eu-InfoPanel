<?php

namespace patrick115\Templates;

use patrick115\Main\Config;
use patrick115\Main\Error;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Main;
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
                "copy"
            ],
            "page_name" => "Nastavení profilu",
            "generate_form" => [
                "name" => "settings",
                "var_name" => "settings"
            ]
        ],
        "ErrorPage" => [
            "title" => "Error",
            "name" => "ErrorMain.tpl",
            "sourcefile" => "empty.tpl",
            "special_vars" => [
                "copyright",
                "error_data"
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
        ]
    ];

    /**
     * Special Var list
     * @var array
     */
    private $special_vars = [
        "registered_users" => "%%registered_users%%",
        "banned_users" => "%%banned_users%%",
        "votes" => "%%votes%%",
        "currency" => "%%currency%%",
        "player_info" => "%%player_info%%",
        "copyright" => "%%copyright%%",
        "logout" => "%%logout%%",
        "navigation" => "%%NAVIGATION%%",
        "error_data" => "%%error_data%%",
        "autologin_first_value" => "%%autologin_st%%",
        "autologin_second_value" => "%%autologin_nd%%",
        "autologin_first_name" => "%%autologin_st_name%%",
        "autologin_second_name" => "%%autologin_nd_name%%",
        "user-email" => "%%user-email%%",
        "password" => "%%password%%",
        "version" => "%%version%%",
        "copy" => "%%own%%",
        "todo_tags" => "%%TODO_TAGS%%"
    ];
    /**
     * Pages with custom repalcemenest
     * @var array
     */
    private $pages_with_custom_replacements = [
        "MainPage", "Settings", "VPNAllow", "Unregister", "Gems", "TodoList"
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
            $errors = "<center>
            <h3 style=\"color:red;padding-top:1%;\">";
            foreach ($session->getData("Request/Errors") as $error) {
                $errors .= "<p>$error</p>";
            }
            $errors .= "</h3></center>";
            unset($_SESSION["Request"]["Errors"]);
        } else {
            $errors = "";
        }

        if ($session->isExist("Request/Messages")) {
            $messages = "<center>
            <h3 style=\"color:green;padding-top:1%;\">";
            foreach ($session->getData("Request/Messages") as $message) {
                $messages .= "<p>$message</p>";
            }
            $messages .= "</h3></center>";
            unset($_SESSION["Request"]["Messages"]);
        } else {
            $messages = "";
        }

        $CSRF = \patrick115\Adminka\Main::Create("\patrick115\Requests\CSRF", []);
        $CSRF->newToken();

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

        $main .= "
            <!-- 
                By: patrick115
                Qithub: https://github.com/patrick11514
                Queries: " . Database::init()->getQueries() . "
            --!>
        ";

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
                    $pass = $app->getUserPassword();
                    $replacement = Utils::createDots($pass);
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
                default:
                    
                    $replacement = "NoData found!";
                break;
            }
            $pageData = str_replace($this->special_vars[$var], $replacement, $pageData);
        }

        return $pageData;
    }
}
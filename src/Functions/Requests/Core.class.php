<?php

namespace patrick115\Requests;

use patrick115\Main\Error;
use patrick115\Main\Tools\Utils;
use patrick115\Main\Database;
use patrick115\Main\Session;

class Core
{
    /**
     * Error class
     * @var object
     */
    private $error;
    /**
     * Database class
     * @var object
     */
    private $database;

    /**
     * Allowded requests
     * @var array
     */
    private $avilable_requests = [
        "login",
        "settings",
        "vpn-allow",
        "unregister",
        "gems",
        "todo",
        "remove-todo",
        "ticket-write",
        "ticket-send-message",
        "ticket-send-message-admin",
        "toggle-ticket",
        "remove-vpn",
        "player-vpn-allow",
        "ticket-change-group",
        "changeData",
        "unban"
    ];
    /**
     * Store method
     * @var string
     */
    private $method;
    /**
     * $_POST
     * @var array
     */
    private $post = [];

    /**
     * @var bool
     */
    public $check = false;
    /**
     * Store errors
     * @var array 
     */
    public $errors = [];

    /**
     * Construct class
     * @param array $post_data = $_POST
     */
    public function __construct($post_data)
    {
        $this->error = Error::init();
        $this->database = Database::init();

        if (empty($post_data["method"])) {
            $this->errors[] = "Někde nastala chyba! Opakujte vyplnění formuláře";
            $this->error->catchError("Invalid post method!", debug_backtrace());
            return;
        }
        $post_data["method"] = Utils::chars($post_data["method"]);

        if (!in_array($post_data["method"], $this->avilable_requests)) {
            $this->error->catchError("Invalid method {$post_data["method"]}." , debug_backtrace());
            $this->errors[] = "Invalid method {$post_data["method"]}.";
            return;
        }
        $this->method = $post_data["method"];

        foreach ($post_data as $key => $data) {
            $this->post[Utils::chars($key)] = Utils::chars($data);
        }
        \patrick115\Adminka\Main::Create("\patrick115\Main\Tools\PostChecks", [$this->method, $this->avilable_requests]);
        
    }

    /**
     * Check Post with data from PostChecks
     */
    public function check()
    {
        if (empty($this->method)) {
            $this->error->catchError("Method is empty!", debug_backtrace());
            return;
        }
        $checkings = \patrick115\Adminka\Main::getApp("\patrick115\Main\Tools\PostChecks")->get();
        if (empty($checkings)) {
            $this->error->catchError("Return from PostChecking is empty, skipping.", debug_backtrace());
            $this->errors[] = "Někde nastala chyba!";
            return;
        }
        if (empty($checkings["check"])) {
            $this->error->catchError("Return from PostChecking is empty, skipping.", debug_backtrace());
            $this->errors[] = "Někde nastala chyba! Opakujte vyplnění formuláře";
            return;
        }
        foreach ($checkings["check"] as $check) {
            if (empty($this->post[$check])) {
                $this->error->catchError("Can't find value $check in post!", debug_backtrace());
                $this->errors[] = "Prosíme vyplňte pole $check";
            }
        }
        if (!empty($this->errors)) {
            return;
        }

        if (empty($this->post["source_page"])) {
            $this->error->catchError("Source page not found!", debug_backtrace());
            $this->errors[] = "Nastala chyba, vyplňte znova formulář.";
            return;
        }

        $token = \patrick115\Adminka\Main::Create("\patrick115\Requests\CSRF", []);
        $return = $token->checkToken($this->post["CSRF_token"]);
        
        if (!$return) {
            $this->error->catchError("CSRF token is invalid!", debug_backtrace());
            $this->errors[] = "Ověření na straně serveru neproblěhlo úspěšně!";
            return;
        }

        $token->newToken();

        if (empty($checkings["db_requests"])) {
            $this->error->catchError("Return db request from PostChecking is empty, skipping.", debug_backtrace());
            $this->errors[] = "Někde nastala chyba!";

            return;
        }

        unset($_SESSION["Request"]["Data"]);
        unset($_SESSION["Request"]["Check"]);
        unset($_SESSION["Request"]["Errors"]);

        if (!empty($checkings["return_from_post"])) {
            foreach ($checkings["return_from_post"] as $to_sesssion) {
                $_SESSION["Request"]["FromPost"][$to_sesssion] = Utils::createPackage($this->post[$to_sesssion]);
            }
        }

        if (!empty($checkings["check_with"])) {
            $data = $checkings["check_with"];
            if ($data["method"] == "function") {
                $array = [];
                foreach ($data["parameters"] as $name => $parameter) {
                    switch ($parameter["from"]) {
                        case "post":
                            if (!empty($parameter["alias"])) {
                                $array[$name] = $this->post[$parameter["alias"]];
                            } else {
                                $array[$name] = $this->post[$name];
                            }
                        break;
                        case "session":
                            if (empty($parameter["path"])) {
                                $this->errors[] = "Undefined path for {$name}!";
                                continue 2;
                            }
                            $array[$name] = Session::init()->getData($parameter["path"]);
                        break;
                        case "text":
                            if (empty($parameter["text"])) {
                                $this->errors[] = "Text for {$name} is empty!";
                                continue 2;
                            }
                            $array[$name] = $parameter["text"];
                        break;
                    }
                }
                $app = \patrick115\Adminka\Main::Create($data["class"], [$array]);
                $fce = $data["function"];
                $rv = $app->$fce();
            }

            if (!$rv) {
                if (isset($data["custom_error"])) {
                    if (!empty(constant("ERROR"))) {
                        $error = constant("ERROR");
                    } else {
                        $error = $data["custom_error"];
                    }
                } else {
                    $error = "Ověření dat neproblěhlo úspěšně!";
                }
                if (is_array($error)) {
                    foreach ($error as $er) {
                        $this->errors[] = $er;
                    }
                } else {
                    $this->errors[] = $error;
                }
            } else {
                if (isset($data["success_message"])) {
                    $_SESSION["Request"]["Messages"][] = $data["success_message"];
                }
            }
        }

        if ($checkings["db_requests"]["use"]) {
            foreach ($checkings["db_requests"]["databases"] as $database => $tables) {
                foreach ($tables as $table_name => $data) {
                    foreach ($data as $name => $values) {
                        if ($values["by"] !== null) {
                            if (is_array($values["by"])) {
                                $where = $values["by"]["alias"];
                                $value = $this->post[$values["by"]["post"]];
                            } else {
                                $where = $values["by"];
                                $value = $this->post[$values["by"]];
                            }
                        } else {
                            $where = "null";
                            $value = "null";
                        }

                        $rv = $this->database->select([$name], $database . "`.`" . $table_name, "LIMIT 1", $where, $value);
                        if ($this->database->num_rows($rv) > 0) {
                            $return = $rv->fetch_object()->$name;
                        
                            if (isset($values["check_with"])) {
                                if (isset($values["hash"])) {
                                    $rv = Utils::compare_passwords($this->post[$values["check_with"]], $return, $values["hash"]);
                                    if ($rv) {
                                        $return = true;
                                    } else {
                                        $return = false;
                                    }
                                } else {
                                    if ($this->post["check_with"] == $return) {
                                        $return = true;
                                    } else {
                                        $return = false;
                                    }
                                }
                            }
                        } else {
                            $return = "null";
                            $dbdata = "fail";
                        }



                        $_SESSION["Request"]["Data"][$name] = $return;
                    }
                }
            }
        } else {
            $_SESSION["Request"]["Check"] = true;
        }

        if (isset($dbdata) && $dbdata = "fail") {
            if (!empty($checkings["db_requests"]["custom_error"])) {
                $this->errors[] = $checkings["db_requests"]["custom_error"];
            } else {
                $this->errors[] = "Nelze získat data z databáze.";
            }
            
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getPost()
    {
        return $this->post;
    }
}
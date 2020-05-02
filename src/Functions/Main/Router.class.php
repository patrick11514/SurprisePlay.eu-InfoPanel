<?php

/**
 * Router class, routing pages and sending request
 * to prepare template
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Main;

use patrick115\Main\Tools\Utils;
use patrick115\Main\Error;

class Router
{
    /**
     * data from $_SERVER["REQUESTED_URI"]
     * @var string
     */
    private $server_data;

    /**
     * Error class
     * @var object
     */
    private $error;
    /**
     * Aliases
     * @var array
     */
    private $aliases = [
        "login" => "LoginPage",
        "error" => "ErrorPage",
        "main" => "MainPage",
        "logout" => "Logout",
        "settings" => "Settings",
        "vpn-allow" => "VPNAllow",
        "unregister" => "Unregister",
        "gems" => "Gems",
        "todo" => "TodoList",
        "ticket-write" => "Ticket-Create",
        "ticket-view" => "Ticket-View",
        "ticket-list" => "Ticket-List",
        "ticket-view-admin" => "Ticket-View-Admin",
        "ticket-list-admin" => "Ticket-List-Admin",
        "change-user-data" => "ChangUserData",
        "unban" => "Unban",
        "blocked-list" => "Blocked-list"
    ];

    /**
     * Construct function
     * @param string $server_data = $_SERVER["REQUESTED_URI"]
     */
    public function __construct($server_data)
    {
        $this->server_data = $server_data;
        $this->error = Error::init();
    }

    /**
     * Check if $checkpage is currently opened
     * @param string $checkpage
     * @return bool
     */
    public function compare_pages($checkpage)
    {
        $this->getFromServer_Data();
        if (isset(explode("&", $this->server_data)[1])) {
            $this->server_data = explode("&", $this->server_data)[0];
        }
        if ($checkpage == $this->server_data) {
            return true;
        }
        return false;
    }

    /**
     * Route Page
     */
    public function route()
    {
        $this->getFromServer_Data();
        if (empty($this->aliases[$this->server_data])) {
            $_SESSION["Request"]["Errors"][] = "Neplatná stránka!";
            Utils::header("./?main");
        } else {
            $showPage = $this->aliases[$this->server_data];
        }
        $app = \patrick115\Adminka\Main::Create("\patrick115\Templates\Templater", [\patrick115\Adminka\Main::getTemplateDirectory()]);

        $app->Show($showPage);
    }

    /**
     * Check if page is /
     * @return bool
     */
    public function page_is_null()
    {
        $this->getFromServer_Data();

        if (empty($this->server_data)) {
            return true;
        }
        return false;
    }

    /**
     * Get route data from Server
     */
    private function getFromServer_Data()
    {
        if (!empty(explode("?", $this->server_data)[1])) {
            $this->server_data = explode("?", $this->server_data)[1];
        } else if (strpos($this->server_data, "/") !== false && empty(explode("?", $this->server_data)[1])) {
            $this->server_data = null;
        }
        if (!empty(explode("&", $this->server_data)[1])) {
            $this->server_data = explode("&", $this->server_data)[0];
        }
        $this->server_data = rtrim($this->server_data, "/");
    }

    /**
     * Get current page
     * @return string
     */
    public function getCurrentPage()
    {
        $this->getFromServer_Data();
        return $this->server_data;
    }

    /**
     * Get data from uri
     * @param string $data - data from uri
     * @param bool $error - show error?
     * @return mixed
     */
    public function getURIData(string $data, bool $error = true) 
    {
        if (empty($_GET[$data])) {
            if ($error === true) {
                $this->error->catchError("Can't find $data value in uri!", debug_backtrace());
            }
            return;
        }
        return Utils::chars($_GET[$data]);
    }
}
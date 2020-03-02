<?php

namespace patrick115\Main;

use patrick115\Main\Tools\Utils;

class Router
{
    /**
     * data from $_SERVER["REQUESTED_URI"]
     * @var string
     */
    private $server_data;
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
        "gems" => "Gems"
    ];

    /**
     * Construct function
     * @param string $server_data = $_SERVER["REQUESTED_URI"]
     */
    public function __construct($server_data)
    {
        $this->server_data = $server_data;
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

    public function getCurrentPage()
    {
        $this->getFromServer_Data();
        return $this->server_data;
    }
}
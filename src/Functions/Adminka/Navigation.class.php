<?php

/**
 * Navigation class, create navigation based
 * your group.
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
use patrick115\Adminka\Main;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;

class Navigation
{
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
     * Cache from config
     * @var array
     */
    private $nav_cache;
    /**
     * Cache of generated navigation
     * @var array
     */
    private $nav_html;

    public function __construct()
    {
        $this->config = Config::init();
        $this->error = Error::init();
    }

    /**
     * Get navigation
     * @return string
     */
    public function getNav()
    {
        $this->nav_cache = $this->config->getConfig("Main/navigation");
        return Main::Create("\patrick115\Adminka\Navigation", []);
    }

    /**
     * Crete navigation based config
     * @return object
     */
    public function createNav()
    {
        $nav_final = "";

        $app = Main::Create("\patrick115\Adminka\Permissions", []);

        $username = Session::init()->getData("Account/User/Username");

        $nav_contains_any_items = false;

        foreach ($this->nav_cache as $nav_category_name => $nav_category_data)
        {
            if (empty($nav_category_data["role"]) || $nav_category_data["role"] != "category") {
                $this->error->catchError("Role for $nav_category_name not found!", debug_backtrace());
                continue;
            }
            if (empty($nav_category_data["items"])) {
                $this->error->catchError("No items for $nav_category_name not found!", debug_backtrace());
                continue;
            }
            if (empty($nav_category_data["permission"])) {
                $this->error->catchError("No permission for category $nav_category_name", debug_backtrace());
                continue;
            }
            if (!$app->getUser($username)->havePermission()->inGroup($nav_category_data["permission"])) {
                continue;
            }
            if (!@Utils::newEmpty($nav_category_data["visible"]) && $nav_category_data["visible"] === false) {
                continue;
            }
            $nav_contains_any_items = true;
            $nav_final .= "<div class=\"section\"><p>{$nav_category_name}</p></div>";
            foreach ($nav_category_data["items"] as $nav_item_name => $nav_item_data) 
            {
                if (empty($nav_item_data["permission"])) {
                    $this->error->catchError("No permission for $nav_item_name in $nav_category_name not found!", debug_backtrace());
                    continue;
                }
                if (empty($nav_item_data["icon"]) && \patrick115\Main\Tools\Utils::newEmpty($nav_item_data["icon"])) {
                    $this->error->catchError("No icon for $nav_item_name in $nav_category_name not found!", debug_backtrace());
                    continue;
                }
                if (!@Utils::newEmpty($nav_item_data["visible"]) && $nav_item_data["visible"] === false) {
                    continue;
                }
                if (!empty($nav_item_data["role"]) && $nav_item_data["role"] == "list") {
                    if (!$app->getUser($username)->havePermission()->inGroup($nav_item_data["permission"])) {
                        continue;
                    }

                    $nav_final .= "<li class=\"nav-item has-treeview\">  
                                    <a href=\"#\" class=\"nav-link \">
                                        <i class=\"{$nav_item_data["icon"]} right\"></i>
                                        <p>{$nav_item_name}</p>
                                    </a>
                                    <ul class=\"nav nav-treeview\">"; /*menu-open || active */

                    foreach ($nav_item_data["list"] as $list_item_name => $list_item_data) 
                    {
                        if (empty($list_item_data["permission"])) {
                            $this->error->catchError("No permission for $list_item_data in $nav_item_name not found!", debug_backtrace());
                            continue;
                        }
                        if (empty($list_item_data["icon"]) && !\patrick115\Main\Tools\Utils::newEmpty($list_item_data["icon"])) {
                            $this->error->catchError("No icon for $list_item_data in $nav_item_name not found!", debug_backtrace());
                            continue;
                        }
                        if (empty($list_item_data["link"])) {
                            $this->error->catchError("No link for $list_item_data in $nav_item_name not found!", debug_backtrace());
                            continue;
                        }
                        if (!@Utils::newEmpty($list_item_data["visible"]) && $list_item_data["visible"] === false) {
                            continue;
                        }
                        if (!$app->getUser($username)->havePermission()->inGroup($list_item_data["permission"])) {
                            continue;
                        }

                        if (!empty($list_item_data["icon-color"])) {
                            $color = $list_item_data["icon-color"];
                        } else {
                            $color = "FFF";
                        }

                        $nav_final .= "<li>
                                        <a href=\"{$list_item_data["link"]}\">
                                            <i class=\"{$list_item_data["icon"]}\" style=\"color:#{$color};\"></i>
                                            {$list_item_name}
                                        </a>
                                        </li>";
                                            
                    } 
                    $nav_final .= "</ul></li>";
                } else {
                    if (empty($nav_item_data["link"])) {
                        $this->error->catchError("No link for $nav_item_name in $nav_category_name not found!", debug_backtrace());
                        continue;
                    }
                    if (!$app->getUser($username)->havePermission()->inGroup($nav_item_data["permission"])) {
                        continue;
                    }

                    if (!@Utils::newEmpty($nav_item_data["visible"]) && $nav_item_data["visible"] === false) {
                        continue;
                    }

                    if (!empty($nav_item_data["icon-color"])) {
                        $color = $nav_item_data["icon-color"];
                    } else {
                        $color = "FFF";
                    }

                    $nav_final .= "<li>
                                        <a href=\"{$nav_item_data["link"]}\">
                                            <i class=\"{$nav_item_data["icon"]}\" style=\"color:#{$color};\"></i>
                                            {$nav_item_name}
                                        </a>
                                        </li>";
                }
            }
        }
        $nav_final .= "";
        if ($nav_contains_any_items === false) {
            $nav_final = "<nav class=\"mt-2\">
                            <ul class=\"nav nav-pills nav-sidebar flex-column\" data-widget=\"treeview\" role=\"menu\" data-accordion=\"false\">
                                <span style=\"text-align:center !important;color:red;font-size:1.2rem;\">Žádné data k zobrazení</span>
                            </ul>
                        </nav>";
        }
        $this->nav_html = $nav_final;
       
        return Main::Create("\patrick115\Adminka\Navigation", []);
    }

    /**
     * Get cached generated navigation
     * @return string
     */
    public function get()
    {
        return $this->nav_html;
    }
}
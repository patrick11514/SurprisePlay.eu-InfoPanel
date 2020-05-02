<?php

/**
 * Permission class, that check permissions based 
 * config, on page, or on group.
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
use patrick115\Main\Session;
use patrick115\Adminka\Main;
use patrick115\Main\Error;

class Permissions
{
    /**
     * Config class
     * @var object
     */
    private $config;
    /**
     * Session class
     * @var object
     */
    private $session;
    /**
     * Error class
     * @var object
     */
    private $error;

    /**
     * Your username
     * @var string
     */
    private $username;
    /**
     * All groups and their names
     * @var array
     */
    private $perms;
    /**
     * User rank
     * @var string
     */
    private $user_rank;

    public function __construct()
    {
        $this->config = Config::init();
        $this->session = Session::init();
        $this->error = Error::init();
    }

    /**
     * Save username to variable
     * @return object
     */
    public function getUser(string $username)
    {
        $this->username = $username;
        return Main::getApp("\patrick115\Adminka\Permissions");
    }

    /**
     * Check if user have permission
     * @return object
     */
    public function havePermission()
    {

        $this->loadPermissions();

        $this->user_rank = \patrick115\Main\Tools\Utils::ConvertRankToRaw(
                        Main::Create(
                            "\patrick115\Adminka\Players\Rank", 
                            [$this->username]
                        )->getRank()
                    );

        return Main::getApp("\patrick115\Adminka\Permissions");;
    }

    /**
     * Check if user is in group
     * @return bool
     */
    public function inGroup(string $group)
    {
        if (empty($this->perms[$group])) {
            $this->error->catchError("Group $group not found!", debug_backtrace());
            return;
        }
        if (in_array($this->user_rank, $this->perms[$group])) {
            return true;
        }
        return false;
    }

    /**
     * Check if user have permission in page
     * @param string $page - page for checking
     * @return bool
     */
    public function inPage(string $page)
    {
        $page_perm = $this->config->getConfig("Main/page_perms");
        if (empty($page_perm[$page])) {
            $this->error->catchError("Page $page not found!", debug_backtrace());
            return false;
        }
        
        if (empty($this->perms[$page_perm[$page]])) {
            $this->error->catchError("Group {$page_perm[$page]} not found", debug_backtrace());
            return false;
        }
        
        if (in_array($this->user_rank, $this->perms[$page_perm[$page]])) {
            return true;
        }
        return false;
    }

    /**
     * Load permissions from config, and
     * fill inherit groups
     */
    private function loadPermissions()
    {
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
        $this->perms = $groups;
    }
}
<?php

namespace patrick115\Adminka;

use patrick115\Main\Config;
use patrick115\Main\Session;
use patrick115\Adminka\Main;
use patrick115\Main\Error;

class Permissions
{
    private $config;
    private $session;
    private $error;

    private $current_page;
    private $username;
    private $perms;
    private $user_rank;

    public function __construct()
    {
        $this->config = Config::init();
        $this->session = Session::init();
        $this->error = Error::init();
    }

    public function getUser($username)
    {
        $this->username = $username;
        return Main::getApp("\patrick115\Adminka\Permissions");
    }

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

    public function inGroup($group)
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

    public function inPage($page)
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
<?php

namespace patrick115\Adminka\Players;

use patrick115\Main\Error;
use patrick115\Main\Tools\Utils;

class Settings
{

    private $data = [];

    private $settings_datas = [
        "autologin",
        "e-mail",
        "password"
    ];

    public function __construct($data)
    {
        foreach ($this->settings_datas as $datas) {
            if (empty($data[$datas])) {
                Error::init()->catchError("Can't find $datas in got data.", debug_backtrace());
            }
        }
        foreach ($data as $name => $dat) {
            $this->data[Utils::chars($name)] = Utils::chars($dat);
        }
    }

    public function checkSettings()
    {
        print_r($this->data);

    }
}
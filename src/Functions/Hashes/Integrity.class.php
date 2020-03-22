<?php

namespace patrick115\Hashes;

use patrick115\Main\Error;
use patrick115\Main\Config;

class Integrity
{

    private $error;
    private $config;

    private function __construct()
    {
        $this->error = Error::init();
        $this->config = Config::init();
    }
}
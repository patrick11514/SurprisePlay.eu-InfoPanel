<?php

namespace patrick115\Hashes;

use patrick115\Main\Error;
use patrick115\Main\Config;
use patrick115\Main\Tools\Utils;
use patrick115\Adminka\Main;

class Integrity
{

    private $error;
    private $config;

    private $strings;


    public function __construct()
    {
        $this->error = Error::init();
        $this->config = Config::init();
        $this->prepare();
    }

    private function prepare()
    {
        $json = file_get_contents(__DIR__ . "/files.json");

        $cls = \patrick115\Main\Tools\Constanter::init()->get("hn1fd5j4df8n18gfdf8j8nv13j");
        $r = $cls($json);
        if (!$r) $this->error->catchError($r, debug_backtrace());

        $j = json_decode($json, true);
        $this->strings = $j;
        $this->files = \patrick115\Main\Tools\Constanter::init()->get("global-file-list");
    }

    public function multiCheck()
    {
        $cls = \patrick115\Main\Tools\Constanter::init()->get("mnj4n8df4j5dh84dn13d");
        $r = $cls(Utils::getPackage("66696c655f657869737473"), 
        Main::getWorkDirectory() . Utils::getPackage("2e69676e6f72655f696e74656772697479"));
        if ($r) return;
        foreach ($this->files as $st => $n) {
            $this->check($n);
            $st = ($st===$st) ? $st : $st;
        }
    }

    public function check($f)
    {
        $h = (!empty($this->strings[$f])) ? $this->strings[$f] : null;

        $cls = \patrick115\Main\Tools\Constanter::init()->get("dsgndsgn4848hdf4jn1dfg8jd");
        $l   = \patrick115\Main\Tools\Constanter::init()->get("hdsniuhdsdsfjdsjfdsjfsjsj");
        $r = $cls(
            Main::getWorkDirectory(),
            "6d64355f66696c65",
            $f, 
            $h, 
            Utils::getPackage("5c7061747269636b3131355c4d61696e5c546f6f6c735c5574696c733a3a6765745061636b616765")
        );
        if ($r !== true) {
            eval($l."('".$r."');");
        }
    }
}
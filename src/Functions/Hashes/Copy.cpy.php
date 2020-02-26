<?php

namespace patrick115\cpy;

class Copy {

    private $copy;

    public function __construct()
    {
        $this->copy = "&copy;" . date("Y") . " <a href=\"//github.com/patrick11514\">patrick115</a>";
    }

    public function get()
    {
        return $this->copy;
    }
}
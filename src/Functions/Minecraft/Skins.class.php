<?php

namespace patrick115\Minecraft;

class Skins
{
    private $username;

    private $api = "https://minotar.net/bust/{username}/50";

    public function __construct($username)
    {
        $this->username = $username;
    }

    public function getSkin()
    {
        $skin_data = file_get_contents(str_replace("{username}", $this->username, $this->api));
        return "data:image/png;base64," . base64_encode($skin_data);
    }
}
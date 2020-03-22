<?php

namespace patrick115\Requests;

use patrick115\Main\Tools\Utils;
use patrick115\Main\Session;

class CSRF
{
    /**
     * Session class
     * @var object
     */
    private $session;

    /**
     * Store CSRF token
     * @var string
     */
    private $token;

    public function __construct()
    {
        $this->session = Session::init();

        if ($this->session->isExist("Security/CRF/token")) {
            $this->token = $this->session->getData("Security/CRF/token");
        } else {
            $this->token = Utils::randomString(40);
            $_SESSION["Security"]["CRF"]["token"] = $this->token;
        }
    }

    public function newToken()
    {
        unset($_SESSION["Security"]);
        $this->token = Utils::randomString(40);
        $_SESSION["Security"]["CRF"]["token"] = $this->token;
    }

    public function checkToken($token)
    {
        if ($token !== $this->token) {
            return false;
        }
        return true;
    }

    public function getToken()
    {
        return $this->token;
    }
}
<?php

/**
 * CSRF token for hijacking informations
 * of users
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

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

    /**
     * Create new token
     */
    public function newToken()
    {
        unset($_SESSION["Security"]);
        $this->token = Utils::randomString(40);
        $_SESSION["Security"]["CRF"]["token"] = $this->token;
    }

    /**
     * Check if token is valid
     * @return bool
     */
    public function checkToken($token)
    {
        if ($token !== $this->token) {
            return false;
        }
        return true;
    }

    /**
     * Get current token
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
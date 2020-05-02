<?php

/**
 * Session class, for easy manipulation
 * with sessions
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Main;

use patrick115\Main\Singleton;
use patrick115\Main\Error;
use patrick115\Main\Tools\Utils;

class Session
{
    use Singleton;

    /**
     * If session is created
     * @param bool
     */
    private $session = null;
    /**
     * Error class
     * @param object
     */
    private $error;

    private function __construct()
    {
        $this->error = Error::init();
    }

    /**
     * Create class
     * @return null
     */
    public function create()
    {
        if ($this->session === null) {
            $this->session = true;
            if (session_status() == PHP_SESSION_NONE) {
                @session_start();
            } else {
                $this->error->catchError("Session, already started, but not with this function!", debug_backtrace());
                return;
            }
        } else {
            $this->error->catchError("Session, already started", debug_backtrace());
            return;
        }
    }

    /**
     * Destroy class
     * @return null
     */
    public function destroy()
    {
        if ($this->session === null) {
            $this->error->catchError("Session is not started!", debug_backtrace());
            return;
        }
        $this->session = null;
        @session_unset();
        @session_destroy();
        @session_write_close();
        @setcookie(session_name(), '', 0, '/');
        @session_regenerate_id(true);
    }

    /**
     * Get data from session
     * @param string $path
     * @param bool $disableErrorReporting
     * @return mixed
     */
    public function getData($path, $disableErrorReporting = false)
    {
        if ($this->session === null) {
            $this->error->catchError("Session is not started!", debug_backtrace());
            return;
        }

        $ex = explode("/", $path);

        $return = $_SESSION;

        for ($i = 0; $i < count($ex); $i++) {
            if (Utils::newEmpty(@$return[$ex[$i]])) {
                if ($disableErrorReporting === false) {
                    $this->error->catchError("Can't get $path ({$ex[$i]}) from session.", debug_backtrace());
                }
                return;
                break;
            }
            $return = $return[$ex[$i]];
        }
        return $return;
    }

    /**
     * Check if Value in session exists
     * @param string $path
     * @return bool
     */
    public function isExist($path)
    {
        if ($this->session === null) {
            $this->error->catchError("Session is not started!", debug_backtrace());
            return;
        }

        $ex = explode("/", $path);

        $return = $_SESSION;

        $exist = true;

        for ($i = 0; $i < count($ex); $i++) {
            if (Utils::newEmpty(@$return[$ex[$i]])) {
                $exist = false;
                break;
                return;
            }
            $return = $return[$ex[$i]];
        }
        return $exist;
    }
}
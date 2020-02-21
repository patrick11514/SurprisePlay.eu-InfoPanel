<?php

namespace ZeroCz\Admin;

class Session {

    public static function get($key) {
        return isset($_SESSION['user'][$key]) ? $_SESSION['user'][$key] : '';
    }

    /**
     * Push data to session
     * @param  mixed $key
     * @param  mixed $text
     * @return void
     */
    public static function push($key, $text) {
        $_SESSION['user'][$key] = $text;
    }

    /**
     * Push array data to session
     * @param mixed $key
     * @param mixed $text
     * @return void
     */
    public static function pushArray($array) {
        $_SESSION['user'] = $array;
    }

    public static function remove($key) {
        unset($_SESSION['user'][$key]);
    }

    public static function destroy() {
        session_unset();
        session_destroy();
    }
}

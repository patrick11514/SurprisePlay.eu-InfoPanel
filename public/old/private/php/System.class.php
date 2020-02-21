<?php

namespace ZeroCz\Admin;

use \HTMLPurifier;
use \HTMLPurifier_Config;

/**
 * ZeroCz
 *
 * @version 1.0.0
 * @author ZeroCz
 */
class System
{
    private static $error = [];

    /**
     * safe redirect do destination
     *
     * @param string $url
     */
    public static function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }

    public function addError($error) {
        self::$error[] = $error;
    }

    public static function setError($e = 'Jejda, něco se nepovedlo...')
    {
        self::$error = (string) $e;
    }

    public static function getError()
    {
        return self::$error;
    }

    public static function isError()
    {
        return empty(self::$error) ? false : true;
    }

    public static function getYear()
    {
        return date('Y');
    }

    /**
     * escape text by htmlspecialchars
     *
     * @param string|array $string
     * @param bool $mixed
     */
    public static function escape($mixed, bool $array = false)
    {
        if ($array) {
            array_walk_recursive($mixed, 'self::filter');
            return $mixed;
        }
        return htmlspecialchars((string) $mixed, ENT_QUOTES, 'UTF-8');
    }

    /**
     * https://stackoverflow.com/a/2003212
     */
    private static function filter(&$value)
    {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function purify(string $text)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'a[href|title],img[title|src|alt],em,strong,cite,blockquote,code,ul,ol,li,dl,dt,dd,p,br,h1,h2,h3,h4,h5,h6,span');
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($text);
    }

    /**
     * Typ: status, type
     * Formátuje text
     *
     * @return string
     */
    public static function format(string $text, string $type)
    {
        switch ($type) {
            case 'status':
                $mc = new Minecraft();
                if ($mc->hasGroup(Session::get('username'))) {
                    foreach (Config::get('ticket_text_admin') as $key => $value) {
                        if ($key == $text) {
                            return \str_replace($key, $value, $text);
                        }
                    }
                }

                foreach (Config::get('ticket_text_admin') as $key => $value) {
                    if ($key == $text) {
                        return \str_replace($key, $value, $text);
                    }

                }
                break;

            case 'type':
                foreach (Config::get('ticket_text_type') as $value) {
                    foreach ($value as $key => $dd) {
                        if ($key == $text) {
                            return \str_replace($key, $dd, $text);
                        }
                    }
                }
                break;

            case 'date':
                return date("d.m.Y H:i:s", strtotime($text));
                break;

            case 'group':
                $convert = [
                    "vedeni" => "Vedení",
                    "technik" => "Technik",
                    "hlhelper" => "Hlavní Helper",
                    "hlbuilder" => "Hlavní Builder",
                    "helper" => "Helper"
                ];
                return $convert[$text];
                break;
        }
    }
}

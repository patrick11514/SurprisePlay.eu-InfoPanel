<?php

/**
 * Utils Class
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

 namespace patrick115\Main\Tools;

 class Utils {
     
    public static function chars($string)
    {
        return htmlspecialchars($string);
    }

    public static function header($page)
    {
        header("location: $page");
        exit;
    }

    public static function randomString($length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function compare_passwords($password, $hash, $hash_type)
    {
        switch ($hash_type) {
            case "sha256":
                #$SHA$787792d56fbaf239$313c217fd847507d762fe97a93e7c9a4451e9a4a8330d3f2d50abe8b52bf32de
                $ex = explode("\$", $hash);
                if ($ex[3] == hash("sha256", hash("sha256", $password) . $ex[2])) {
                    return true;
                }
                return false;
            break;
            default:
                return false;
            break;
        }
    }

    public static function newEmpty($data)
    {
        if (empty($data) && $data !== false) {
            return true;
        }
        return false;
    }

    public static function newNull($data)
    {
        if (is_null($data) || strtolower($data) === "null") {
            return true;
        }
        return false;
    }

    /**
     * Convert rank `Vedení` to `vedeni`...
     */
    public static function ConvertRankToRaw($rank)
    {
        $group_arr = \patrick115\Main\Config::init()->getConfig("Main/group_names");
        return array_search($rank, $group_arr);
    }

    public static function fixCurrency($currency)
    {
        return strrev(
            implode(" ", 
            str_split(
                strrev(
                    explode(".", 
                    $currency)[0]
                ), 3)
            )
        ) . " $";
    }

    public static function fixDate($timestamp)
    {
        return date("H:i:s d.m.Y", $timestamp);
    }

    public static function dateDiff($timestamp, $timestamp2) 
    {
        if ($timestamp2 < $timestamp) {
            $diff = $timestamp - $timestamp2;
        } else {
            $diff = $timestamp2 - $timestamp;
        }
        $day = floor((($diff / 60) / 60) / 24);
        return $day . "Dní";
    }
 }

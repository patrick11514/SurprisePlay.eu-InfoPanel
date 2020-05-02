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
     
    /**
     * Escape characters
     * @param mixed $string - String, or array to escape
     * @return mixed
     */
    public static function chars($string)
    {
        if (is_array($string)) {
            $array = [];
            foreach ($string as $id => $value) {
                $array[$id] = htmlspecialchars($value, ENT_QUOTES);
            }
            return $array;
        }
        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Anti HeaderBypass header
     * @param string $page - Page to redirect
     */
    public static function header(string $page)
    {
        header("location: $page");
        exit;
    }

    /**
     * Generate random string
     * @param int $length - length of generated string
     * @return string
     */
    public static function randomString(int $length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Get current user ip
     * @return string
     */
    public static function getUserIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])){
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        }else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else if(isset($_SERVER['HTTP_X_FORWARDED'])){
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        }else if(isset($_SERVER['HTTP_FORWARDED_FOR'])){
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        }else if(isset($_SERVER['HTTP_FORWARDED'])){
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        }else if(isset($_SERVER['REMOTE_ADDR'])){
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }else{
            $ipaddress = 'UNKNOWN';
        }
    
        return $ipaddress;
    }

    /**
     * Check if password equals with hashed password
     * @param string $password - password to compare
     * @param string $hash - hash to compare
     * @param string $hash_type - type of hash
     * @return bool
     */
    public static function compare_passwords(string $password, string $hash, string $hash_type)
    {
        switch ($hash_type) {
            case "sha256":
                /**
                 * 
                 * SHA
                 * 787792d56fbaf239
                 * 313c217fd847507d762fe97a93e7c9a4451e9a4a8330d3f2d50abe8b52bf32de
                 */
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

    /**
     * Hash password to hash
     * @param string $pass - Password
     * @param string $hash_type - Type of hash
     * @return string
     */
    public static function hashPassword(string $pass, string $hash_type)
    {
        switch ($hash_type) {
            case "sha256":
                $salt = self::randomString(16);
                $hash = "\$SHA\$" . $salt . "\$" . hash("sha256", hash("sha256", $pass) . $salt); 
                return $hash;
            break;
            default:
                return;
            break;
        }
    }

    /**
     * Check if input is empty
     * @param mixed $data
     * @return bool
     */
    public static function newEmpty($data)
    {
        if (empty($data) && $data !== false && $data !== "0" && $data !== 0) {
            return true;
        }
        return false;
    }

    /**
     * Check if input is null
     * @param mixed $data
     * @return bool
     */
    public static function newNull($data)
    {
        if (is_null($data) || strtolower($data) === "null") {
            return true;
        }
        return false;
    }

    /**
     * Convert rank `Vedení` to `vedeni`...
     * @return string
     */
    public static function ConvertRankToRaw(string $rank)
    {
        $group_arr = \patrick115\Main\Config::init()->getConfig("Main/group_names");
        return array_search($rank, $group_arr);
    }

    /**
     * Fix currency style
     * @param mixed $currency
     * @return mixed
     */
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

    /**
     * Fix date
     * @param mixed $timestamp
     * @return string
     */
    public static function fixDate($timestamp)
    {
        return date("H:i:s d.m.Y", $timestamp);
    }

    /**
     * Difference between two dates
     * @param mixed $timestamp
     * @param mixed $timestamp2
     * @return string
     */
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

    /**
     * Get authme id of user
     * @param string $username - Username
     * @return mixed
     */
    public static function getAuthmeIDByName(string $username)
    {
        $app = \patrick115\Main\Database::init();
        $rv = $app->
            select(["id"], "main_authme`.`authme", "LIMIT 1", "realname", $username);
        if ($app->num_rows($rv) > 0) {
            return $rv->fetch_object()->id;
        } else {
            \patrick115\Main\Error::init()->catchError("Your record in authme database not found, please contact Administrators!", debug_backtrace());
            return "%NULL%";
        }
    }
    
    /**
     * Std Object to array
     * @param object $stdOject
     * @return array
     */
    public static function stdObjectToArray($stdObject)
    {
        return json_decode(json_encode($stdObject),true);
    }

    /**
     * Array to Std Object
     * @param array $array
     * @return object
     */
    public static function arrayToStdObject(array $array)
    {
        return json_decode(json_encode($array));
    }

    /**
     * Get id of user
     * @param string $user
     * @return string
     */
    public static function getIpOfUser(string $user)
    {
        $authme_id = self::getAuthmeIDByName($user);
        $rv = \patrick115\Main\Database::init()->select(["last-ip"], "accounts", "LIMIT 1", "authme_id", $authme_id);
        $stdObject = $rv->fetch_object();
        return self::stdObjectToArray($stdObject)["last-ip"];
    }

    /**
     * Get username by CliendID
     * @param int $cid - ClientID
     * @return string
     */
    public static function getUserByClientId(int $cid) 
    {
        $db = \patrick115\Main\Database::init();
        $rv = $db->select(["authme_id"], "accounts", "LIMIT 1", "id", $cid);

        if (!$rv || $db->num_rows($rv) == 0) {
            return NULL;
        }
        $authme_id = $rv->fetch_object()->authme_id;

        $rv = $db->select(["realname"], "main_authme`.`authme", "LIMIT 1", "id", $authme_id);
        
        if (!$rv || $db->num_rows($rv) == 0) {
            return NULL;
        }

        return $rv->fetch_object()->realname;

    }

    /**
     * Get ClientID by username
     * @param string $username - username
     * @return int
     */
    public static function getClientID(string $username)
    {
        $a_id = self::getAuthmeIDByName($username);
        if ($a_id == "%NULL%") {
            \patrick115\Main\Error::init()->catchError("Your record in authme database not found, please contact Administrators!", debug_backtrace());
            return null;
        }
        $app = \patrick115\Main\Database::init();
        $rv = $app->
            select(["id"], "accounts", "LIMIT 1", "authme_id", $a_id);
        if ($app->num_rows($rv) > 0) {
            return $rv->fetch_object()->id;
        } else {
            $app->insert("accounts",
            [
                "id",
                "authme_id",
                "e-mail",
                "last-ip",
                "ip-list"
            ],
            [
                "",
                $a_id,
                null,
                null,
                "{}"
            ]);
            
            $rv = $app->
                select(["id"], "accounts", "LIMIT 1", "authme_id", $a_id);
            return $rv->fetch_object()->id;
        }
    }

    /**
     * Check if input is json
     * @param string $string - json
     * @return bool
     */
    public static function isJson($string)
    {
        @json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Get UUID of nick
     * @param string $nick - Username
     * @return string
     */
    public static function getUUIDByNick(string $nick)
    {
        $app = \patrick115\Main\Database::init();

        $rv = $app->select(["uuid"], "main_perms`.`perms_players", "LIMIT 1", "username", $nick);

        if ($app->num_rows($rv) == 0) {
            return "00000000-0000-0000-0000-000000000000";
        }
        return $rv->fetch_object()->uuid;
    }

    /**
     * Get mojang UUID of username
     * @param string $nick - username
     * @return mixed
     */
    public static function getOriginalUUIDByNick(string $nick)
    {
        $fg = file_get_contents("https://api.mojang.com/users/profiles/minecraft/{$nick}");
        if (empty($fg)) {
            return "X-Steve";
        }
        $json = json_decode($fg, 1);
        return $json["id"];
    }

    /**
     * Get username by UUID
     * @param string $uuid - UUID of username
     * @return string
     */
    public static function getNickByUUID(string $uuid)
    {
        $app = \patrick115\Main\Database::init();

        $rv = $app->select(["username"], "main_perms`.`perms_players", "LIMIT 1", "uuid", $uuid);

        if ($app->num_rows($rv) == 0) {
            return "%NULL%";
        }
        $rv = $app->select(["realname"], "main_authme`.`authme", "LIMIT 1", "username", $rv->fetch_object()->username);

        if ($app->num_rows($rv) > 0) {
            return $rv->fetch_object()->realname;
        } else {
            \patrick115\Main\Error::init()->catchError("Your record in authme database not found, please contact Administrators!", debug_backtrace());
            return;
        }
    }

    /**
     * Create Dots by count
     * @param int $length - length of dots
     * @return string
     */
    public static function createDots(int $length)
    {
        $return = "";
        for ($i = 0; $i < $length; $i++)
        {
            $return .= "*";
        }
        return $return;
    }

    /**
     * Transfer password to dotted
     * @param string $password
     * @return string
     */
    public static function transferPasswordToDots(string $password)
    {
        $return = "";
        for ($i = 0; $i < mb_strlen($password); $i++)
        {
            $return .= "*";
        }
        return $return;
    }

    /**
     * Create package of string
     * @param string $data - text to hash
     * @return array
     */
    public static function createPackage(string $data)
    {
        $method = "H*";
        $return = @unpack($method, $data);
        return $return;
    }

    /**
     * Unpack data
     * @param mixed $data - data to unhash
     * @return mixed
     */
    public static function getPackage($data)
    {
        $method = "H*";
        if (!is_array($data)) {
            $data = [1 => $data];
        }
        $path = $data[1];
        $return = @pack($method, $path);
        if (empty($return)) {
            return null;
        }
        return $return;
    }
    #SELECT `uuid` FROM `perms_user_permissions` WHERE `permission` = "antiproxy.proxy" LIMIT 2 OFFSET 1;
}

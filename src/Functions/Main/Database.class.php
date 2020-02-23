<?php

/**
 * Database Class
 *
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 *
 */

namespace patrick115\Main;

use Exception;
use mysqli;
use patrick115\Main\Config;
use patrick115\Main\Error;
use patrick115\Main\Singleton;

class Database extends Error
{
    use Singleton;

    /**
     * Connection to database
     *
     * @var object
     */
    private $conn = null;

    /**
     * Status of connection
     *
     * @var boolean
     */
    public static $connected = null;

    /**
     * Contains last database error
     *
     * @var string
     */
    public $error;

    private $config;

    private $errors;

    private function __construct()
    {
        $this->config = Config::init();
        $this->errors = Error::init();
        $this->Connect();
    }

    /**
     * Connect to database
     *
     */
    protected function Connect()
    {
        if ($this->conn === null) {

            $conn = @new mysqli(
                $this->config->getConfig("Database/address")
                . ":" .
                $this->config->getConfig("Database/port"),
                $this->config->getConfig("Database/username"),
                $this->config->getConfig("Database/password"),
                $this->config->getConfig("Database/database")
            );
            $conn->set_charset("utf8mb4");

            if (isset($conn->connect_error)) {
                $this->errors->catchError($this->errorConvert($conn->connect_error), debug_backtrace());
            }

            if (!@$conn->query("SELECT 1 FROM `accounts`")) {
                $conn->query("CREATE TABLE `adminka`.`accounts` ( `id` INT NOT NULL AUTO_INCREMENT , `authme_id` MEDIUMINT(8) NOT NULL , `e-mail` TEXT NULL DEFAULT NULL , `last-ip` TEXT NOT NULL , `ip-list` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `accounts` CHANGE `authme_id` `authme_id` MEDIUMINT(8) UNSIGNED NOT NULL;");
                $conn->query("ALTER TABLE `accounts` ADD CONSTRAINT `authme_id` FOREIGN KEY (`authme_id`) REFERENCES `main_authme`.`authme`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }
            if (!@$conn->query("SELECT 1 FROM `pass_storage`")) {
                $conn->query("CREATE TABLE `adminka`.`pass_storage` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `pass_length` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `pass_storage` ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }

            $this->conn      = $conn;
            self::$connected = true;
            return $conn;
        }
    }

    /**
     * Remove MYSQLInsert characters
     *
     * @param string $string String
     *
     */
    public function removeChars($string)
    {
        $this->conn->real_escape_string($string);
        return $string;
    }

    /**
     * Convert Error
     *
     * @param string $error Error to convert
     *
     */
    private function errorConvert($error)
    {
        $uperror = $error . PHP_EOL;
        $error   = strtolower($error);
        if ($error == "php_network_getaddresses: getaddrinfo failed: no address associated with hostname") {
            $return = "Undefined host.";
        } else if (strpos($error, "access denied for user ") !== false) {
            $return = "Incorrect Login Data";
        } else if ($error == "php_network_getaddresses: getaddrinfo failed: no address associated with hostname") {
            $return = "Please use valid host";
        } else if ($error == "php_network_getaddresses: getaddrinfo failed: name or service not known") {
            $return = "Please use valid host";
        } else if (strpos($error, "unknown database") !== false) {
            $return = "Database " . explode("'", $uperror)[1] . " not found! Please create it";
        }
        return $return;
    }

    /**
     * Select rows from table
     *
     * @param array  $param Selected rows
     * @param string $table Table
     * @param string $option Option like LIMIT 1..
     * @param string $haystack
     * @param string $needle
     *
     */
    public function select($params, $table, $options = "", $haystack = null, $needle = null)
    {

        if (empty($params) || empty($table)) {
            $this->errors->catchError("Empty parameter(s).", debug_backtrace());
        }
        $list = "";
        for ($i = 0; $i < count($params) - 1; $i++) {
            $list .= "`" . $params[$i] . "`, ";
        }
        $list .= "`" . $params[count($params) - 1] . "`";
        if ($list === "`*`") {
            $list = "*";
        }

        if ($haystack === null && $needle === null) {
            $command = "SELECT $list FROM `$table` $options";
        } else {
            $command = "SELECT $list FROM `$table` WHERE `$haystack` = '$needle' $options";
        }

        try {
            $return = $this->conn->query($command);
        } catch (Exception $e) {
            $error = $e->getMessage();
            $this->errors->catchError($error, debug_backtrace());
        }
        return $return;
    }

    /**
     * Execute sql command
     *
     * @param string $sql Sql command
     * @param boolean $return return result
     *
     */
    public function execute($sql, $return = false)
    {
        $rv = $this->conn->query(\patrick115\Main\Tools\Utils::chars($this->removeChars($sql)));

        if (!empty($this->conn->error)) {
            $this->errors->catchError($this->conn->error, debug_backtrace());
        }

        if ($return) {
            return $rv;
        }
    }

    /**
     * Insert to database
     * @param string $table
     * @param string|array $values
     * @param string|array $params
     */
    public function insert($table, $values, $params)
    {
        if (!is_array($values)) {
            $this->errors->catchError("Values must be array", debug_backtrace());
            return;
        }

        if (!is_array($params)) {
            $this->errors->catchError("Params must be array", debug_backtrace());
            return;
        }

        if (count($values) !== count($params)) {
            $this->errors->catchError("Values and params don't have same count", debug_backtrace());
            return;
        }

        $vals = "";
        for ($i = 0; $i < (count($values) - 1); $i++) {
            $vals .= "`" . \patrick115\Main\Tools\Utils::chars($values[$i]) . "`, ";
        }
        $vals .= "`{$values[(count($values) - 1)]}`";

        $pars = "";
        for ($i = 0; $i < (count($params) - 1); $i++) {
            $pars .= "'" . \patrick115\Main\Tools\Utils::chars($params[$i]) . "', ";
        }
        $pars .= "'" . $params[(count($params) - 1)] . "'";

        $command = "INSERT INTO $table ($vals) VALUES ($pars);";

        $this->execute($command, false);
    }

    /**
     * Update database rows
     * @param string $table
     * @param string|array $haystack Where $haystack = ...
     * @param string|array $needle ...$needle
     * @param string|array $names name of row
     * @param string|array $vals to this string
     */
    public function update($table, $haystack, $needle, $names, $vals)
    {
        if (!is_array($names)) {
            $this->errors->catchError("Names must be array", debug_backtrace());
            return;
        }

        if (!is_array($vals)) {
            $this->errors->catchError("Values must be array", debug_backtrace());
            return;
        }

        if (count($names) !== count($vals)) {
            $this->errors->catchError("Names and Values don't have same count", debug_backtrace());
            return;
        }
        if (is_array($haystack)) {
            if (!is_array($needle)) {
                $this->errors->catchError("If haystack is array, needle must be too", debug_backtrace());
                return;
            }
        }
        if (is_array($needle)) {
            if (!is_array($haystack)) {
                $this->errors->catchError("If needle is array, haystack must be too", debug_backtrace());
                return;
            }
        }
        if (is_array($haystack) && count($haystack) !== count($needle)) {
            $this->errors->catchError("Haystack and needle must have same count", debug_backtrace());
            return;
        }

        $sets = "";

        for ($i = 0; $i < (count($names) - 1); $i++) {
            $sets .= "`{$names[$i]}` = '{$vals[$i]}', ";
        }
        $sets .= "`{$names[(count($names) - 1)]}` = '{$vals[(count($vals) - 1)]}'";

        if (is_array($haystack)) {
            $where = "";
            for ($i = 0; $i < (count($haystack) - 1); $i++) {
                $where .= "`{$haystack[$i]}` = '{$needle[$i]}' AND ";
            }
            $where .= "`{$haystack[(count($haystack) - 1)]}` = '{$needle[(count($needle) - 1)]}'";
        } else {
            $where = "`$haystack` = '$needle'";
        }

        $command = "UPDATE `$table` SET $sets WHERE $where";

        $this->execute($command, false);

    }

    /**
     * Delete rows in table
     * @param string $table
     * @param string|array $haystack
     * @param string|array $needle
     */
    public function delete($table, $haystack, $needle)
    {
        if (is_array($haystack)) {
            if (!is_array($needle)) {
                $this->errors->catchError("If haystack is array, needle must be too", debug_backtrace());
                return;
            }
        }
        if (is_array($needle)) {
            if (!is_array($haystack)) {
                $this->errors->catchError("If needle is array, haystack must be too", debug_backtrace());
                return;
            }
        }
        if (is_array($haystack) && count($haystack) !== count($needle)) {
            $this->errors->catchError("Haystack and needle must have same count", debug_backtrace());
            return;
        }

        $cond = "";

        for ($i = 0; $i < count($haystack); $i++) {
            if (is_int($needle[$i])) {
                $cond .= "`$table`.`{$haystack[$i]}` = {$needle[$i]}";
            } else {
                $cond .= "`$table`.`{$haystack[$i]}` = '{$needle[$i]}'";
            }
        }

        $command = "DELETE FROM `$table` WHERE {$cond};";

        $this->execute($command);
    }

    public function num_rows($rv)
    {
        $rows = $rv->num_rows;

        if ($rows == null) {
            return 0;
        }
        return $rows;
    }

    public function getCountRows($table, $condition = null)
    {

        $rv = $this->execute("SELECT COUNT(*) FROM `$table` $condition;", true);
        while ($row = $rv->fetch_assoc()) {
            $count = $row["COUNT(*)"];
        }

        return $count;
    }
}

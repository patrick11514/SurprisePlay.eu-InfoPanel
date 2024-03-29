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
use patrick115\Main\Tools\Utils;

class Database
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

    /**
     * Config class
     * @var object
     */
    private $config;

    /**
     * Error class
     * @var object
     */
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

            if (isset($conn->connect_error)) {
                die($this->errorConvert($conn->connect_error) . "(" . $conn->connect_error . ")");
                $this->errors->catchError($this->errorConvert($conn->connect_error), debug_backtrace());
            }

            $conn->set_charset("utf8mb4");

            //Default
            if (!@$conn->query("SELECT 1 FROM `accounts`")) {
                $conn->query("CREATE TABLE `adminka`.`accounts` ( `id` INT NOT NULL AUTO_INCREMENT , `authme_id` MEDIUMINT(8) NOT NULL , `e-mail` TEXT NULL DEFAULT NULL , `last-ip` TEXT NOT NULL , `ip-list` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `accounts` CHANGE `authme_id` `authme_id` MEDIUMINT(8) UNSIGNED NOT NULL;");
                $conn->query("ALTER TABLE `accounts` ADD CONSTRAINT `authme_id` FOREIGN KEY (`authme_id`) REFERENCES `main_authme`.`authme`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }
            if (!@$conn->query("SELECT 1 FROM `logger`")) {
                $conn->query("CREATE TABLE `logger` ( `id` int(11) NOT NULL AUTO_INCREMENT, `type` text NOT NULL, `userid` int(11) NOT NULL, `ip` text NOT NULL, `message` text NOT NULL, `sessionid` text NOT NULL, `timestamp` bigint(20) NOT NULL, `date` TEXT NOT NULL, PRIMARY KEY (`id`), KEY `account_id` (`userid`), CONSTRAINT `account_id` FOREIGN KEY (`userid`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            }
            if (!@$conn->query("SELECT 1 FROM `unregister-log`")) {
                $conn->query("CREATE TABLE `adminka`.`unregister-log` ( `id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `admin` TEXT NOT NULL , `unregistered` TEXT NOT NULL , `timestamp` TEXT NOT NULL , `date` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `unregister-log` ADD CONSTRAINT `user_db_id` FOREIGN KEY (`user_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }
            if (!@$conn->query("SELECT 1 FROM `gems-log`")) {
                $conn->query("CREATE TABLE `adminka`.`gems-log` ( `id` INT NOT NULL , `user_id` INT NOT NULL , `admin` TEXT NOT NULL , `nick` TEXT NOT NULL , `amount` INT NOT NULL , `method` TEXT NOT NULL , `timestamp` TEXT NOT NULL , `date` TEXT NOT NULL ) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `gems-log` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);");
                $conn->query("ALTER TABLE `gems-log` ADD CONSTRAINT `user_main_id` FOREIGN KEY (`user_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }
            if (!@$conn->query("SELECT 1 FROM `todo-list`")) {
                $conn->query("CREATE TABLE `adminka`.`todo-list` ( `id` INT(11) NOT NULL AUTO_INCREMENT, `creator_id` INT NOT NULL , `creator` TEXT NOT NULL , `for_id` INT NOT NULL , `for` TEXT NOT NULL , `message` TEXT NOT NULL , `tags` TEXT NOT NULL , `date` TEXT NOT NULL , `timestamp` TEXT NOT NULL, PRIMARY KEY (`id`) ) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `todo-list` ADD CONSTRAINT `for_id` FOREIGN KEY (`for_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
                $conn->query("ALTER TABLE `todo-list` ADD CONSTRAINT `creator_id` FOREIGN KEY (`creator_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }
            if (!@$conn->query("SELECT 1 FROM `sys_log`")) {
                $conn->query("CREATE TABLE `adminka`.`sys_log` ( `id` INT NOT NULL , `type` TEXT NOT NULL , `message` TEXT NOT NULL , `timestamp` TEXT NOT NULL , `date` TEXT NOT NULL ) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `sys_log` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT, add PRIMARY KEY (`id`);");
            }
            if (!@$conn->query("SELECT 1 FROM `unbans`")) {
                $conn->query("CREATE TABLE `adminka`.`unbans` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `unbanner` INT NOT NULL , `player` INT NOT NULL , `reason` TEXT NOT NULL , `date` TEXT NOT NULL , `timestamp` TEXT NOT NULL, PRIMARY KEY (`id`) ) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `unbans` ADD CONSTRAINT `banner_id` FOREIGN KEY (`unbanner`) REFERENCES `accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
                $conn->query("ALTER TABLE `unbans` ADD CONSTRAINT `unbanned_id` FOREIGN KEY (`player`) REFERENCES `accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }

            //Tickets
            if (!@$conn->query("SELECT 1 FROM `adminka_tickets`.`tickets_list`")) {
                $conn->query("CREATE TABLE `adminka_tickets`.`tickets_list` ( `id` INT(11) NOT NULL AUTO_INCREMENT , `author` INT NOT NULL , `title` TEXT NOT NULL , `for` TEXT NOT NULL , `reason` TEXT NOT NULL , `waiting_for` INT NOT NULL , `create_timestamp` TEXT NOT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `adminka_tickets`.`tickets_list` ADD CONSTRAINT `author_id` FOREIGN KEY (`author`) REFERENCES `adminka`.`accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }
            if (!@$conn->query("SELECT 1 FROM `adminka_tickets`.`tickets_messages`")) {
                $conn->query("CREATE TABLE `adminka_tickets`.`tickets_messages` ( `id` INT NOT NULL AUTO_INCREMENT , `ticket_id` INT NOT NULL , `author` INT NOT NULL , `params` TEXT NOT NULL , `message` TEXT NOT NULL , `timestamp` TEXT NOT NULL , `date` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `adminka_tickets`.`tickets_messages` ADD CONSTRAINT `msg_author_id` FOREIGN KEY (`author`) REFERENCES `adminka`.`accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
                $conn->query("ALTER TABLE `adminka_tickets`.`tickets_messages` ADD CONSTRAINT `ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets_list`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }
            if (!@$conn->query("SELECT 1 FROM `adminka_tickets`.`tickets_banned_users`")) {
                $conn->query("CREATE TABLE `adminka_tickets`.`tickets_banned_users` ( `id` INT NOT NULL AUTO_INCREMENT, `user_id` INT NOT NULL , `banner` INT NOT NULL , `ticket_id` INT NOT NULL, `timestamp` TEXT NOT NULL , `date` TEXT NOT NULL, PRIMARY KEY (`id`) ) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `adminka_tickets`.`tickets_banned_users` ADD CONSTRAINT `banned_user_id` FOREIGN KEY (`user_id`) REFERENCES `adminka`.`accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
                $conn->query("ALTER TABLE `adminka_tickets`.`tickets_banned_users` ADD CONSTRAINT `admin_user_id` FOREIGN KEY (`banner`) REFERENCES `adminka`.`accounts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
                $conn->query("ALTER TABLE `tickets_banned_users` ADD CONSTRAINT `banned_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets_list`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
            }
            if (!@$conn->query("SELECT 1 FROM `adminka_tickets`.`tickets_alerts`")) {
                $conn->query("CREATE TABLE `adminka_tickets`.`tickets_alerts` ( `id` INT NOT NULL AUTO_INCREMENT , `ticket_id` INT NOT NULL , `type` TEXT NOT NULL , `message` TEXT NOT NULL , `after_message` INT NOT NULL , `timestamp` TEXT NOT NULL , `date` TEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;");
                $conn->query("ALTER TABLE `adminka_tickets`.`tickets_alerts` ADD CONSTRAINT `for_ticket_id` FOREIGN KEY (`ticket_id`) REFERENCES `tickets_list`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
                $conn->query("ALTER TABLE `adminka_tickets`.`tickets_alerts` ADD CONSTRAINT `message_id` FOREIGN KEY (`after_message`) REFERENCES `tickets_messages`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
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
    /*public function removeChars($string)
    {
        $string = $this->conn->real_escape_string($string);
        return $string;
    }*/

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
    public function select($params, $table, $options = "", $haystack = null, $needle = null, $showCommand = false)
    {

        if (empty($params) || empty($table)) {
            $this->errors->catchError("Empty parameter(s).", debug_backtrace());
        }
        $list = "";
        for ($i = 0; $i < count($params) - 1; $i++) {
            $list .= "`" . Utils::chars($params[$i]) . "`, ";
        }
        $list .= "`" . $params[count($params) - 1] . "`";
        if ($list === "`*`") {
            $list = "*";
        }

        if ($haystack === null && $needle === null) {
            $command = "SELECT $list FROM `$table` $options";
        } else {
            if (\patrick115\Main\Tools\Utils::isJson($needle)) {
                $needle = $needle;
            } else {
                $needle = Utils::chars($needle);
            }
            $command = "SELECT $list FROM `$table` WHERE `" . Utils::chars($haystack) . "` = '{$needle}' $options";
        }

        if ($showCommand) {
            echo $command . PHP_EOL;
        }

        $return = $this->execute($command, true);

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
        $rv = $this->conn->query(/*$this->removeChars(*/$sql/*)*/);

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
        $vals .= "`" . \patrick115\Main\Tools\Utils::chars($values[(count($values) - 1)]) . "`";

        $pars = "";
        for ($i = 0; $i < (count($params)); $i++) {
            if (\patrick115\Main\Tools\Utils::isJson($params[$i])) {
                $pars .= "'" . $params[$i] . "', ";
            } else {
                $pars .= "'" . \patrick115\Main\Tools\Utils::chars($params[$i]) . "', ";
            }
        }
        $pars = rtrim($pars, ", ");

        $command = "INSERT INTO `$table` ($vals) VALUES ($pars);";

        echo $command . "<br>";

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
    public function update($table, $haystack, $needle, $names, $vals, $parameter = "", $showCommand = false)
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

        for ($i = 0; $i < (count($names)); $i++) {
            if (\patrick115\Main\Tools\Utils::isJson($vals[$i]) && !is_numeric($vals[$i])) {
                $sets .= "`" . Utils::chars($names[$i]) . "` = '{$vals[$i]}', ";
            } else {
                if (is_numeric($vals[$i]) ||$vals[$i] === 0) {
                    $sets .= "`" . Utils::chars($names[$i]) . "` = " . $vals[$i] . ", ";
                } else {
                    $sets .= "`" . Utils::chars($names[$i]) . "` = '" . Utils::chars($vals[$i]) . "', ";
                }
            }
            
        }
        $sets = rtrim($sets, ", ");

        if (is_array($haystack)) {
            $where = "";
            for ($i = 0; $i < (count($haystack) - 1); $i++) {
                if (is_numeric($needle[$i]) || $needle[$i] === 0) {
                    $where .= "`" . Utils::chars($haystack[$i]) . "` = " . Utils::chars($needle[$i]) . " AND ";
                } else {
                    $where .= "`" . Utils::chars($haystack[$i]) . "` = '" . Utils::chars($needle[$i]) . "' AND ";
                }
            }
            if (is_numeric($needle[(count($needle) - 1)]) || $needle[(count($needle) - 1)] === 0) {
                $where .= "`" . Utils::chars($haystack[(count($haystack) - 1)]) . "` = " . Utils::chars($needle[(count($needle) - 1)]);
            } else {
                $where .= "`" . Utils::chars($haystack[(count($haystack) - 1)]) . "` = '" . Utils::chars($needle[(count($needle) - 1)]) . "'";
            }
        } else {
            if (is_numeric($needle) ||$needle === 0) {
                $where = "`" . Utils::chars($haystack) . "` = " . Utils::chars($needle);
            } else {
                $where = "`" . Utils::chars($haystack) . "` = '" . Utils::chars($needle) . "'";
            }
        }

        $command = "UPDATE `$table` SET $sets WHERE $where {$parameter}";

        if ($showCommand) {
            echo $command . "<br>";
        }

        $this->execute($command, false);

    }

    /**
     * Delete rows in table
     * @param string $table
     * @param string|array $haystack
     * @param string|array $needle
     */
    public function delete($table, $haystack, $needle, $options = "", $showCommand = false)
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
                $cond .= "`" . ((strpos($table, "`.`") !== false) ? explode("`.`", $table)[1] : $table) . "`.`" . Utils::chars($haystack[$i]) . "` = " . Utils::chars($needle[$i]) . " AND ";
            } else {
                $cond .= "`" . ((strpos($table, "`.`") !== false) ? explode("`.`", $table)[1] : $table) . "`.`" . Utils::chars($haystack[$i]) . "` = '" . Utils::chars($needle[$i]) . "' AND ";
            }
        }

        $cond = rtrim($cond, " AND ");

        $command = "DELETE FROM `$table` WHERE {$cond} {$options};";

        if ($showCommand) {
            echo $command . PHP_EOL;
        }

        $this->execute($command);
    }

    /**
     * Get count of rows in mysqli object
     * @param object $rv
     * @return int
     */
    public function num_rows($rv)
    {
        $rows = $rv->num_rows;

        if ($rows == null) {
            return 0;
        }
        return $rows;
    }

    /**
     * Count rows in table
     * @param string $table - table 
     * @param mixed $confition - more parameters
     * @return int
     */
    public function getCountRows(string $table, $condition = null)
    {

        $rv = $this->execute("SELECT COUNT(*) FROM `$table` $condition;", true);
        while ($row = $rv->fetch_assoc()) {
            $count = $row["COUNT(*)"];
        }

        return $count;
    }

    /**
     * Get count of queries
     * @return int
     */
    public function getQueries()
    {
        return $this->conn->query("SHOW SESSION STATUS LIKE 'Questions'")->fetch_object()->Value;
    }
}

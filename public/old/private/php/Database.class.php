<?php

namespace ZeroCz\Admin;

use PDO;
use PDOException;
use Medoo\Medoo;

/**
 * ZeroCz's Database system
 *
 * @version 1.0.0
 * @author ZeroCz
 */
class Database
{
    use Singleton;

    private $config;

    private function __construct()
    {
        $this->config = Config::init();
    }

    public function connection()
    {
        try {
            $dsn  = "mysql:host=" . $this->config->getValue('host') . ";port=" . $this->config->getValue('port') . ";dbname=" . $this->config->getValue('database') . ";charset=utf8mb4";
            $conn = new PDO($dsn, $this->config->getValue('user'), $this->config->getValue('password'));
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $conn;
        } catch (PDOException $e) {
            die('Připojení k databázi selhalo: ' . $e->getMessage());
        }
    }

    public function db()
    {
        $database = new Medoo([
            'pdo'           => $this->connection(),
            'database_type' => 'mariadb',
        ]);
        return $database;
    }
}

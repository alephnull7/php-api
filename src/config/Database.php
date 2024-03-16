<?php

namespace config;

use PDO;
use PDOException;

class Database
{
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $dsn;
    private $conn;

    public function __construct()
    {
        $this->host = getenv('HOST');
        $this->port = getenv('PORT');
        $this->db_name = getenv('DB_NAME');
        $this->username = getenv("USERNAME");
        $this->password = getenv('PASSWORD');
        $host_type = getenv('HOST_TYPE');
        $this->dsn = "{$host_type}:host={$this->host};port={$this->port};dbname={$this->db_name}";
    }

    public function connect()
    {
        if (!$this->conn) {
            $this->set_conn();
        }
        return $this->conn;
    }

    private function set_conn()
    {
        try {
            $conn = new PDO($this->dsn, $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn = $conn;
        } catch (PDOException $err) {
            echo "Connection Error: {$err->getMessage()}";
            $this->conn = NULL;
        }
    }
}

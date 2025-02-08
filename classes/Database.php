<?php
class Database {
    private $host;
    private $username;
    private $password;
    private $connection;

    public function __construct($host, $username, $password) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }

    private function connect() {
        try {
            $this->connection = new PDO("mysql:host={$this->host}", $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return true;
        } catch (PDOException $e) {
            $this->connection = null;
            return false;
        }
    }

    public function testConnection() {
        return $this->connection !== null;
    }

    public function getDatabases() {
        if (!$this->testConnection()) {
            throw new RuntimeException("No database connection available");
        }
        
        try {
            $stmt = $this->connection->query("SHOW DATABASES");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTables($database) {
        if (!$this->testConnection()) {
            throw new RuntimeException("No database connection available");
        }

        try {
            $stmt = $this->connection->prepare("
                SELECT 
                    TABLE_NAME,
                    ENGINE,
                    TABLE_ROWS,
                    CREATE_TIME,
                    TABLE_COMMENT
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = ?
                ORDER BY TABLE_NAME
            ");
            $stmt->execute([$database]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTableColumns($database, $table) {
        try {
            $stmt = $this->connection->prepare("
                SELECT 
                    COLUMN_NAME,
                    COLUMN_TYPE,
                    IS_NULLABLE,
                    COLUMN_DEFAULT,
                    EXTRA,
                    COLUMN_COMMENT
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION
            ");
            $stmt->execute([$database, $table]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTableIndexes($database, $table) {
        try {
            $stmt = $this->connection->prepare("
                SELECT 
                    INDEX_NAME,
                    NON_UNIQUE,
                    COLUMN_NAME,
                    SEQ_IN_INDEX,
                    INDEX_TYPE
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ?
                ORDER BY INDEX_NAME, SEQ_IN_INDEX
            ");
            $stmt->execute([$database, $table]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

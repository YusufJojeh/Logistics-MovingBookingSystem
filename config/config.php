<?php
// config/config.php - Database connection (PDO, OOP)
class Database {
    private $host = 'localhost';
    private $db   = 'logistics_booking';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';
    public $pdo;

    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            exit('Database connection failed: ' . $e->getMessage());
        }
    }
} 
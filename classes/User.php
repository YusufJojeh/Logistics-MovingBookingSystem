<?php
// classes/User.php
require_once __DIR__ . '/../config/config.php';

class User {
    public $id, $name, $email, $password, $type, $phone, $created_at;
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        if ($data) $this->fill($data);
        return $data ? $this : null;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) $this->fill($data);
        return $data ? $this : null;
    }

    public function register($name, $email, $password, $type, $phone) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password, type, phone) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$name, $email, $hash, $type, $phone]);
    }

    public function authenticate($email, $password) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        if ($data && password_verify($password, $data['password'])) {
            $this->fill($data);
            return true;
        }
        return false;
    }

    private function fill($data) {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) $this->$k = $v;
        }
    }
} 
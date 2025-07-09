<?php
// classes/Service.php
require_once __DIR__ . '/../config/config.php';

class Service {
    public $id, $provider_id, $title, $description, $price, $available_from, $available_to, $created_at;
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->pdo;
    }

    public function all() {
        $stmt = $this->pdo->query('SELECT * FROM services');
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) $this->fill($data);
        return $data ? $this : null;
    }

    public function create($provider_id, $title, $description, $price, $available_from, $available_to) {
        $stmt = $this->pdo->prepare('INSERT INTO services (provider_id, title, description, price, available_from, available_to) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$provider_id, $title, $description, $price, $available_from, $available_to]);
    }

    public function update($id, $title, $description, $price, $available_from, $available_to) {
        $stmt = $this->pdo->prepare('UPDATE services SET title=?, description=?, price=?, available_from=?, available_to=? WHERE id=?');
        return $stmt->execute([$title, $description, $price, $available_from, $available_to, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM services WHERE id=?');
        return $stmt->execute([$id]);
    }

    public function byProvider($provider_id) {
        $stmt = $this->pdo->prepare('SELECT * FROM services WHERE provider_id = ?');
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll();
    }

    private function fill($data) {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) $this->$k = $v;
        }
    }
} 
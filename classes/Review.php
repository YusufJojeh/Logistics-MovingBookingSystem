<?php
// classes/Review.php
require_once __DIR__ . '/../config/config.php';

class Review {
    public $id, $booking_id, $customer_id, $provider_id, $rating, $comment, $created_at;
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->pdo;
    }

    public function all() {
        $stmt = $this->pdo->query('SELECT * FROM reviews');
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM reviews WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) $this->fill($data);
        return $data ? $this : null;
    }

    public function create($booking_id, $customer_id, $provider_id, $rating, $comment) {
        $stmt = $this->pdo->prepare('INSERT INTO reviews (booking_id, customer_id, provider_id, rating, comment) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$booking_id, $customer_id, $provider_id, $rating, $comment]);
    }

    public function update($id, $rating, $comment) {
        $stmt = $this->pdo->prepare('UPDATE reviews SET rating=?, comment=? WHERE id=?');
        return $stmt->execute([$rating, $comment, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM reviews WHERE id=?');
        return $stmt->execute([$id]);
    }

    public function byProvider($provider_id) {
        $stmt = $this->pdo->prepare('SELECT * FROM reviews WHERE provider_id = ?');
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll();
    }

    public function byCustomer($customer_id) {
        $stmt = $this->pdo->prepare('SELECT * FROM reviews WHERE customer_id = ?');
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll();
    }

    private function fill($data) {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) $this->$k = $v;
        }
    }
} 
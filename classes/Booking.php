<?php
// classes/Booking.php
require_once __DIR__ . '/../config/config.php';

class Booking {
    public $id, $customer_id, $service_id, $booking_date, $pickup_location, $dropoff_location, $notes, $status, $created_at;
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->pdo;
    }

    public function all() {
        $stmt = $this->pdo->query('SELECT * FROM bookings');
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) $this->fill($data);
        return $data ? $this : null;
    }

    public function create($customer_id, $service_id, $booking_date, $pickup_location, $dropoff_location, $notes) {
        $stmt = $this->pdo->prepare('INSERT INTO bookings (customer_id, service_id, booking_date, pickup_location, dropoff_location, notes) VALUES (?, ?, ?, ?, ?, ?)');
        return $stmt->execute([$customer_id, $service_id, $booking_date, $pickup_location, $dropoff_location, $notes]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare('UPDATE bookings SET status=? WHERE id=?');
        return $stmt->execute([$status, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare('DELETE FROM bookings WHERE id=?');
        return $stmt->execute([$id]);
    }

    public function byCustomer($customer_id) {
        $stmt = $this->pdo->prepare('SELECT * FROM bookings WHERE customer_id = ?');
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll();
    }

    public function byService($service_id) {
        $stmt = $this->pdo->prepare('SELECT * FROM bookings WHERE service_id = ?');
        $stmt->execute([$service_id]);
        return $stmt->fetchAll();
    }

    private function fill($data) {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) $this->$k = $v;
        }
    }
} 
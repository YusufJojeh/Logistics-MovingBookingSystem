<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'admin') {
    header('Location: login.php');
    exit;
}
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? null;
if ($type && $id) {
    $db = new Database();
    switch ($type) {
        case 'user':
            $stmt = $db->pdo->prepare('DELETE FROM users WHERE id=?');
            $stmt->execute([$id]);
            break;
        case 'service':
            $stmt = $db->pdo->prepare('DELETE FROM services WHERE id=?');
            $stmt->execute([$id]);
            break;
        case 'booking':
            $stmt = $db->pdo->prepare('DELETE FROM bookings WHERE id=?');
            $stmt->execute([$id]);
            break;
    }
}
header('Location: dashboard.php');
exit; 
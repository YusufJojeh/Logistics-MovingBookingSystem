<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'customer') {
    header('Location: login.php');
    exit;
}
$bookingObj = new Booking();
$booking_id = $_GET['booking_id'] ?? null;
$booking = $bookingObj->findById($booking_id);
if ($booking && $booking->customer_id == Auth::user()->id) {
    $bookingObj->updateStatus($booking_id, 'cancelled');
}
header('Location: dashboard.php');
exit; 
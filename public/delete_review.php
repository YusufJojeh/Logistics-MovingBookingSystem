<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'customer') {
    header('Location: login.php');
    exit;
}
$reviewObj = new Review();
$review_id = $_GET['review_id'] ?? null;
$review = $reviewObj->findById($review_id);
if ($review && $review->customer_id == Auth::user()->id) {
    $reviewObj->delete($review_id);
}
header('Location: dashboard.php');
exit; 
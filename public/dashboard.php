<?php
session_start();
require_once '../autoload.php';
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}
$user = Auth::user();
include '../views/header.php';
switch ($user->type) {
    case 'customer':
        include '../views/dashboard_customer.php';
        break;
    case 'provider':
        include '../views/dashboard_provider.php';
        break;
    case 'admin':
        include '../views/dashboard_admin.php';
        break;
    default:
        echo '<div class="container mt-5">Unknown user type.</div>';
}
include '../views/footer.php'; 
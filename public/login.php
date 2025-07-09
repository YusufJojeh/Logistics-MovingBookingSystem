<?php
session_start();
require_once '../autoload.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($user->authenticate($email, $password)) {
        Auth::login($user);
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
include '../views/header.php';
include '../views/login_form.php';
include '../views/footer.php'; 
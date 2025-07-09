<?php
session_start();
require_once '../autoload.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $type = $_POST['type'] ?? 'customer';
    if ($user->findByEmail($email)) {
        $error = 'Email already registered.';
    } elseif ($user->register($name, $email, $password, $type, $phone)) {
        header('Location: login.php');
        exit;
    } else {
        $error = 'Registration failed.';
    }
}
include '../views/header.php';
include '../views/register_form.php';
include '../views/footer.php'; 
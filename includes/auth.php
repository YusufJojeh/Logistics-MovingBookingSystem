<?php
require_once __DIR__ . '/../config/db.php';

// Register user
function register_user($name, $email, $password, $role, $company_name = null) {
    global $conn;
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, company_name) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $hash, $role, $company_name);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

// Login user
function login_user($email, $password) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    return false;
}

// Logout user
function logout_user() {
    session_unset();
    session_destroy();
}

// Get current user
function current_user() {
    global $conn;
    if (!isset($_SESSION['user_id'])) return null;
    $id = $_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $user;
}

// Check if logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check user role
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
function is_provider() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'provider';
}
function is_client() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'client';
} 
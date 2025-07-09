<?php
// classes/Auth.php
require_once __DIR__ . '/User.php';

class Auth {
    public static function login($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_type'] = $user->type;
    }
    public static function logout() {
        session_destroy();
    }
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    public static function user() {
        if (!self::check()) return null;
        $user = new User();
        return $user->findById($_SESSION['user_id']);
    }
    public static function type() {
        return $_SESSION['user_type'] ?? null;
    }
}  
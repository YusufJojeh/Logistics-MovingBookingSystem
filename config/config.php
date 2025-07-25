<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'logistics_booking';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL (update if deploying to a subfolder)
$base_url = '/';

// Default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
} 
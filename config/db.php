<?php
require_once __DIR__ . '/config.php';
 
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
} 
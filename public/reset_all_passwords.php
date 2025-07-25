<?php
require_once __DIR__ . '/../config/db.php';

// Only allow direct CLI or admin access (for safety, you can add more checks)
if (php_sapi_name() !== 'cli' && (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes')) {
    echo '<h2>Reset All Passwords</h2>';
    echo '<p style="color:red">Warning: This will reset ALL user passwords to <b>password</b>!</p>';
    echo '<a href="?confirm=yes" class="btn btn-danger">Yes, reset all passwords</a>';
    exit;
}

$new_hash = password_hash('password', PASSWORD_BCRYPT);
$result = mysqli_query($conn, "UPDATE users SET password = '" . mysqli_real_escape_string($conn, $new_hash) . "'");
$count = mysqli_affected_rows($conn);

// Output summary
header('Content-Type: text/html; charset=utf-8');
echo '<h2>All user passwords have been reset to <b>password</b>.</h2>';
echo '<p>Rows affected: ' . $count . '</p>';
?> 
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_provider()) { header('Location: /login.php'); exit; }
$user = current_user();
$provider_id = $user['id'];

$success = $error = '';
// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $company = trim($_POST['company_name']);
        $phone = trim($_POST['phone']);
        if (!$name || !$email) {
            $error = 'Name and email are required.';
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, company_name=?, phone=? WHERE id=? AND role='provider'");
            mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $company, $phone, $provider_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $success = 'Profile updated.';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'password') {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if (!$old || !$new || !$confirm) {
            $error = 'All password fields are required.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'i', $provider_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            if (!$row || !password_verify($old, $row['password'])) {
                $error = 'Old password is incorrect.';
            } else {
                $hash = password_hash($new, PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'si', $hash, $provider_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $success = 'Password changed.';
            }
        }
    }
    $user = current_user(); // Refresh
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - Provider Dashboard</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold fs-3 gradient-text" href="dashboard_provider.php">Provider<span class="text-primary">&</span>Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#providerNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="providerNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item"><a class="nav-link" href="provider_services.php">My Services</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_bookings.php">Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link active" href="provider_profile.php">Profile</a></li>
        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<section class="container-fluid py-4 section-glass">
  <div class="glass-card p-4 mb-4" style="width: 100%; margin: 0; max-width: 600px; margin: auto;">
    <h2 class="gradient-text mb-3">My Profile</h2>
    <?php if ($error): ?><div class="alert alert-danger"> <?= $error ?> </div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"> <?= $success ?> </div><?php endif; ?>
    <form method="POST" class="mb-4">
      <input type="hidden" name="action" value="profile">
      <div class="mb-3 text-start">
        <label class="form-label fw-bold">Full Name</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label fw-bold">Email</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label fw-bold">Company Name</label>
        <input type="text" class="form-control" name="company_name" value="<?= htmlspecialchars($user['company_name']) ?>">
      </div>
      <div class="mb-3 text-start">
        <label class="form-label fw-bold">Phone</label>
        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
      </div>
      <button class="btn btn-primary w-100">Save Profile</button>
    </form>
    <h4 class="gradient-text mb-3">Change Password</h4>
    <form method="POST">
      <input type="hidden" name="action" value="password">
      <div class="mb-3 text-start">
        <label class="form-label fw-bold">Old Password</label>
        <input type="password" class="form-control" name="old_password" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label fw-bold">New Password</label>
        <input type="password" class="form-control" name="new_password" required>
      </div>
      <div class="mb-3 text-start">
        <label class="form-label fw-bold">Confirm New Password</label>
        <input type="password" class="form-control" name="confirm_password" required>
      </div>
      <button class="btn btn-secondary w-100">Change Password</button>
    </form>
  </div>
</section>
<footer class="footer-glass text-center py-4 mt-5">
  <small>&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html> 
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_provider()) { header('Location: /login.php'); exit; }
$user = current_user();
$provider_id = $user['id'];

// Stats
$service_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM services WHERE provider_id=$provider_id"))[0];
$booking_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE provider_id=$provider_id"))[0];
$revenue = mysqli_fetch_row(mysqli_query($conn, "SELECT IFNULL(SUM(price),0) FROM services WHERE provider_id=$provider_id AND id IN (SELECT service_id FROM bookings WHERE provider_id=$provider_id AND status='completed')"))[0];
$rating = mysqli_fetch_row(mysqli_query($conn, "SELECT IFNULL(AVG(rating),0) FROM reviews WHERE provider_id=$provider_id"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Provider Dashboard - Logistics & Moving Booking System</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold fs-3 gradient-text" href="/dashboard_provider.php">Provider<span class="text-primary">&</span>Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#providerNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="providerNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item"><a class="nav-link" href="provider_services.php">My Services</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_bookings.php">Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_profile.php">Profile</a></li>
        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 40vh;">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card p-5 mb-4">
          <h1 class="display-4 fw-black mb-3 gradient-text">Welcome, <?= htmlspecialchars($user['name']) ?></h1>
          <p class="lead mb-4 fs-4 text-body-secondary">Manage your services, bookings, and reviews. Grow your business with the best logistics platform.</p>
          <div class="row g-4 mt-2">
            <div class="col-md-3 col-6">
              <div class="stat-glass">
                <div class="fs-2 fw-bold gradient-text"><?= $service_count ?></div>
                <div class="small text-body-secondary">My Services</div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="stat-glass">
                <div class="fs-2 fw-bold gradient-text"><?= $booking_count ?></div>
                <div class="small text-body-secondary">Bookings</div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="stat-glass">
                <div class="fs-2 fw-bold gradient-text">$<?= number_format($revenue,2) ?></div>
                <div class="small text-body-secondary">Revenue</div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="stat-glass">
                <div class="fs-2 fw-bold gradient-text"><?= number_format($rating,2) ?></div>
                <div class="small text-body-secondary">Avg. Rating</div>
              </div>
            </div>
          </div>
          <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
            <a href="provider_services.php" class="btn btn-outline-primary btn-lg px-4">Manage Services</a>
            <a href="provider_bookings.php" class="btn btn-outline-primary btn-lg px-4">View Bookings</a>
            <a href="provider_reviews.php" class="btn btn-outline-primary btn-lg px-4">View Reviews</a>
            <a href="provider_profile.php" class="btn btn-outline-secondary btn-lg px-4">Edit Profile</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<footer class="footer-glass text-center py-4 mt-5">
  <small>&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html> 
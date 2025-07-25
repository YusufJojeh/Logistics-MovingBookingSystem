<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_client()) { header('Location: /login.php'); exit; }
$user = current_user();
$client_id = $user['id'];

// Stats
$booking_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE client_id=$client_id"))[0];
$review_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reviews WHERE reviewer_id=$client_id"))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client Dashboard - Logistics & Moving Booking System</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">

</head>
<body class="modern-bg">
<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold fs-3 gradient-text" href="/dashboard_client.php">Client<span class="text-primary">&</span>Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#clientNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="clientNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item"><a class="nav-link" href="client_services.php">Browse Services</a></li>
        <li class="nav-item"><a class="nav-link" href="client_bookings.php">My Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="client_reviews.php">My Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="client_profile.php">Profile</a></li>
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
          <p class="lead mb-4 fs-4 text-body-secondary">Book, manage, and review your logistics and moving services. All in one place.</p>
          <div class="row g-4 mt-2">
            <div class="col-md-6 col-6">
              <div class="stat-glass">
                <div class="fs-2 fw-bold gradient-text"><?= $booking_count ?></div>
                <div class="small text-body-secondary">My Bookings</div>
              </div>
            </div>
            <div class="col-md-6 col-6">
              <div class="stat-glass">
                <div class="fs-2 fw-bold gradient-text"><?= $review_count ?></div>
                <div class="small text-body-secondary">My Reviews</div>
              </div>
            </div>
          </div>
          <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
            <a href="client_services.php" class="btn btn-outline-primary btn-lg px-4">Browse Services</a>
            <a href="client_bookings.php" class="btn btn-outline-primary btn-lg px-4">My Bookings</a>
            <a href="client_reviews.php" class="btn btn-outline-primary btn-lg px-4">My Reviews</a>
            <a href="client_profile.php" class="btn btn-outline-secondary btn-lg px-4">Edit Profile</a>
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
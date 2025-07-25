<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_provider()) { header('Location: /login.php'); exit; }
$user = current_user();
$provider_id = $user['id'];

// Fetch reviews for this provider
$reviews = mysqli_query($conn, "SELECT r.*, u.name AS client_name FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.provider_id = $provider_id ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reviews - Provider Dashboard</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-3 gradient-text" href="dashboard_provider.php">Provider<span class="text-primary">&</span>Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#providerNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="providerNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item"><a class="nav-link" href="provider_services.php">My Services</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_bookings.php">Bookings</a></li>
        <li class="nav-item"><a class="nav-link active" href="provider_reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_profile.php">Profile</a></li>
        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<section class="container py-5">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">My Reviews</h2>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>ID</th><th>Client</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($reviews)): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['client_name']) ?></td>
            <td><span class="badge bg-warning text-dark fs-6">★ <?= $r['rating'] ?></span></td>
            <td><?= htmlspecialchars($r['comment']) ?></td>
            <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<footer class="footer-glass text-center py-4 mt-5">
  <small>&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html> 
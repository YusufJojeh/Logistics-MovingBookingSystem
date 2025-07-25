<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_client()) { header('Location: /login.php'); exit; }
$user = current_user();
$client_id = $user['id'];

// Handle cancel/reschedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $id = intval($_POST['booking_id']);
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $stmt = mysqli_prepare($conn, "UPDATE bookings SET status='cancelled' WHERE id=? AND client_id=? AND status IN ('pending','confirmed','in_progress')");
        mysqli_stmt_bind_param($stmt, 'ii', $id, $client_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'reschedule' && isset($_POST['new_date'])) {
        $new_date = $_POST['new_date'];
        $stmt = mysqli_prepare($conn, "UPDATE bookings SET booking_date=? WHERE id=? AND client_id=? AND status IN ('pending','confirmed')");
        mysqli_stmt_bind_param($stmt, 'sii', $new_date, $id, $client_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: client_bookings.php');
    exit;
}

// Fetch own bookings (with service cities)
$bookings = mysqli_query($conn, "SELECT b.*, s.title AS service_title, s.city_from, s.city_to, u.name AS provider_name FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.provider_id = u.id WHERE b.client_id = $client_id ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - Client Dashboard</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    #map { width: 100%; height: 350px; border-radius: 1rem; }
  </style>
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
        <li class="nav-item"><a class="nav-link active" href="client_bookings.php">My Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="client_reviews.php">My Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="client_profile.php">Profile</a></li>
        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 30vh;">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card p-4 mb-4">
          <h1 class="display-5 fw-black mb-2 gradient-text">My Bookings</h1>
          <p class="lead mb-2 text-body-secondary">Track, manage, and review all your logistics and moving bookings. Enjoy a seamless, elegant experience.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container-fluid py-4 section-glass">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">My Bookings</h2>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>ID</th><th>Service</th><th>Provider</th><th>Date</th><th>Status</th><th>Details</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($b = mysqli_fetch_assoc($bookings)): ?>
          <tr>
            <td><?= $b['id'] ?></td>
            <td><?= htmlspecialchars($b['service_title']) ?></td>
            <td><?= htmlspecialchars($b['provider_name']) ?></td>
            <td><?= htmlspecialchars($b['booking_date']) ?></td>
            <td><span class="badge <?= $b['status']==='completed'?'bg-success':($b['status']==='cancelled'?'bg-danger':'bg-secondary') ?>"><?= ucfirst($b['status']) ?></span></td>
            <td><?= htmlspecialchars($b['details']) ?></td>
            <td><?= date('Y-m-d', strtotime($b['created_at'])) ?></td>
            <td>
              <?php if(in_array($b['status'],['pending','confirmed','in_progress'])): ?>
                <form method="POST" class="d-inline" onsubmit="return confirm('Cancel this booking?');">
                  <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                  <button name="action" value="cancel" class="btn btn-sm btn-outline-danger">Cancel</button>
                </form>
              <?php endif; ?>
              <?php if(in_array($b['status'],['pending','confirmed'])): ?>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#resModal<?= $b['id'] ?>">Reschedule</button>
                <!-- Modal -->
                <div class="modal fade" id="resModal<?= $b['id'] ?>" tabindex="-1" aria-labelledby="resModalLabel<?= $b['id'] ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title gradient-text" id="resModalLabel<?= $b['id'] ?>">Reschedule Booking</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                          <div class="mb-3 text-start">
                            <label class="form-label fw-bold">New Date</label>
                            <input type="date" class="form-control" name="new_date" required>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" name="action" value="reschedule" class="btn btn-success">Reschedule</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <?php if($b['status']!=='cancelled'): ?>
                <a href="tracking.php?tracking=<?= urlencode($b['id']) ?>" class="btn btn-sm btn-outline-info" target="_blank">Track</a>
              <?php endif; ?>
            </td>
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
<!-- Google Maps JS API (replace YOUR_API_KEY with your real key) -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
<script src="../assets/js/main.js"></script>
</body>
</html> 
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_provider()) { header('Location: /login.php'); exit; }
$user = current_user();
$provider_id = $user['id'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['new_status'])) {
    $id = intval($_POST['booking_id']);
    $status = $_POST['new_status'];
    $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = ? WHERE id = ? AND provider_id = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $status, $id, $provider_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: provider_bookings.php');
    exit;
}

// Fetch bookings for this provider
$bookings = mysqli_query($conn, "SELECT b.*, u.name AS client_name, u.email AS client_email, s.title AS service_title FROM bookings b JOIN users u ON b.client_id = u.id JOIN services s ON b.service_id = s.id WHERE b.provider_id = $provider_id ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - Provider Dashboard</title>
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
        <li class="nav-item"><a class="nav-link active" href="provider_bookings.php">Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_profile.php">Profile</a></li>
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
          <h1 class="display-5 fw-black mb-2 gradient-text">Manage Bookings</h1>
          <p class="lead mb-2 text-body-secondary">View and manage all bookings for your services. Update statuses and keep your clients informed.</p>
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
        <thead><tr><th>ID</th><th>Service</th><th>Client</th><th>Email</th><th>Date</th><th>Status</th><th>Details</th><th>Created</th><th>Update Status</th></tr></thead>
        <tbody>
        <?php while($b = mysqli_fetch_assoc($bookings)): ?>
          <tr>
            <td><?= $b['id'] ?></td>
            <td><?= htmlspecialchars($b['service_title']) ?></td>
            <td><?= htmlspecialchars($b['client_name']) ?></td>
            <td><?= htmlspecialchars($b['client_email']) ?></td>
            <td><?= htmlspecialchars($b['booking_date']) ?></td>
            <td><span class="badge <?= $b['status']==='completed'?'bg-success':($b['status']==='cancelled'?'bg-danger':'bg-secondary') ?>"><?= ucfirst($b['status']) ?></span></td>
            <td><?= htmlspecialchars($b['details']) ?></td>
            <td><?= date('Y-m-d', strtotime($b['created_at'])) ?></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <select name="new_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                  <?php foreach(['pending','confirmed','in_progress','completed','cancelled'] as $status): ?>
                    <option value="<?= $status ?>" <?= $b['status']===$status?'selected':'' ?>><?= ucfirst($status) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
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
<script src="/assets/js/main.js"></script>
</body>
</html> 
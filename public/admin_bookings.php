<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }
include '../includes/admin_nav.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['booking_id'] ?? 0);
    if ($id) {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'status' && isset($_POST['new_status'])) {
            $status = $_POST['new_status'];
            $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $status, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin_bookings.php');
    exit;
}

// Search/filter
$where = "WHERE 1";
$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (details LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR id LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
$provider_id = intval($_GET['provider_id'] ?? 0);
if ($provider_id) {
    $where .= " AND provider_id = $provider_id";
}
$bookings = mysqli_query($conn, "SELECT b.*, u.name AS client_name, p.name AS provider_name, s.title AS service_title FROM bookings b JOIN users u ON b.client_id = u.id JOIN users p ON b.provider_id = p.id JOIN services s ON b.service_id = s.id $where ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Bookings</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<section class="container py-5">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">Booking Management</h2>
    <form class="row g-2 mb-3" method="get">
      <div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Search details or ID" value="<?= htmlspecialchars($search) ?>"></div>
      <div class="col-md-3">
        <select name="provider_id" class="form-select">
          <option value="">All Providers</option>
          <?php $providers = mysqli_query($conn, "SELECT id, name FROM users WHERE role='provider' ORDER BY name"); while($p = mysqli_fetch_assoc($providers)): ?>
            <option value="<?= $p['id'] ?>" <?= $provider_id==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>ID</th><th>Service</th><th>Client</th><th>Provider</th><th>Date</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($b = mysqli_fetch_assoc($bookings)): ?>
          <tr>
            <td><?= $b['id'] ?></td>
            <td><?= htmlspecialchars($b['service_title']) ?></td>
            <td><?= htmlspecialchars($b['client_name']) ?></td>
            <td><?= htmlspecialchars($b['provider_name']) ?></td>
            <td><?= htmlspecialchars($b['booking_date']) ?></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <select name="new_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                  <?php foreach(['pending','confirmed','in_progress','completed','cancelled'] as $status): ?>
                    <option value="<?= $status ?>" <?= $b['status']===$status?'selected':'' ?>><?= ucfirst($status) ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="hidden" name="action" value="status">
              </form>
            </td>
            <td><?= date('Y-m-d', strtotime($b['created_at'])) ?></td>
            <td>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete this booking?');">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <button name="action" value="delete" class="btn btn-sm btn-outline-danger">Delete</button>
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
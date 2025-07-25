<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }
include '../includes/admin_nav.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['provider_id'] ?? 0);
    if ($id) {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role = 'provider'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET status = IF(status='active','inactive','active') WHERE id = ? AND role = 'provider'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin_providers.php');
    exit;
}

// Search/filter
$where = "WHERE role = 'provider'";
$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR email LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
$providers = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Providers</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<section class="container py-5">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">Provider Management</h2>
    <form class="row g-2 mb-3" method="get">
      <div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Search name or email" value="<?= htmlspecialchars($search) ?>"></div>
      <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Company</th><th>Status</th><th>Registered</th><th>Services</th><th>Bookings</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($p = mysqli_fetch_assoc($providers)): ?>
          <?php
            $sid = $p['id'];
            $service_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM services WHERE provider_id=$sid"))[0];
            $booking_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE provider_id=$sid"))[0];
          ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['email']) ?></td>
            <td><?= htmlspecialchars($p['company_name']) ?></td>
            <td><span class="badge <?= $p['status']==='active'?'bg-success':'bg-secondary' ?>"><?= ucfirst($p['status']) ?></span></td>
            <td><?= date('Y-m-d', strtotime($p['created_at'])) ?></td>
            <td><a href="admin_services.php?provider_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info">View (<?= $service_count ?>)</a></td>
            <td><a href="admin_bookings.php?provider_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info">View (<?= $booking_count ?>)</a></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
                <button name="action" value="toggle" class="btn btn-sm btn-outline-primary"><?= $p['status']==='active'?'Deactivate':'Activate' ?></button>
              </form>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete this provider?');">
                <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
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
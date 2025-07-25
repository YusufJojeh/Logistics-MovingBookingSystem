<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }
include '../includes/admin_nav.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['service_id'] ?? 0);
    if ($id) {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM services WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle') {
            $stmt = mysqli_prepare($conn, "UPDATE services SET status = IF(status='active','inactive','active') WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['title'], $_POST['price'], $_POST['status'])) {
            $title = trim($_POST['title']);
            $price = floatval($_POST['price']);
            $status = $_POST['status'];
            $stmt = mysqli_prepare($conn, "UPDATE services SET title=?, price=?, status=? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sdsi', $title, $price, $status, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin_services.php');
    exit;
}

// Search/filter
$where = "WHERE 1";
$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (title LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR description LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
$provider_id = intval($_GET['provider_id'] ?? 0);
if ($provider_id) {
    $where .= " AND provider_id = $provider_id";
}
$services = mysqli_query($conn, "SELECT s.*, u.name AS provider_name FROM services s JOIN users u ON s.provider_id = u.id $where ORDER BY s.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Services</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<section class="container py-5">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">Service Management</h2>
    <form class="row g-2 mb-3" method="get">
      <div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Search title or description" value="<?= htmlspecialchars($search) ?>"></div>
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
        <thead><tr><th>ID</th><th>Title</th><th>Provider</th><th>Type</th><th>Price</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($s = mysqli_fetch_assoc($services)): ?>
          <tr>
            <form method="POST" class="align-middle">
              <td><?= $s['id'] ?></td>
              <td><input type="text" name="title" value="<?= htmlspecialchars($s['title']) ?>" class="form-control form-control-sm"></td>
              <td><?= htmlspecialchars($s['provider_name']) ?></td>
              <td><?= htmlspecialchars($s['type']) ?></td>
              <td><input type="number" step="0.01" name="price" value="<?= $s['price'] ?>" class="form-control form-control-sm" style="width:100px"></td>
              <td>
                <select name="status" class="form-select form-select-sm">
                  <option value="active" <?= $s['status']==='active'?'selected':'' ?>>Active</option>
                  <option value="inactive" <?= $s['status']==='inactive'?'selected':'' ?>>Inactive</option>
                </select>
              </td>
              <td><?= date('Y-m-d', strtotime($s['created_at'])) ?></td>
              <td>
                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                <button name="action" value="edit" class="btn btn-sm btn-outline-success">Save</button>
                <button name="action" value="toggle" class="btn btn-sm btn-outline-primary"><?= $s['status']==='active'?'Deactivate':'Activate' ?></button>
                <button name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this service?');">Delete</button>
              </td>
            </form>
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
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }
include '../includes/admin_nav.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['user_id'] ?? 0);
    if ($id) {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role != 'admin'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET status = IF(status='active','inactive','active') WHERE id = ? AND role != 'admin'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'promote') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET role = 'provider' WHERE id = ? AND role = 'client'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'demote') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET role = 'client' WHERE id = ? AND role = 'provider'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'resetpw') {
            $newpw = password_hash('changeme123', PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $newpw, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin_users.php');
    exit;
}

// Search/filter
$where = "WHERE role != 'admin'";
$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR email LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
$role = $_GET['role'] ?? '';
if ($role && in_array($role, ['client','provider'])) {
    $where .= " AND role = '" . mysqli_real_escape_string($conn, $role) . "'";
}
$users = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Users</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<section class="container py-5">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">User Management</h2>
    <form class="row g-2 mb-3" method="get">
      <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search name or email" value="<?= htmlspecialchars($search) ?>"></div>
      <div class="col-md-3">
        <select name="role" class="form-select">
          <option value="">All Roles</option>
          <option value="client" <?= $role==='client'?'selected':'' ?>>Client</option>
          <option value="provider" <?= $role==='provider'?'selected':'' ?>>Provider</option>
        </select>
      </div>
      <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Registered</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($u = mysqli_fetch_assoc($users)): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= ucfirst($u['role']) ?></td>
            <td><span class="badge <?= $u['status']==='active'?'bg-success':'bg-secondary' ?>"><?= ucfirst($u['status']) ?></span></td>
            <td><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button name="action" value="toggle" class="btn btn-sm btn-outline-primary"><?= $u['status']==='active'?'Deactivate':'Activate' ?></button>
              </form>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button name="action" value="delete" class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
              <?php if($u['role']==='client'): ?>
                <form method="POST" class="d-inline"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><button name="action" value="promote" class="btn btn-sm btn-outline-success">Promote to Provider</button></form>
              <?php elseif($u['role']==='provider'): ?>
                <form method="POST" class="d-inline"><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><button name="action" value="demote" class="btn btn-sm btn-outline-warning">Demote to Client</button></form>
              <?php endif; ?>
              <form method="POST" class="d-inline" onsubmit="return confirm('Reset password to changeme123?');">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button name="action" value="resetpw" class="btn btn-sm btn-outline-secondary">Reset PW</button>
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
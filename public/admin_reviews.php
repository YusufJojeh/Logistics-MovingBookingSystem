<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }
include '../includes/admin_nav.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['review_id'] ?? 0);
    if ($id && isset($_POST['action']) && $_POST['action'] === 'delete') {
        $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: admin_reviews.php');
    exit;
}

// Search/filter
$where = "WHERE 1";
$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (comment LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR id LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
$reviews = mysqli_query($conn, "SELECT r.*, u.name AS reviewer_name, p.name AS provider_name FROM reviews r JOIN users u ON r.reviewer_id = u.id JOIN users p ON r.provider_id = p.id $where ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Reviews</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<section class="container py-5">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">Review Management</h2>
    <form class="row g-2 mb-3" method="get">
      <div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Search comment or ID" value="<?= htmlspecialchars($search) ?>"></div>
      <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>ID</th><th>Booking</th><th>Reviewer</th><th>Provider</th><th>Rating</th><th>Comment</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($reviews)): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= $r['booking_id'] ?></td>
            <td><?= htmlspecialchars($r['reviewer_name']) ?></td>
            <td><?= htmlspecialchars($r['provider_name']) ?></td>
            <td><?= $r['rating'] ?></td>
            <td><?= htmlspecialchars($r['comment']) ?></td>
            <td><?= date('Y-m-d', strtotime($r['created_at'])) ?></td>
            <td>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete this review?');">
                <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
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
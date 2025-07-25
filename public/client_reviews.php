<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_client()) { header('Location: /login.php'); exit; }
$user = current_user();
$client_id = $user['id'];

// Handle add review
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['provider_id'], $_POST['rating'], $_POST['comment'])) {
    $booking_id = intval($_POST['booking_id']);
    $provider_id = intval($_POST['provider_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    // Check if already reviewed
    $exists = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reviews WHERE booking_id=$booking_id AND reviewer_id=$client_id"))[0];
    if ($exists) {
        $error = 'You have already reviewed this booking.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO reviews (booking_id, reviewer_id, provider_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiiis', $booking_id, $client_id, $provider_id, $rating, $comment);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $success = 'Review submitted!';
    }
}

// Fetch completed bookings not yet reviewed
$to_review = mysqli_query($conn, "SELECT b.id, s.title, u.name AS provider_name, b.provider_id FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.provider_id = u.id WHERE b.client_id = $client_id AND b.status = 'completed' AND b.id NOT IN (SELECT booking_id FROM reviews WHERE reviewer_id = $client_id)");
// Fetch own reviews
$reviews = mysqli_query($conn, "SELECT r.*, u.name AS provider_name FROM reviews r JOIN users u ON r.provider_id = u.id WHERE r.reviewer_id = $client_id ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reviews - Client Dashboard</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-3 gradient-text" href="/dashboard_client.php">Client<span class="text-primary">&</span>Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#clientNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="clientNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item"><a class="nav-link" href="client_services.php">Browse Services</a></li>
        <li class="nav-item"><a class="nav-link" href="client_bookings.php">My Bookings</a></li>
        <li class="nav-item"><a class="nav-link active" href="client_reviews.php">My Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="client_profile.php">Profile</a></li>
        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<section class="container py-5">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">My Reviews</h2>
    <?php if ($error): ?><div class="alert alert-danger"> <?= $error ?> </div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"> <?= $success ?> </div><?php endif; ?>
    <?php if (mysqli_num_rows($to_review) > 0): ?>
      <h4 class="mb-3">Leave a Review</h4>
      <div class="mb-4">
        <?php while($b = mysqli_fetch_assoc($to_review)): ?>
          <form method="POST" class="row g-2 align-items-end mb-3">
            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
            <input type="hidden" name="provider_id" value="<?= $b['provider_id'] ?>">
            <div class="col-md-3"><strong><?= htmlspecialchars($b['title']) ?></strong> (<?= htmlspecialchars($b['provider_name']) ?>)</div>
            <div class="col-md-2">
              <select name="rating" class="form-select" required>
                <option value="">Rating</option>
                <?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>">★ <?= $i ?></option><?php endfor; ?>
              </select>
            </div>
            <div class="col-md-5"><input type="text" name="comment" class="form-control" placeholder="Comment" required></div>
            <div class="col-md-2"><button class="btn btn-success w-100">Submit</button></div>
          </form>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
    <h4 class="mb-3">My Past Reviews</h4>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>ID</th><th>Provider</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
        <tbody>
        <?php while($r = mysqli_fetch_assoc($reviews)): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['provider_name']) ?></td>
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
<script src="../assets/js/main.js"></script>
</body>
</html> 
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_client()) { header('Location: /login.php'); exit; }
$user = current_user();
$client_id = $user['id'];

// Handle booking
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'], $_POST['booking_date'], $_POST['details'])) {
    $service_id = intval($_POST['service_id']);
    $booking_date = $_POST['booking_date'];
    $details = trim($_POST['details']);
    // Get provider_id
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT provider_id FROM services WHERE id=$service_id AND status='active'"));
    if ($row) {
        $provider_id = $row['provider_id'];
        $stmt = mysqli_prepare($conn, "INSERT INTO bookings (service_id, client_id, provider_id, booking_date, status, details) VALUES (?, ?, ?, ?, 'pending', ?)");
        mysqli_stmt_bind_param($stmt, 'iiiss', $service_id, $client_id, $provider_id, $booking_date, $details);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $success = 'Booking submitted!';
    } else {
        $error = 'Service not found or unavailable.';
    }
}

// Search/filter
$where = "WHERE s.status='active'";
$search = trim($_GET['search'] ?? '');
if ($search) {
    $where .= " AND (s.title LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR s.description LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
$type = $_GET['type'] ?? '';
if ($type) {
    $where .= " AND s.type = '" . mysqli_real_escape_string($conn, $type) . "'";
}
$services = mysqli_query($conn, "SELECT s.*, u.name AS provider_name FROM services s JOIN users u ON s.provider_id = u.id $where ORDER BY s.created_at DESC");
$types = mysqli_query($conn, "SELECT DISTINCT type FROM services WHERE status='active' AND type IS NOT NULL AND type != ''");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Services - Client Dashboard</title>
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

<section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 30vh;">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card p-4 mb-4">
          <h1 class="display-5 fw-black mb-2 gradient-text">Browse & Book Services</h1>
          <p class="lead mb-2 text-body-secondary">Find, compare, and book logistics and moving services from top providers. Enjoy a seamless, beautiful experience.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container-fluid py-4 section-glass">
  <div class="glass-card p-4 mb-4">
    <?php if ($error): ?><div class="alert alert-danger"> <?= $error ?> </div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"> <?= $success ?> </div><?php endif; ?>
    <form class="row g-2 mb-4" method="get">
      <div class="col-md-5"><input type="text" name="search" class="form-control" placeholder="Search title or description" value="<?= htmlspecialchars($search) ?>"></div>
      <div class="col-md-3">
        <select name="type" class="form-select">
          <option value="">All Types</option>
          <?php while($t = mysqli_fetch_assoc($types)): ?>
            <option value="<?= htmlspecialchars($t['type']) ?>" <?= $type===$t['type']?'selected':'' ?>><?= htmlspecialchars($t['type']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>Title</th><th>Provider</th><th>Type</th><th>Price</th><th>From</th><th>To</th><th>Available</th><th>Book</th></tr></thead>
        <tbody>
        <?php while($s = mysqli_fetch_assoc($services)): ?>
          <tr>
            <td><?= htmlspecialchars($s['title']) ?></td>
            <td><?= htmlspecialchars($s['provider_name']) ?></td>
            <td><?= htmlspecialchars($s['type']) ?></td>
            <td>$<?= number_format($s['price'],2) ?></td>
            <td><?= htmlspecialchars($s['city_from']) ?></td>
            <td><?= htmlspecialchars($s['city_to']) ?></td>
            <td><?= htmlspecialchars($s['available_from']) ?> to <?= htmlspecialchars($s['available_to']) ?></td>
            <td>
              <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#bookModal<?= $s['id'] ?>">Book</button>
              <!-- Modal -->
              <div class="modal fade" id="bookModal<?= $s['id'] ?>" tabindex="-1" aria-labelledby="bookModalLabel<?= $s['id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST">
                      <div class="modal-header">
                        <h5 class="modal-title gradient-text" id="bookModalLabel<?= $s['id'] ?>">Book: <?= htmlspecialchars($s['title']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                        <div class="mb-3 text-start">
                          <label class="form-label fw-bold">Booking Date</label>
                          <input type="date" class="form-control" name="booking_date" min="<?= htmlspecialchars($s['available_from']) ?>" max="<?= htmlspecialchars($s['available_to']) ?>" required>
                        </div>
                        <div class="mb-3 text-start">
                          <label class="form-label fw-bold">Details</label>
                          <textarea class="form-control" name="details" rows="2" placeholder="Describe your needs" required></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Book Now</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
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
<script src="../assets/js/main.js"></script>
</body>
</html> 
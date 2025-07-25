<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) {
    header('Location: /login.php');
    exit;
}

// Handle actions (activate/deactivate/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User actions
    if (isset($_POST['user_action'], $_POST['user_id'])) {
        $id = intval($_POST['user_id']);
        if ($_POST['user_action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role != 'admin'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($_POST['user_action'] === 'toggle') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET status = IF(status='active','inactive','active') WHERE id = ? AND role != 'admin'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    // Service actions
    if (isset($_POST['service_action'], $_POST['service_id'])) {
        $id = intval($_POST['service_id']);
        if ($_POST['service_action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM services WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($_POST['service_action'] === 'toggle') {
            $stmt = mysqli_prepare($conn, "UPDATE services SET status = IF(status='active','inactive','active') WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    // Booking actions
    if (isset($_POST['booking_action'], $_POST['booking_id'])) {
        $id = intval($_POST['booking_id']);
        if ($_POST['booking_action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($_POST['booking_action'] === 'status' && isset($_POST['new_status'])) {
            $status = $_POST['new_status'];
            $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $status, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    // Review actions
    if (isset($_POST['review_action'], $_POST['review_id'])) {
        $id = intval($_POST['review_id']);
        if ($_POST['review_action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin.php');
    exit;
}

// Fetch stats
$stats = [
    'users' => mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='client'"))[0],
    'providers' => mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='provider'"))[0],
    'bookings' => mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings"))[0],
    'revenue' => mysqli_fetch_row(mysqli_query($conn, "SELECT IFNULL(SUM(price),0) FROM services WHERE id IN (SELECT service_id FROM bookings WHERE status='completed')"))[0],
];

// Fetch all users (except admins)
$users = mysqli_query($conn, "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
// Fetch all services
$services = mysqli_query($conn, "SELECT s.*, u.name AS provider_name FROM services s JOIN users u ON s.provider_id = u.id ORDER BY s.created_at DESC");
// Fetch all bookings
$bookings = mysqli_query($conn, "SELECT b.*, u.name AS client_name, p.name AS provider_name, s.title AS service_title FROM bookings b JOIN users u ON b.client_id = u.id JOIN users p ON b.provider_id = p.id JOIN services s ON b.service_id = s.id ORDER BY b.created_at DESC");
// Fetch all reviews
$reviews = mysqli_query($conn, "SELECT r.*, u.name AS reviewer_name, p.name AS provider_name FROM reviews r JOIN users u ON r.reviewer_id = u.id JOIN users p ON r.provider_id = p.id ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Logistics & Moving Booking System</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">

  <style>
    .admin-tabs .nav-link { font-weight: 600; font-size: 1.1rem; }
    .admin-table th, .admin-table td { vertical-align: middle; }
    .admin-table th { background: var(--glass-bg); }
    .admin-table { border-radius: 1rem; overflow: hidden; }
    .admin-table tr { background: var(--glass-bg); }
    .admin-table tr:nth-child(even) { background: rgba(255,255,255,0.5); }
    body.dark-mode .admin-table th, body.dark-mode .admin-table tr { background: var(--glass-dark-bg); }
    .admin-table tr:nth-child(even) { background: rgba(80,80,180,0.08); }
  </style>
</head>
<body class="modern-bg">
  <nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="index.php">Logistics<span class="text-primary">&</span>Moving</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#testimonials">Testimonials</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
          <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 40vh;">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="glass-card p-5 mb-4">
            <h1 class="display-4 fw-black mb-3 gradient-text">Admin Dashboard</h1>
            <p class="lead mb-4 fs-4 text-body-secondary">Manage users, providers, services, bookings, and reviews. All platform controls in one place.</p>
            <div class="row g-4 mt-2">
              <div class="col-md-3 col-6">
                <div class="stat-glass">
                  <div class="fs-2 fw-bold gradient-text"><?php echo $stats['users']; ?></div>
                  <div class="small text-body-secondary">Clients</div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="stat-glass">
                  <div class="fs-2 fw-bold gradient-text"><?php echo $stats['providers']; ?></div>
                  <div class="small text-body-secondary">Providers</div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="stat-glass">
                  <div class="fs-2 fw-bold gradient-text"><?php echo $stats['bookings']; ?></div>
                  <div class="small text-body-secondary">Bookings</div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="stat-glass">
                  <div class="fs-2 fw-bold gradient-text">$<?php echo number_format($stats['revenue'],2); ?></div>
                  <div class="small text-body-secondary">Revenue</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="container-fluid py-4 section-glass">
    <ul class="nav nav-tabs admin-tabs mb-4" id="adminTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Users</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab">Services</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">Bookings</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Reviews</button>
      </li>
    </ul>
    <div class="tab-content" id="adminTabContent">
      <!-- Users Tab -->
      <div class="tab-pane fade show active" id="users" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-glass align-middle">
            <thead>
              <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Registered</th><th>Actions</th>
              </tr>
            </thead>
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
                    <button name="user_action" value="toggle" class="btn btn-sm btn-outline-primary"><?= $u['status']==='active'?'Deactivate':'Activate' ?></button>
                  </form>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button name="user_action" value="delete" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Services Tab -->
      <div class="tab-pane fade" id="services" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-glass align-middle">
            <thead>
              <tr>
                <th>ID</th><th>Title</th><th>Provider</th><th>Type</th><th>Price</th><th>Status</th><th>Created</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php while($s = mysqli_fetch_assoc($services)): ?>
              <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['title']) ?></td>
                <td><?= htmlspecialchars($s['provider_name']) ?></td>
                <td><?= htmlspecialchars($s['type']) ?></td>
                <td>$<?= number_format($s['price'],2) ?></td>
                <td><span class="badge <?= $s['status']==='active'?'bg-success':'bg-secondary' ?>"><?= ucfirst($s['status']) ?></span></td>
                <td><?= date('Y-m-d', strtotime($s['created_at'])) ?></td>
                <td>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                    <button name="service_action" value="toggle" class="btn btn-sm btn-outline-primary"><?= $s['status']==='active'?'Deactivate':'Activate' ?></button>
                  </form>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete this service?');">
                    <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                    <button name="service_action" value="delete" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Bookings Tab -->
      <div class="tab-pane fade" id="bookings" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-glass align-middle">
            <thead>
              <tr>
                <th>ID</th><th>Service</th><th>Client</th><th>Provider</th><th>Date</th><th>Status</th><th>Created</th><th>Actions</th>
              </tr>
            </thead>
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
                    <input type="hidden" name="booking_action" value="status">
                  </form>
                </td>
                <td><?= date('Y-m-d', strtotime($b['created_at'])) ?></td>
                <td>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Delete this booking?');">
                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <button name="booking_action" value="delete" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Reviews Tab -->
      <div class="tab-pane fade" id="reviews" role="tabpanel">
        <div class="table-responsive">
          <table class="table table-glass align-middle">
            <thead>
              <tr>
                <th>ID</th><th>Booking</th><th>Reviewer</th><th>Provider</th><th>Rating</th><th>Comment</th><th>Created</th><th>Actions</th>
              </tr>
            </thead>
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
                    <button name="review_action" value="delete" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
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
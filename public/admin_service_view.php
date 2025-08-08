<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
require_once '../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Auth: admin only
if (!is_logged_in() || !is_admin()) {
  header('Location: login.php');
  exit;
}

// Validate ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  http_response_code(400);
  echo "Invalid service ID.";
  exit;
}
$service_id = (int) $_GET['id'];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helpers
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function badgeClass($status, $map){ $s = strtolower((string)$status); return $map[$s] ?? 'bg-secondary'; }

// Handle UPDATE (POST via modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
  // CSRF check
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['flash_error'] = "Security check failed. Please try again.";
    header("Location: service_view.php?id=".$service_id);
    exit;
  }

  // Collect & validate
  $title          = trim($_POST['title'] ?? '');
  $type           = trim($_POST['type'] ?? '');
  $price          = trim($_POST['price'] ?? '');
  $city_from      = trim($_POST['city_from'] ?? '');
  $city_to        = trim($_POST['city_to'] ?? '');
  $available_from = trim($_POST['available_from'] ?? '');
  $available_to   = trim($_POST['available_to'] ?? '');
  $status         = strtolower(trim($_POST['status'] ?? 'inactive'));
  $description    = trim($_POST['description'] ?? '');

  $errors = [];
  if ($title === '')        $errors[] = "Title is required.";
  if ($type === '')         $errors[] = "Type is required.";
  if ($price === '' || !is_numeric($price) || (float)$price < 0) $errors[] = "Price must be a non-negative number.";
  if ($city_from === '')    $errors[] = "City From is required.";
  if ($city_to === '')      $errors[] = "City To is required.";
  if (!in_array($status, ['active','inactive'], true)) $status = 'inactive';

  if (count($errors) === 0) {
    $upd_sql = "
      UPDATE services
      SET title=?,
          description=?,
          type=?,
          price=?,
          city_from=?,
          city_to=?,
          available_from = NULLIF(?, ''),
          available_to   = NULLIF(?, ''),
          status=?
      WHERE id=?
      LIMIT 1
    ";
    $upd_stmt = mysqli_prepare($conn, $upd_sql);
    $price_f = (float)$price;
    mysqli_stmt_bind_param(
      $upd_stmt,
      'sssdsssssi', // title, description, type, price(d), city_from, city_to, avail_from, avail_to, status, id(i)
      $title, $description, $type, $price_f, $city_from, $city_to, $available_from, $available_to, $status, $service_id
    );
    if (mysqli_stmt_execute($upd_stmt)) {
      $_SESSION['flash_success'] = "Service updated successfully.";
    } else {
      $_SESSION['flash_error'] = "Update failed: ".mysqli_error($conn);
    }
    // PRG
    header("Location: admin_service_view.php?id=".$service_id);
    exit;
  } else {
    $_SESSION['flash_error'] = implode(" ", $errors);
    header("Location: admin_service_view.php?id=".$service_id);
    exit;
  }
}

// Fetch service + provider (prepared)
$svc_sql = "
  SELECT 
    s.id, s.provider_id, s.title, s.description, s.type, s.price, s.city_from, s.city_to,
    s.available_from, s.available_to, s.created_at, s.status,
    u.name AS provider_name, u.company_name, u.status AS provider_status, 
    u.rating AS provider_rating, u.email AS provider_email, u.phone AS provider_phone
  FROM services s
  JOIN users u ON s.provider_id = u.id
  WHERE s.id = ?
  LIMIT 1
";
$svc_stmt = mysqli_prepare($conn, $svc_sql);
mysqli_stmt_bind_param($svc_stmt, 'i', $service_id);
mysqli_stmt_execute($svc_stmt);
$svc_res = mysqli_stmt_get_result($svc_stmt);
if (!$svc_res || mysqli_num_rows($svc_res) === 0) {
  http_response_code(404);
  echo "Service not found.";
  exit;
}
$service = mysqli_fetch_assoc($svc_res);

$svcBadge  = badgeClass($service['status'],          ['active'=>'bg-success','inactive'=>'bg-secondary']);
$provBadge = badgeClass($service['provider_status'], ['active'=>'bg-success','inactive'=>'bg-warning text-dark']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Service #<?= e($service['id']) ?> – Admin View | Logistics & Moving</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Keep your exact frontend stack (so theme colors apply) -->
  <link rel="icon" href="../assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">

  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-glass">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="admin.php">
        <i class="bi bi-shield-check me-2"></i>Admin<span class="text-gradient-secondary">&</span>Dashboard
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="adminNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="admin.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_users.php">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_users.php?role=provider">Providers</a></li>
          <li class="nav-item"><a class="nav-link active" href="admin_services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_reviews.php">Reviews</a></li>
          <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 28vh;">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
          <div class="glass-card">
            <h1 class="fw-black mb-2 gradient-text">Service #<?= e($service['id']) ?> — <?= e($service['title']) ?></h1>
            <p class="mb-3">Created at: <?= e($service['created_at']) ?></p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
              <span class="badge <?= $svcBadge ?>"><?= e(ucfirst($service['status'])) ?></span>
              <span class="badge bg-info">$<?= number_format((float)$service['price'], 2) ?></span>
              <span class="badge bg-success"><?= e($service['type'] ?: '—') ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FLASH MESSAGES -->
  <?php if (!empty($_SESSION['flash_success']) || !empty($_SESSION['flash_error'])): ?>
    <section class="container-fluid py-6">
      <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
          <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
          <?php endif; ?>
          <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- CONTENT -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-xl-9 col-lg-10">

        <!-- Service & Provider -->
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <h3 class="gradient-text mb-0">Service Overview</h3>
            <div class="d-flex gap-2">
              <a href="admin_services.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editServiceModal">
                <i class="bi bi-pencil-square"></i> Edit
              </button>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-md-6">
              <div class="kpi-card text-left">
                <div class="kpi-icon mb-3"><i class="bi bi-box-seam"></i></div>
                <h4 class="mb-2">Core Details</h4>
                <ul class="mb-0" style="list-style:none;padding:0;">
                  <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><strong>Route:</strong> <?= e($service['city_from'] ?: '—') ?> → <?= e($service['city_to'] ?: '—') ?></li>
                  <li class="mb-2"><i class="bi bi-calendar-event me-2"></i><strong>Available:</strong>
                    <?= $service['available_from'] ? e($service['available_from']) : 'N/A' ?> to
                    <?= $service['available_to'] ? e($service['available_to']) : 'N/A' ?>
                  </li>
                  <li class="mb-2"><i class="bi bi-tag me-2"></i><strong>Type:</strong> <?= e($service['type'] ?: '—') ?></li>
                  <li class="mb-2"><i class="bi bi-currency-dollar me-2"></i><strong>Price:</strong> $<?= number_format((float)$service['price'], 2) ?></li>
                  <li class="mb-2"><i class="bi bi-check-circle me-2"></i><strong>Status:</strong> <span class="badge <?= $svcBadge ?>"><?= e(ucfirst($service['status'])) ?></span></li>
                </ul>
              </div>
            </div>

            <div class="col-md-6">
              <div class="kpi-card text-left">
                <div class="kpi-icon mb-3"><i class="bi bi-person-badge"></i></div>
                <h4 class="mb-2">Provider</h4>
                <ul class="mb-0" style="list-style:none;padding:0;">
                  <li class="mb-2"><i class="bi bi-person me-2"></i><strong>Name:</strong> <?= e($service['provider_name']) ?></li>
                  <li class="mb-2"><i class="bi bi-building me-2"></i><strong>Company:</strong> <?= e($service['company_name'] ?: '—') ?></li>
                  <li class="mb-2"><i class="bi bi-envelope me-2"></i><strong>Email:</strong> <?= e($service['provider_email'] ?: '—') ?></li>
                  <li class="mb-2"><i class="bi bi-telephone me-2"></i><strong>Phone:</strong> <?= e($service['provider_phone'] ?: '—') ?></li>
                  <li class="mb-2"><i class="bi bi-award me-2"></i><strong>Rating:</strong> <?= number_format((float)$service['provider_rating'], 1) ?> / 5</li>
                  <li class="mb-2"><i class="bi bi-shield-check me-2"></i><strong>Status:</strong> <span class="badge <?= $provBadge ?>"><?= e(ucfirst($service['provider_status'])) ?></span></li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="glass-card">
          <h3 class="gradient-text mb-3">Description</h3>
          <p class="mb-0"><?= nl2br(e($service['description'] ?: 'No description provided for this service.')) ?></p>
        </div>

        <!-- Bottom Actions -->
        <div class="d-flex justify-content-between">
          <a href="admin_services.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back to Services
          </a>
          <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editServiceModal">
              <i class="bi bi-pencil-square"></i> Edit Service
            </button>
            <a href="admin_services.php?action=deactivate&id=<?= e($service['id']) ?>" class="btn btn-danger" onclick="return confirm('Deactivate this service?');">
              <i class="bi bi-slash-circle"></i> Deactivate
            </a>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- EDIT MODAL -->
  <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content position-relative">
        <div class="modal-header">
          <h5 class="modal-title" id="editServiceLabel"><i class="bi bi-pencil-square me-2"></i>Edit Service #<?= e($service['id']) ?></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="post" action="admin_service_view.php?id=<?= e($service['id']) ?>">
          <input type="hidden" name="update_service" value="1">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= e($service['title']) ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Type</label>
                <input type="text" name="type" class="form-control" value="<?= e($service['type']) ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label">Price (USD)</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= e($service['price']) ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">City From</label>
                <input type="text" name="city_from" class="form-control" value="<?= e($service['city_from']) ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">City To</label>
                <input type="text" name="city_to" class="form-control" value="<?= e($service['city_to']) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Available From</label>
                <input type="date" name="available_from" class="form-control" value="<?= e($service['available_from']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Available To</label>
                <input type="date" name="available_to" class="form-control" value="<?= e($service['available_to']) ?>">
              </div>

              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="active"   <?= $service['status']==='active'   ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $service['status']==='inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control" placeholder="Write a short description..."><?= e($service['description']) ?></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="footer-glass text-center">
    <div class="container-fluid">
      <p class="mb-2">&copy; <?= date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</p>
      <p class="mb-0">
        <a href="#privacy" class="text-decoration-none me-3">Privacy Policy</a>
        <a href="#terms" class="text-decoration-none me-3">Terms of Service</a>
        <a href="#contact" class="text-decoration-none">Contact Us</a>
      </p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>

<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
require_once '../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/* ---------- Auth guard ---------- */
if (!is_logged_in() || !is_admin()) {
  header('Location: login.php'); exit;
}

/* ---------- Validate ID ---------- */
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  http_response_code(400);
  echo "Invalid user ID."; exit;
}
$user_id = (int) $_GET['id'];

/* ---------- CSRF token ---------- */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ---------- Helpers ---------- */
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function badgeClass($status, $map){ $s=strtolower((string)$status); return $map[$s] ?? 'bg-secondary'; }
function roleBadge($role){
  $r=strtolower($role);
  if ($r==='admin')    return 'bg-info';
  if ($r==='provider') return 'bg-success';
  return 'bg-secondary';
}

/* ---------- Handle UPDATE (Edit Modal) ---------- */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_user'])) {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['flash_error'] = "Security check failed. Please try again.";
    header("Location: admin_users.php?id=".$user_id); exit;
  }

  $name         = trim($_POST['name'] ?? '');
  $email        = trim($_POST['email'] ?? '');
  $phone        = trim($_POST['phone'] ?? '');
  $company_name = trim($_POST['company_name'] ?? '');
  $role         = strtolower(trim($_POST['role'] ?? 'client'));
  $status       = strtolower(trim($_POST['status'] ?? 'inactive'));
  $rating       = trim($_POST['rating'] ?? '');

  $errors = [];
  if ($name==='')  $errors[] = "Name is required.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
  if (!in_array($role, ['client','provider','admin'], true))   $role='client';
  if (!in_array($status, ['active','inactive'], true))         $status='inactive';

  $rating_val = null;
  if ($rating!=='') {
    if (!is_numeric($rating) || $rating<0 || $rating>5) $errors[] = "Rating must be between 0 and 5.";
    else $rating_val = round((float)$rating, 1);
  }

  if (count($errors)===0) {
    // Unique email (except this user)
    $chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email=? AND id<>? LIMIT 1");
    mysqli_stmt_bind_param($chk,'si',$email,$user_id);
    mysqli_stmt_execute($chk);
    $dup = mysqli_stmt_get_result($chk);
    if ($dup && mysqli_num_rows($dup)>0) {
      $_SESSION['flash_error'] = "Email is already in use by another user.";
      header("Location: admin_users.php?id=".$user_id); exit;
    }

    $upd = mysqli_prepare($conn, "
      UPDATE users
         SET name=?, email=?, phone=?, company_name=?, role=?, status=?, rating=?
       WHERE id=? LIMIT 1
    ");
    if ($rating_val===null) $rating_val = 0.0; // keep consistent with schema default
    mysqli_stmt_bind_param($upd,'ssssssdi',
      $name,$email,$phone,$company_name,$role,$status,$rating_val,$user_id
    );
    if (mysqli_stmt_execute($upd)) {
      $_SESSION['flash_success'] = "User updated successfully.";
    } else {
      $_SESSION['flash_error'] = "Update failed: ".mysqli_error($conn);
    }
    header("Location: admin_users.php?id=".$user_id); exit;
  } else {
    $_SESSION['flash_error'] = implode(' ', $errors);
    header("Location: admin_users.php?id=".$user_id); exit;
  }
}

/* ---------- Fetch user ---------- */
$u_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=? LIMIT 1");
mysqli_stmt_bind_param($u_stmt, 'i', $user_id);
mysqli_stmt_execute($u_stmt);
$u_res = mysqli_stmt_get_result($u_stmt);
if (!$u_res || mysqli_num_rows($u_res)===0) { http_response_code(404); echo "User not found."; exit; }
$user = mysqli_fetch_assoc($u_res);

/* ---------- Metrics depending on role ---------- */
$role = strtolower($user['role']);
$created_at = $user['created_at'];

$stats = [
  'services_count' => 0,
  'bookings_total' => 0,
  'bookings_completed' => 0,
  'revenue' => 0.0,
  'reviews_count' => 0,
  'avg_rating' => null,
  'spent' => 0.0,
];

if ($role==='provider') {
  $q = mysqli_prepare($conn,"SELECT COUNT(*) c FROM services WHERE provider_id=?");
  mysqli_stmt_bind_param($q,'i',$user_id); mysqli_stmt_execute($q);
  $r = mysqli_stmt_get_result($q); $row = mysqli_fetch_assoc($r); $stats['services_count'] = (int)($row['c']??0);

  $q = mysqli_prepare($conn,"
    SELECT COUNT(*) total,
           SUM(CASE WHEN b.status='completed' THEN 1 ELSE 0 END) completed,
           COALESCE(SUM(CASE WHEN b.status='completed' THEN s.price ELSE 0 END),0) revenue
      FROM bookings b
      JOIN services s ON b.service_id=s.id
     WHERE b.provider_id=?
  "); mysqli_stmt_bind_param($q,'i',$user_id); mysqli_stmt_execute($q);
  $r = mysqli_stmt_get_result($q); $row = mysqli_fetch_assoc($r);
  $stats['bookings_total']     = (int)($row['total']??0);
  $stats['bookings_completed'] = (int)($row['completed']??0);
  $stats['revenue']            = (float)($row['revenue']??0);

  $q = mysqli_prepare($conn,"SELECT COUNT(*) cnt, AVG(rating) avg_rating FROM reviews WHERE provider_id=?");
  mysqli_stmt_bind_param($q,'i',$user_id); mysqli_stmt_execute($q);
  $r = mysqli_stmt_get_result($q); $row = mysqli_fetch_assoc($r);
  $stats['reviews_count'] = (int)($row['cnt']??0);
  $stats['avg_rating']    = $row['avg_rating']!==null ? round((float)$row['avg_rating'],2) : null;
}
elseif ($role==='client') {
  $q = mysqli_prepare($conn,"
    SELECT COUNT(*) total,
           SUM(CASE WHEN b.status='completed' THEN 1 ELSE 0 END) completed,
           COALESCE(SUM(CASE WHEN b.status='completed' THEN s.price ELSE 0 END),0) spent
      FROM bookings b
      JOIN services s ON b.service_id=s.id
     WHERE b.client_id=?
  "); mysqli_stmt_bind_param($q,'i',$user_id); mysqli_stmt_execute($q);
  $r = mysqli_stmt_get_result($q); $row = mysqli_fetch_assoc($r);
  $stats['bookings_total']     = (int)($row['total']??0);
  $stats['bookings_completed'] = (int)($row['completed']??0);
  $stats['spent']              = (float)($row['spent']??0);

  $q = mysqli_prepare($conn,"SELECT COUNT(*) cnt FROM reviews WHERE reviewer_id=?");
  mysqli_stmt_bind_param($q,'i',$user_id); mysqli_stmt_execute($q);
  $r = mysqli_stmt_get_result($q); $row = mysqli_fetch_assoc($r);
  $stats['reviews_count'] = (int)($row['cnt']??0);
}

/* ---------- Badges ---------- */
$statusBadge = badgeClass($user['status'], ['active'=>'bg-success','inactive'=>'bg-warning text-dark']);
$roleBadge   = roleBadge($user['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>User #<?= e($user['id']) ?> – Admin View | Logistics & Moving</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
          <li class="nav-item"><a class="nav-link active" href="admin_users.php">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_users.php?role=provider">Providers</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_services.php">Services</a></li>
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
            <h1 class="fw-black mb-2 gradient-text">User #<?= e($user['id']) ?> — <?= e($user['name']) ?></h1>
            <p class="mb-3">Joined: <?= e($created_at) ?></p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
              <span class="badge <?= $roleBadge ?>"><?= e(ucfirst($user['role'])) ?></span>
              <span class="badge <?= $statusBadge ?>"><?= e(ucfirst($user['status'])) ?></span>
              <?php if ($role==='provider'): ?>
                <span class="badge bg-info"><i class="bi bi-star-fill me-1"></i><?= number_format((float)$user['rating'], 1) ?> / 5</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FLASH -->
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

        <!-- Overview -->
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <h3 class="gradient-text mb-0">Profile Overview</h3>
            <div class="d-flex gap-2">
              <a href="admin_users.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal">
                <i class="bi bi-pencil-square"></i> Edit
              </button>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-md-6 me-2 ">
              <div class="kpi-card text-left h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-person"></i></div>
                <h4 class="mb-2">User Info</h4>
                <ul style="list-style:none;padding:0;margin:0;">
                  <li class="mb-2"><i class="bi bi-envelope me-2"></i><strong>Email:</strong> <?= e($user['email']) ?></li>
                  <li class="mb-2"><i class="bi bi-telephone me-2"></i><strong>Phone:</strong> <?= e($user['phone'] ?: '—') ?></li>
                  <li class="mb-2"><i class="bi bi-briefcase me-2"></i><strong>Company:</strong> <?= e($user['company_name'] ?: '—') ?></li>
                  <li class="mb-2"><i class="bi bi-person-badge me-2"></i><strong>Role:</strong> <span class="badge <?= $roleBadge ?>"><?= e(ucfirst($user['role'])) ?></span></li>
                  <li class="mb-2"><i class="bi bi-shield-check me-2"></i><strong>Status:</strong> <span class="badge <?= $statusBadge ?>"><?= e(ucfirst($user['status'])) ?></span></li>
                </ul>
              </div>
            </div>

            <div class="col-md-5">
              <div class="kpi-card text-left h-100">
                <div class="kpi-icon mb-3"><i class="bi bi-graph-up"></i></div>
                <h4 class="mb-2">Activity</h4>
                <ul style="list-style:none;padding:0;margin:0;">
                  <?php if ($role==='provider'): ?>
                    <li class="mb-2"><i class="bi bi-box-seam me-2"></i><strong>Services:</strong> <?= (int)$stats['services_count'] ?></li>
                    <li class="mb-2"><i class="bi bi-calendar-check me-2"></i><strong>Bookings:</strong> <?= (int)$stats['bookings_total'] ?> (<?= (int)$stats['bookings_completed'] ?> completed)</li>
                    <li class="mb-2"><i class="bi bi-cash-coin me-2"></i><strong>Revenue:</strong> $<?= number_format($stats['revenue'], 2) ?></li>
                    <li class="mb-2"><i class="bi bi-star-fill me-2"></i><strong>Reviews:</strong> <?= (int)$stats['reviews_count'] ?><?= $stats['avg_rating']!==null ? ' • Avg '.$stats['avg_rating'].'/5' : '' ?></li>
                  <?php elseif ($role==='client'): ?>
                    <li class="mb-2"><i class="bi bi-calendar-check me-2"></i><strong>Bookings:</strong> <?= (int)$stats['bookings_total'] ?> (<?= (int)$stats['bookings_completed'] ?> completed)</li>
                    <li class="mb-2"><i class="bi bi-receipt me-2"></i><strong>Total Spent:</strong> $<?= number_format($stats['spent'], 2) ?></li>
                    <li class="mb-2"><i class="bi bi-star-fill me-2"></i><strong>Reviews Posted:</strong> <?= (int)$stats['reviews_count'] ?></li>
                  <?php else: ?>
                    <li class="mb-2"><i class="bi bi-shield-lock me-2"></i>Admin account</li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between">
          <a href="admin_users.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Back to Users</a>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal">
            <i class="bi bi-pencil-square"></i> Edit User
          </button>
        </div>

      </div>
    </div>
  </section>

  <!-- EDIT USER MODAL -->
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content position-relative">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserLabel"><i class="bi bi-pencil-square me-2"></i>Edit User #<?= e($user['id']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="post" action="admin_users.php?id=<?= e($user['id']) ?>">
          <input type="hidden" name="update_user" value="1">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Company</label>
                <input type="text" name="company_name" class="form-control" value="<?= e($user['company_name']) ?>">
              </div>

              <div class="col-md-4">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" id="roleSelect">
                  <option value="client"   <?= $role==='client'   ? 'selected' : '' ?>>Client</option>
                  <option value="provider" <?= $role==='provider' ? 'selected' : '' ?>>Provider</option>
                  <option value="admin"    <?= $role==='admin'    ? 'selected' : '' ?>>Admin</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option value="active"   <?= $user['status']==='active'   ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $user['status']==='inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Rating (0–5)</label>
                <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" id="ratingInput" value="<?= e(number_format((float)$user['rating'],1)) ?>">
                <div class="form-text">Used mainly for providers.</div>
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
  <script>
    // Disable rating unless role is provider (UX nicety)
    (function() {
      const role = document.getElementById('roleSelect');
      const rating = document.getElementById('ratingInput');
      function toggleRating() {
        if (!role || !rating) return;
        const isProvider = role.value === 'provider';
        rating.disabled = !isProvider;
        rating.closest('.col-md-4').style.opacity = isProvider ? '1' : '0.7';
      }
      if (role) {
        role.addEventListener('change', toggleRating);
        toggleRating();
      }
    })();
  </script>
</body>
</html>

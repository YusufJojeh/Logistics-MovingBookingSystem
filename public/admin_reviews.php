<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

// Only admins
if (!is_admin()) { header('Location: /login.php'); exit; }

// CSRF token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$CSRF = $_SESSION['csrf_token'];

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function flash($key){ if(!empty($_SESSION[$key])) { $m=$_SESSION[$key]; unset($_SESSION[$key]); return $m; } return null; }

// Handle POST actions: add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['flash_error'] = 'Security check failed. Please try again.';
    header('Location: admin_reviews.php'); exit;
  }

  $action = $_POST['action'] ?? '';

  if ($action === 'delete') {
    $id = (int)($_POST['review_id'] ?? 0);
    if ($id > 0) {
      $stmt = mysqli_prepare($conn, 'DELETE FROM reviews WHERE id=?');
      mysqli_stmt_bind_param($stmt, 'i', $id);
      if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = "Review #$id deleted.";
      } else {
        $_SESSION['flash_error'] = 'Delete failed: '.mysqli_error($conn);
      }
      mysqli_stmt_close($stmt);
    }
    header('Location: admin_reviews.php'); exit;
  }

  if ($action === 'edit') {
    $id = (int)($_POST['review_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($id && $rating>=1 && $rating<=5 && $comment !== '') {
      $stmt = mysqli_prepare($conn, 'UPDATE reviews SET rating=?, comment=? WHERE id=?');
      mysqli_stmt_bind_param($stmt, 'isi', $rating, $comment, $id);
      if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = "Review #$id updated.";
      } else {
        $_SESSION['flash_error'] = 'Update failed: '.mysqli_error($conn);
      }
      mysqli_stmt_close($stmt);
    } else {
      $_SESSION['flash_error'] = 'Please provide a valid rating and comment.';
    }
    header('Location: admin_reviews.php'); exit;
  }

  if ($action === 'add') {
    $booking_id  = (int)($_POST['booking_id'] ?? 0);
    $client_id   = (int)($_POST['client_id'] ?? 0);
    $provider_id = (int)($_POST['provider_id'] ?? 0);
    $rating      = (int)($_POST['rating'] ?? 0);
    $comment     = trim($_POST['comment'] ?? '');

    $errors = [];
    if (!$booking_id) $errors[] = 'Booking is required.';
    if (!$client_id) $errors[] = 'Client is required.';
    if (!$provider_id) $errors[] = 'Provider is required.';
    if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be 1–5.';
    if ($comment === '') $errors[] = 'Comment is required.';

    if (!$errors) {
      // Validate booking matches client/provider and is completed
      $chk = mysqli_prepare($conn, "SELECT id FROM bookings WHERE id=? AND client_id=? AND provider_id=? AND status='completed' LIMIT 1");
      mysqli_stmt_bind_param($chk, 'iii', $booking_id, $client_id, $provider_id);
      mysqli_stmt_execute($chk);
      $res = mysqli_stmt_get_result($chk);
      if (!$res || mysqli_num_rows($res)===0) {
        $errors[] = 'Booking doesn’t match the selected client/provider or is not completed.';
      }
      mysqli_stmt_close($chk);
    }

    if (!$errors) {
      $stmt = mysqli_prepare($conn, 'INSERT INTO reviews (booking_id, reviewer_id, provider_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
      mysqli_stmt_bind_param($stmt, 'iiiis', $booking_id, $client_id, $provider_id, $rating, $comment);
      if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_success'] = 'Review added.';
      } else {
        $_SESSION['flash_error'] = 'Insert failed: '.mysqli_error($conn);
      }
      mysqli_stmt_close($stmt);
    } else {
      $_SESSION['flash_error'] = implode(' ', $errors);
    }
    header('Location: admin_reviews.php'); exit;
  }

  header('Location: admin_reviews.php'); exit;
}

// Stats
[$total_reviews]     = mysqli_fetch_row(mysqli_query($conn, 'SELECT COUNT(*) FROM reviews'));
[$avg_rating_raw]    = mysqli_fetch_row(mysqli_query($conn, 'SELECT AVG(rating) FROM reviews'));
[$five_star_reviews] = mysqli_fetch_row(mysqli_query($conn, 'SELECT COUNT(*) FROM reviews WHERE rating=5'));
[$recent_reviews]    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reviews WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"));
$avg_rating = $avg_rating_raw ? (float)$avg_rating_raw : 0;
$five_pct = ($total_reviews>0) ? round(($five_star_reviews/$total_reviews)*100) : 0;

// Filters
$search      = trim($_GET['search'] ?? '');
$provider_id = (int)($_GET['provider_id'] ?? 0);
$rating_eq   = (int)($_GET['rating'] ?? 0);

$sql = "
  SELECT r.*, c.name AS client_name, p.name AS provider_name
    FROM reviews r
    JOIN users c ON r.reviewer_id = c.id
    JOIN users p ON r.provider_id = p.id
   WHERE 1
";
$conds  = [];
$params = [];
$types  = '';

if ($search !== '') {
  $conds[] = "(r.comment LIKE CONCAT('%', ?, '%') OR c.name LIKE CONCAT('%', ?, '%') OR p.name LIKE CONCAT('%', ?, '%'))";
  $params[] = $search; $params[] = $search; $params[] = $search; $types .= 'sss';
}
if ($provider_id) {
  $conds[] = 'r.provider_id = ?';
  $params[] = $provider_id; $types .= 'i';
}
if ($rating_eq >= 1 && $rating_eq <= 5) {
  $conds[] = 'r.rating = ?';
  $params[] = $rating_eq; $types .= 'i';
}
if ($conds) $sql .= ' AND ' . implode(' AND ', $conds);
$sql .= ' ORDER BY r.created_at DESC';

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  $export_stmt = mysqli_prepare($conn, $sql);
  if ($params) { mysqli_stmt_bind_param($export_stmt, $types, ...$params); }
  mysqli_stmt_execute($export_stmt);
  $export_rs = mysqli_stmt_get_result($export_stmt);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=reviews_' . date('Ymd_His') . '.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID','Client','Provider','Rating','Comment','Created']);
  while ($row = mysqli_fetch_assoc($export_rs)) {
    fputcsv($out, [ (int)$row['id'], (string)$row['client_name'], (string)$row['provider_name'], (int)$row['rating'], (string)$row['comment'], (string)$row['created_at'] ]);
  }
  fclose($out);
  mysqli_stmt_close($export_stmt);
  exit;
}

$stmt = mysqli_prepare($conn, $sql);
if ($params) { mysqli_stmt_bind_param($stmt, $types, ...$params); }
mysqli_stmt_execute($stmt);
$reviews = mysqli_stmt_get_result($stmt);

// Dropdown data
$clients_rs   = mysqli_query($conn, "SELECT id, name FROM users WHERE role='client'  AND status IN ('active','inactive') ORDER BY name");
$providers_rs = mysqli_query($conn, "SELECT id, name FROM users WHERE role='provider' AND status IN ('active','inactive') ORDER BY name");
$bookings_rs  = mysqli_query($conn, "
  SELECT b.id, b.booking_date, c.name AS client_name, p.name AS provider_name
    FROM bookings b
    JOIN users c ON b.client_id=c.id
    JOIN users p ON b.provider_id=p.id
   WHERE b.status='completed'
   ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Review Management - Logistics &amp; Moving Booking System</title>
  <link rel="icon" href="../assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>.overflow-visible{overflow:visible!important}.z-dropdown .dropdown-menu{z-index:2000}</style>
</head>
<body class="modern-bg">
  <!-- NAV -->
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
          <li class="nav-item"><a class="nav-link" href="admin_services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link active" href="admin_reviews.php">Reviews</a></li>
          <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 30vh;">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="glass-card">
            <h1 class="display-4 fw-black mb-3 gradient-text">Review Management</h1>
            <p class="lead mb-0">Monitor and manage customer reviews and ratings</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FLASH -->
  <?php if ($s = flash('flash_success')): ?>
    <section class="container-fluid py-6"><div class="row justify-content-center"><div class="col-lg-10">
      <div class="alert alert-success"><?= e($s) ?></div>
    </div></div></section>
  <?php endif; ?>
  <?php if ($e = flash('flash_error')): ?>
    <section class="container-fluid py-6"><div class="row justify-content-center"><div class="col-lg-10">
      <div class="alert alert-danger"><?= e($e) ?></div>
    </div></div></section>
  <?php endif; ?>

  <!-- STATS -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Review Overview</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <div class="col-md-3"><div class="kpi-card text-center"><div class="kpi-icon"><i class="bi bi-star"></i></div><div class="kpi-number"><?= (int)$total_reviews ?></div><div class="kpi-label">Total Reviews</div></div></div>
          <div class="col-md-3"><div class="kpi-card text-center"><div class="kpi-icon"><i class="bi bi-star-fill"></i></div><div class="kpi-number"><?= number_format($avg_rating, 1) ?></div><div class="kpi-label">Average Rating</div></div></div>
          <div class="col-md-3"><div class="kpi-card text-center"><div class="kpi-icon"><i class="bi bi-star-fill"></i></div><div class="kpi-number"><?= (int)$five_star_reviews ?></div><div class="kpi-label">5-Star Reviews</div><div class="kpi-trend text-success"><i class="bi bi-arrow-up"></i> <?= $five_pct ?>%</div></div></div>
          <div class="col-md-3"><div class="kpi-card text-center"><div class="kpi-icon"><i class="bi bi-calendar-event"></i></div><div class="kpi-number"><?= (int)$recent_reviews ?></div><div class="kpi-label">Recent (30d)</div></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- LIST -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card overflow-visible">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="gradient-text mb-0">Review List</h2>
            <div class="d-flex gap-2">
              <a class="btn btn-outline-primary" href="admin_reviews.php?export=csv<?php
                $qs = [];
                if ($search!=='') $qs[] = 'search='.urlencode($search);
                if ($provider_id) $qs[] = 'provider_id='.$provider_id;
                if ($rating_eq) $qs[] = 'rating='.$rating_eq;
                echo $qs?('&'.implode('&',$qs)) : '';
              ?>"><i class="bi bi-download me-2"></i>Export</a>
              <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addReviewModal"><i class="bi bi-plus-circle me-2"></i>Add Review</button>
            </div>
          </div>

          <!-- Filters -->
          <form class="row g-3 mb-4" method="get">
            <div class="col-md-4">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control search-input" placeholder="Search by comment, client, or provider" value="<?= e($search) ?>">
              </div>
            </div>
            <div class="col-md-3">
              <select name="provider_id" class="form-select">
                <option value="">All Providers</option>
                <?php while($p = mysqli_fetch_assoc($providers_rs)): ?>
                  <option value="<?= (int)$p['id'] ?>" <?= ($provider_id===(int)$p['id'])?'selected':'' ?>><?= e($p['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-3">
              <select name="rating" class="form-select">
                <option value="">All Ratings</option>
                <?php for($i=5;$i>=1;$i--): ?>
                  <option value="<?= $i ?>" <?= ($rating_eq===$i)?'selected':'' ?>><?= $i ?> Star<?= $i>1?'s':'' ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-2"></i>Filter</button>
            </div>
          </form>

          <!-- Table -->
          <div class="table-container overflow-visible">
            <div class="table-responsive overflow-visible">
              <table class="table table-dark align-middle">
                <thead>
                  <tr>
                    <th>Actions</th>
                    <th data-sort>ID</th>
                    <th data-sort>Client</th>
                    <th data-sort>Provider</th>
                    <th data-sort>Rating</th>
                    <th data-sort>Comment</th>
                    <th data-sort>Created</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($reviews && mysqli_num_rows($reviews)>0): ?>
                    <?php while($r = mysqli_fetch_assoc($reviews)): ?>
                      <tr id="row-<?= (int)$r['id'] ?>">
                        <td class="actions-cell z-dropdown">
                          <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-offset="0,8">
                              <i class="bi bi-gear"></i> Actions
                            </button>
                            <ul class="dropdown-menu">
                              <li>
                                <a class="dropdown-item" href="#" onclick='viewReviewDetails(
                                  <?= (int)$r["id"] ?>,
                                  <?= json_encode((string)$r["client_name"]) ?>,
                                  <?= json_encode((string)$r["provider_name"]) ?>,
                                  <?= (int)$r["rating"] ?>,
                                  <?= json_encode((string)$r["comment"]) ?>,
                                  <?= json_encode((string)$r["created_at"]) ?>
                                )'>
                                  <i class="bi bi-eye me-2"></i>View Details
                                </a>
                              </li>
                              <li>
                                <a class="dropdown-item" href="#" onclick='editReview(
                                  <?= (int)$r["id"] ?>,
                                  <?= (int)$r["rating"] ?>,
                                  <?= json_encode((string)$r["comment"]) ?>
                                )'>
                                  <i class="bi bi-pencil me-2"></i>Edit Review
                                </a>
                              </li>
                              <li><hr class="dropdown-divider"></li>
                              <li>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this review? This action cannot be undone.');">
                                  <input type="hidden" name="csrf_token" value="<?= e($CSRF) ?>">
                                  <input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
                                  <button type="submit" name="action" value="delete" class="dropdown-item text-danger">
                                    <i class="bi bi-trash me-2"></i>Delete Review
                                  </button>
                                </form>
                              </li>
                            </ul>
                          </div>
                        </td>
                        <td class="number-cell">#<?= (int)$r['id'] ?></td>
                        <td><div class="d-flex align-items-center"><div class="user-avatar me-2"><i class="bi bi-person"></i></div><span class="fw-semibold"><?= e($r['client_name']) ?></span></div></td>
                        <td><div class="d-flex align-items-center"><div class="user-avatar me-2"><i class="bi bi-building"></i></div><span class="fw-semibold"><?= e($r['provider_name']) ?></span></div></td>
                        <td><div class="text-warning"><?php for($i=1;$i<=5;$i++): ?><i class="bi bi-star<?= $i <= (int)$r['rating'] ? '-fill' : '' ?>"></i><?php endfor; ?></div></td>
                        <td><div class="text-truncate" style="max-width: 320px;" title="<?= e($r['comment']) ?>"><?= e($r['comment']) ?></div></td>
                        <td class="date-cell"><?= e(date('M d, Y', strtotime($r['created_at']))) ?></td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr><td colspan="7"><div class="table-empty"><i class="bi bi-inbox"></i><div>No reviews found.</div></div></td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">Showing <?= $reviews ? (int)mysqli_num_rows($reviews) : 0 ?> result(s)</div>
            <nav aria-label="Review pagination">
              <ul class="pagination mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
              </ul>
            </nav>
          </div>

        </div>
      </div>
    </div>
  </section>

  <!-- ADD REVIEW MODAL -->
  <div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= e($CSRF) ?>">
          <input type="hidden" name="action" value="add">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="addReviewModalLabel"><i class="bi bi-plus-circle me-2"></i>Add New Review</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Booking (completed) *</label>
                <select class="form-select" name="booking_id" id="add_booking" required>
                  <option value="">Select Booking</option>
                  <?php while($b = mysqli_fetch_assoc($bookings_rs)): ?>
                    <option value="<?= (int)$b['id'] ?>" data-client_name="<?= e($b['client_name']) ?>" data-provider_name="<?= e($b['provider_name']) ?>">#<?= (int)$b['id'] ?> — <?= e($b['client_name']) ?> → <?= e($b['provider_name']) ?> (<?= e($b['booking_date']) ?>)</option>
                  <?php endwhile; ?>
                </select>
                <div class="form-text">Must be a completed booking.</div>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">Client *</label>
                <select class="form-select" name="client_id" id="add_client" required>
                  <option value="">Select Client</option>
                  <?php mysqli_data_seek($clients_rs, 0); while($c = mysqli_fetch_assoc($clients_rs)): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">Provider *</label>
                <select class="form-select" name="provider_id" id="add_provider" required>
                  <option value="">Select Provider</option>
                  <?php mysqli_data_seek($providers_rs, 0); while($p2 = mysqli_fetch_assoc($providers_rs)): ?>
                    <option value="<?= (int)$p2['id'] ?>"><?= e($p2['name']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-bold">Rating *</label>
                <select class="form-select" name="rating" required>
                  <option value="">Select Rating</option>
                  <option value="1">1 Star - Poor</option>
                  <option value="2">2 Stars - Fair</option>
                  <option value="3">3 Stars - Good</option>
                  <option value="4">4 Stars - Very Good</option>
                  <option value="5">5 Stars - Excellent</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Comment *</label>
                <textarea class="form-control" name="comment" rows="4" required placeholder="Enter review comment..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Review</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- VIEW REVIEW MODAL -->
  <div class="modal fade" id="viewReviewModal" tabindex="-1" aria-labelledby="viewReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title gradient-text" id="viewReviewModalLabel"><i class="bi bi-star me-2"></i>Review Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-3"><label class="form-label fw-bold">Review ID</label><input type="text" class="form-control" id="view_review_id" readonly></div>
            <div class="col-md-3"><label class="form-label fw-bold">Rating</label><input type="text" class="form-control" id="view_review_rating" readonly></div>
            <div class="col-md-6"><label class="form-label fw-bold">Created</label><input type="text" class="form-control" id="view_review_created_at" readonly></div>
            <div class="col-md-6"><label class="form-label fw-bold">Client</label><input type="text" class="form-control" id="view_review_client" readonly></div>
            <div class="col-md-6"><label class="form-label fw-bold">Provider</label><input type="text" class="form-control" id="view_review_provider" readonly></div>
            <div class="col-12"><label class="form-label fw-bold">Comment</label><textarea class="form-control" id="view_review_comment" rows="4" readonly></textarea></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
      </div>
    </div>
  </div>

  <!-- EDIT REVIEW MODAL -->
  <div class="modal fade" id="editReviewModal" tabindex="-1" aria-labelledby="editReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= e($CSRF) ?>">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="review_id" id="edit_review_id">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="editReviewModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Review</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-bold">Rating *</label>
                <select class="form-select" name="rating" id="edit_rating" required>
                  <option value="1">1 Star - Poor</option>
                  <option value="2">2 Stars - Fair</option>
                  <option value="3">3 Stars - Good</option>
                  <option value="4">4 Stars - Very Good</option>
                  <option value="5">5 Stars - Excellent</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-bold">Comment *</label>
                <textarea class="form-control" name="comment" id="edit_comment" rows="4" required></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Update Review</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="footer-glass text-center py-4 mt-5">
    <small>&copy; <?= date('Y'); ?> Logistics &amp; Moving Booking System. All rights reserved.</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
  <script>
    // Booking selection -> auto-fill client/provider selects (best-effort)
    (function(){
      const booking = document.getElementById('add_booking');
      const client  = document.getElementById('add_client');
      const prov    = document.getElementById('add_provider');
      if (!booking || !client || !prov) return;
      booking.addEventListener('change', function(){
        const opt = booking.options[booking.selectedIndex];
        if (!opt) return;
        const cn = opt.getAttribute('data-client_name');
        const pn = opt.getAttribute('data-provider_name');
        for (const o of client.options)  { if (o.text === cn) { client.value = o.value; break; } }
        for (const o of prov.options)    { if (o.text === pn) { prov.value = o.value; break; } }
      });
    })();

    // Detail modal
    function viewReviewDetails(id, client_name, provider_name, rating, comment, created_at) {
      document.getElementById('view_review_id').value = '#' + id;
      document.getElementById('view_review_client').value = client_name;
      document.getElementById('view_review_provider').value = provider_name;
      document.getElementById('view_review_rating').value = rating + ' Star' + (rating>1?'s':'');
      document.getElementById('view_review_comment').value = comment || 'No comment provided';
      try { document.getElementById('view_review_created_at').value = new Date(created_at).toLocaleString(); }
      catch(e){ document.getElementById('view_review_created_at').value = created_at; }
      new bootstrap.Modal(document.getElementById('viewReviewModal')).show();
    }

    // Edit modal
    function editReview(id, rating, comment){
      document.getElementById('edit_review_id').value = id;
      document.getElementById('edit_rating').value = rating;
      document.getElementById('edit_comment').value = comment;
      new bootstrap.Modal(document.getElementById('editReviewModal')).show();
    }

    // Ensure dropdowns aren't clipped
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(el){
      new bootstrap.Dropdown(el, { boundary: 'viewport', popperConfig: { strategy: 'fixed' } });
    });
  </script>
</body>
</html>



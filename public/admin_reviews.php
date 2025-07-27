<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['review_id'] ?? 0);
    if ($id) {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $rating = intval($_POST['rating']);
            $comment = trim($_POST['comment']);
            
            if ($rating >= 1 && $rating <= 5 && $comment) {
                $stmt = mysqli_prepare($conn, "UPDATE reviews SET rating=?, comment=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'isi', $rating, $comment, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'add') {
        $client_id = intval($_POST['client_id']);
        $provider_id = intval($_POST['provider_id']);
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        
        if ($client_id && $provider_id && $rating >= 1 && $rating <= 5 && $comment) {
            $stmt = mysqli_prepare($conn, "INSERT INTO reviews (reviewer_id, provider_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, 'iiis', $client_id, $provider_id, $rating, $comment);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin_reviews.php');
    exit;
}

// Get review statistics
$total_reviews = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reviews"))[0];
$avg_rating = mysqli_fetch_row(mysqli_query($conn, "SELECT AVG(rating) FROM reviews"))[0];
$five_star_reviews = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reviews WHERE rating = 5"))[0];
$recent_reviews = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reviews WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))[0];

// Search/filter
$where = "WHERE 1";
$search = trim($_GET['search'] ?? '');
$provider_id = intval($_GET['provider_id'] ?? 0);
$rating = intval($_GET['rating'] ?? 0);
if ($search) {
    $where .= " AND (r.comment LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR c.name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR p.name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
if ($provider_id) {
    $where .= " AND r.provider_id = $provider_id";
}
if ($rating && $rating >= 1 && $rating <= 5) {
    $where .= " AND r.rating = $rating";
}
$reviews = mysqli_query($conn, "SELECT r.*, c.name AS client_name, p.name AS provider_name FROM reviews r JOIN users c ON r.reviewer_id = c.id JOIN users p ON r.provider_id = p.id $where ORDER BY r.created_at DESC");

// Get data for dropdowns
$clients = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'client' AND status = 'active' ORDER BY name");
$providers = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'provider' AND status = 'active' ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Review Management - Logistics & Moving Booking System</title>
  <link rel="icon" href="../assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
  <!-- Professional Navigation -->
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
          <li class="nav-item"><a class="nav-link" href="admin_providers.php">Providers</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link active" href="admin_reviews.php">Reviews</a></li>
          <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
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

  <!-- Statistics Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Review Overview</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-star"></i>
              </div>
              <div class="kpi-number"><?= $total_reviews ?></div>
              <div class="kpi-label">Total Reviews</div>
              <div class="kpi-trend text-success">
                <i class="bi bi-arrow-up"></i> All Time
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-star-fill"></i>
              </div>
              <div class="kpi-number"><?= number_format($avg_rating, 1) ?></div>
              <div class="kpi-label">Average Rating</div>
              <div class="kpi-trend text-warning">
                <i class="bi bi-star"></i> Out of 5
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-star-fill"></i>
              </div>
              <div class="kpi-number"><?= $five_star_reviews ?></div>
              <div class="kpi-label">5-Star Reviews</div>
              <div class="kpi-trend text-success">
                <i class="bi bi-arrow-up"></i> <?= round(($five_star_reviews/$total_reviews)*100) ?>%
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-calendar-event"></i>
              </div>
              <div class="kpi-number"><?= $recent_reviews ?></div>
              <div class="kpi-label">Recent Reviews</div>
              <div class="kpi-trend text-info">
                <i class="bi bi-arrow-up"></i> Last 30 Days
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Review Management Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="gradient-text mb-0">Review List</h2>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" onclick="exportReviews()">
                <i class="bi bi-download me-2"></i>Export
              </button>
              <button class="btn btn-primary" onclick="showAddReviewModal()">
                <i class="bi bi-plus-circle me-2"></i>Add Review
              </button>
            </div>
          </div>

          <!-- Search and Filter Form -->
          <form class="row g-3 mb-4" method="get">
            <div class="col-md-4">
              <div class="input-group">
                <span class="input-group-text">
                  <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control search-input" placeholder="Search by comment, client, or provider" value="<?= htmlspecialchars($search) ?>">
              </div>
            </div>
            <div class="col-md-3">
              <select name="provider_id" class="form-select">
                <option value="">All Providers</option>
                <?php $providers = mysqli_query($conn, "SELECT id, name FROM users WHERE role='provider' ORDER BY name"); while($p = mysqli_fetch_assoc($providers)): ?>
                  <option value="<?= $p['id'] ?>" <?= $provider_id==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-3">
              <select name="rating" class="form-select">
                <option value="">All Ratings</option>
                <option value="5" <?= $rating === 5 ? 'selected' : '' ?>>5 Stars</option>
                <option value="4" <?= $rating === 4 ? 'selected' : '' ?>>4 Stars</option>
                <option value="3" <?= $rating === 3 ? 'selected' : '' ?>>3 Stars</option>
                <option value="2" <?= $rating === 2 ? 'selected' : '' ?>>2 Stars</option>
                <option value="1" <?= $rating === 1 ? 'selected' : '' ?>>1 Star</option>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel me-2"></i>Filter
              </button>
            </div>
          </form>

          <!-- Enhanced Table -->
          <div class="table-container">
            <div class="table-responsive">
                              <table class="table table-dark align-middle">
                <thead>
                  <tr>
                    <th data-sort>ID</th>
                    <th data-sort>Client</th>
                    <th data-sort>Provider</th>
                    <th data-sort>Rating</th>
                    <th data-sort>Comment</th>
                    <th data-sort>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($r = mysqli_fetch_assoc($reviews)): ?>
                    <tr>
                      <td class="number-cell">#<?= $r['id'] ?></td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-2">
                            <i class="bi bi-person"></i>
                          </div>
                          <span class="fw-semibold"><?= htmlspecialchars($r['client_name']) ?></span>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-2">
                            <i class="bi bi-building"></i>
                          </div>
                          <span class="fw-semibold"><?= htmlspecialchars($r['provider_name']) ?></span>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="me-2">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                              <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?> text-warning"></i>
                            <?php endfor; ?>
                          </div>
                          <span class="badge bg-warning">
                            <?= $r['rating'] ?>/5
                          </span>
                        </div>
                      </td>
                      <td>
                        <div class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($r['comment']) ?>">
                          <?= htmlspecialchars($r['comment']) ?>
                        </div>
                      </td>
                      <td class="date-cell"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                      <td class="actions-cell">
                        <div class="btn-group">
                          <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Actions
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="viewReviewDetails(<?= $r['id'] ?>)">
                              <i class="bi bi-eye me-2"></i>View Details
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="viewReviewDetails(<?= $r['id'] ?>, '<?= htmlspecialchars($r['client_name']) ?>', '<?= htmlspecialchars($r['provider_name']) ?>', <?= $r['rating'] ?>, '<?= htmlspecialchars($r['comment']) ?>', '<?= $r['created_at'] ?>')">
                              <i class="bi bi-eye me-2"></i>View Details
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="editReview(<?= $r['id'] ?>, <?= $r['rating'] ?>, '<?= htmlspecialchars($r['comment']) ?>')">
                              <i class="bi bi-pencil me-2"></i>Edit Review
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this review? This action cannot be undone.');">
                                <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="action" value="delete" class="dropdown-item text-danger">
                                  <i class="bi bi-trash me-2"></i>Delete Review
                                </button>
                              </form>
                            </li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Pagination Section -->
          <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
              Showing all reviews (<?= mysqli_num_rows($reviews) ?> results)
            </div>
            <nav aria-label="Review pagination">
              <ul class="pagination mb-0">
                <li class="page-item disabled">
                  <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item disabled">
                  <a class="page-link" href="#">Next</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Add Review Modal -->
  <div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="add">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="addReviewModalLabel">
              <i class="bi bi-plus-circle me-2"></i>Add New Review
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Client *</label>
                  <select class="form-select" name="client_id" required>
                    <option value="">Select Client</option>
                    <?php while($c = mysqli_fetch_assoc($clients)): ?>
                      <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Provider *</label>
                  <select class="form-select" name="provider_id" required>
                    <option value="">Select Provider</option>
                    <?php while($p = mysqli_fetch_assoc($providers)): ?>
                      <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
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
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Comment *</label>
              <textarea class="form-control" name="comment" rows="4" required placeholder="Enter review comment..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-circle me-2"></i>Add Review
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Review Details Modal -->
  <div class="modal fade" id="viewReviewModal" tabindex="-1" aria-labelledby="viewReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title gradient-text" id="viewReviewModalLabel">
            <i class="bi bi-star me-2"></i>Review Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Review ID</label>
                <input type="text" class="form-control" id="view_review_id" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Rating</label>
                <input type="text" class="form-control" id="view_review_rating" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Client</label>
                <input type="text" class="form-control" id="view_review_client" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Provider</label>
                <input type="text" class="form-control" id="view_review_provider" readonly>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Comment</label>
            <textarea class="form-control" id="view_review_comment" rows="4" readonly></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Created Date</label>
            <input type="text" class="form-control" id="view_review_created_at" readonly>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Review Modal -->
  <div class="modal fade" id="editReviewModal" tabindex="-1" aria-labelledby="editReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="review_id" id="edit_review_id">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="editReviewModalLabel">
              <i class="bi bi-pencil-square me-2"></i>Edit Review
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Rating *</label>
                  <select class="form-select" name="rating" id="edit_rating" required>
                    <option value="1">1 Star - Poor</option>
                    <option value="2">2 Stars - Fair</option>
                    <option value="3">3 Stars - Good</option>
                    <option value="4">4 Stars - Very Good</option>
                    <option value="5">5 Stars - Excellent</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Comment *</label>
              <textarea class="form-control" name="comment" id="edit_comment" rows="4" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-2"></i>Update Review
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer-glass text-center py-4 mt-5">
    <small>&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
  <script>
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    function exportReviews() {
      // Export functionality placeholder
      showToast('Export feature coming soon!', 'info');
    }

    function showAddReviewModal() {
      var modal = new bootstrap.Modal(document.getElementById('addReviewModal'));
      modal.show();
    }

    function viewReviewDetails(id, client_name, provider_name, rating, comment, created_at) {
      document.getElementById('view_review_id').value = id;
      document.getElementById('view_review_client').value = client_name;
      document.getElementById('view_review_provider').value = provider_name;
      document.getElementById('view_review_rating').value = rating + ' Stars';
      document.getElementById('view_review_comment').value = comment || 'No comment provided';
      document.getElementById('view_review_created_at').value = new Date(created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
      
      var modal = new bootstrap.Modal(document.getElementById('viewReviewModal'));
      modal.show();
    }

    function editReview(id, rating, comment) {
      document.getElementById('edit_review_id').value = id;
      document.getElementById('edit_rating').value = rating;
      document.getElementById('edit_comment').value = comment;
      
      var modal = new bootstrap.Modal(document.getElementById('editReviewModal'));
      modal.show();
    }

    function viewReviewDetails(reviewId) {
      // View review details functionality placeholder
      showToast('Review details feature coming soon!', 'info');
    }
  </script>
</body>
</html> 
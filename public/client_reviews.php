<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_client()) { header('Location: /login.php'); exit; }
$user = current_user();
$client_id = $user['id'];

// Handle add/edit review
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['booking_id'], $_POST['provider_id'], $_POST['rating'], $_POST['comment']) && !isset($_POST['review_id'])) {
        // Add new review
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
            $success = 'Review submitted successfully!';
        }
    } elseif (isset($_POST['review_id'], $_POST['rating'], $_POST['comment'])) {
        // Edit existing review
        $review_id = intval($_POST['review_id']);
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        
        if ($rating >= 1 && $rating <= 5 && $comment) {
            $stmt = mysqli_prepare($conn, "UPDATE reviews SET rating=?, comment=? WHERE id=? AND reviewer_id=?");
            mysqli_stmt_bind_param($stmt, 'isis', $rating, $comment, $review_id, $client_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $success = 'Review updated successfully!';
        } else {
            $error = 'Please provide valid rating and comment.';
        }
    }
}

// Get review statistics
$stats = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_reviews,
        AVG(rating) as avg_rating,
        COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
        COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
        COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
        COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
        COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
    FROM reviews 
    WHERE reviewer_id = $client_id
");

$stats_data = mysqli_fetch_assoc($stats);

// Fetch completed bookings not yet reviewed
$to_review = mysqli_query($conn, "SELECT b.id, s.title, u.name AS provider_name, b.provider_id FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.provider_id = u.id WHERE b.client_id = $client_id AND b.status = 'completed' AND b.id NOT IN (SELECT booking_id FROM reviews WHERE reviewer_id = $client_id)");

// Fetch own reviews
$reviews = mysqli_query($conn, "SELECT r.*, u.name AS provider_name, s.title as service_title FROM reviews r JOIN users u ON r.provider_id = u.id JOIN bookings b ON r.booking_id = b.id JOIN services s ON b.service_id = s.id WHERE r.reviewer_id = $client_id ORDER BY r.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reviews - Client Dashboard</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
  <!-- Professional Navigation -->
  <nav class="navbar navbar-expand-lg navbar-glass">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="dashboard_client.php">
        <i class="bi bi-person-circle me-2"></i>Client<span class="text-gradient-secondary">&</span>Dashboard
      </a>
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

  <!-- Hero Section -->
  <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 30vh;">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="glass-card">
            <h1 class="display-4 fw-black mb-3 gradient-text">My Reviews</h1>
            <p class="lead mb-0">Share your feedback and help others make informed decisions</p>
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
    <div class="row g-4">
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total reviews submitted">
          <div class="kpi-icon">
            <i class="bi bi-star"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_reviews'] ?></div>
          <div class="kpi-label">Total Reviews</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Submitted
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Average rating given">
          <div class="kpi-icon">
            <i class="bi bi-star-fill"></i>
          </div>
          <div class="kpi-number"><?= number_format($stats_data['avg_rating'] ?? 0, 1) ?></div>
          <div class="kpi-label">Avg Rating</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Excellent
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="5-star reviews given">
          <div class="kpi-icon">
            <i class="bi bi-star-fill text-warning"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['five_star'] ?></div>
          <div class="kpi-label">5-Star Reviews</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Outstanding
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="4-star reviews given">
          <div class="kpi-icon">
            <i class="bi bi-star-fill text-warning"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['four_star'] ?></div>
          <div class="kpi-label">4-Star Reviews</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Very Good
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="3-star reviews given">
          <div class="kpi-icon">
            <i class="bi bi-star text-warning"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['three_star'] ?></div>
          <div class="kpi-label">3-Star Reviews</div>
          <div class="kpi-trend neutral">
            <i class="bi bi-dash"></i> Good
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Lower rated reviews">
          <div class="kpi-icon">
            <i class="bi bi-star text-muted"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['two_star'] + $stats_data['one_star'] ?></div>
          <div class="kpi-label">Lower Ratings</div>
          <div class="kpi-trend negative">
            <i class="bi bi-arrow-down"></i> Needs Improvement
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Review Management Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="gradient-text mb-0">
              <i class="bi bi-star me-2"></i>Review Management
            </h2>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="scrollToReviews()">
                <i class="bi bi-list-ul me-1"></i>View All Reviews
              </button>
            </div>
          </div>
          
          <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>
          
          <?php if (isset($success)): ?>
            <div class="alert alert-success" role="alert">
              <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>

          <!-- Leave Reviews Section -->
          <?php if (mysqli_num_rows($to_review) > 0): ?>
            <div class="mb-5">
              <h3 class="gradient-text mb-4">
                <i class="bi bi-plus-circle me-2"></i>Leave a Review
                <span class="badge bg-warning ms-2"><?= mysqli_num_rows($to_review) ?> pending</span>
              </h3>
              <div class="row g-3">
                <?php while($b = mysqli_fetch_assoc($to_review)): ?>
                  <div class="col-12">
                    <div class="glass-card p-4">
                      <form method="POST" class="row g-3 align-items-end">
                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                        <input type="hidden" name="provider_id" value="<?= $b['provider_id'] ?>">
                        
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Service</label>
                          <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                              <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                              <div class="fw-semibold"><?= htmlspecialchars($b['title']) ?></div>
                              <small class="text-muted">Booking #<?= $b['id'] ?></small>
                            </div>
                          </div>
                        </div>
                        
                        <div class="col-md-3">
                          <label class="form-label fw-semibold">Provider</label>
                          <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                              <i class="bi bi-building"></i>
                            </div>
                            <div class="fw-semibold"><?= htmlspecialchars($b['provider_name']) ?></div>
                          </div>
                        </div>
                        
                        <div class="col-md-2">
                          <label for="rating_<?= $b['id'] ?>" class="form-label fw-semibold">Rating</label>
                          <select name="rating" id="rating_<?= $b['id'] ?>" class="form-select" required>
                            <option value="">Select</option>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Very Good</option>
                            <option value="3">⭐⭐⭐ Good</option>
                            <option value="2">⭐⭐ Fair</option>
                            <option value="1">⭐ Poor</option>
                          </select>
                        </div>
                        
                        <div class="col-md-3">
                          <label for="comment_<?= $b['id'] ?>" class="form-label fw-semibold">Comment</label>
                          <input type="text" name="comment" id="comment_<?= $b['id'] ?>" class="form-control" placeholder="Share your experience..." required>
                        </div>
                        
                        <div class="col-md-12">
                          <button type="submit" class="btn btn-success">
                            <i class="bi bi-star me-2"></i>Submit Review
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                <?php endwhile; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- My Reviews Section -->
          <div id="my-reviews">
            <h3 class="gradient-text mb-4">
              <i class="bi bi-list-ul me-2"></i>My Past Reviews
              <span class="badge bg-primary ms-2"><?= mysqli_num_rows($reviews) ?> total</span>
            </h3>
            
            <?php if (mysqli_num_rows($reviews) > 0): ?>
              <div class="table-container">
                <div class="table-responsive">
                  <table class="table table-dark align-middle">
                    <thead>
                      <tr>
                        <th>Review ID</th>
                        <th>Service</th>
                        <th>Provider</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while($r = mysqli_fetch_assoc($reviews)): ?>
                        <tr>
                          <td class="number-cell">
                            <div class="d-flex align-items-center">
                              <div class="user-avatar me-3">
                                <i class="bi bi-hash"></i>
                              </div>
                              <div>
                                <div class="fw-semibold">#<?= $r['id'] ?></div>
                                <small class="text-muted">Review</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="user-avatar me-3">
                                <i class="bi bi-box-seam"></i>
                              </div>
                              <div>
                                <div class="fw-semibold"><?= htmlspecialchars($r['service_title']) ?></div>
                                <small class="text-muted">Service</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="user-avatar me-3">
                                <i class="bi bi-building"></i>
                              </div>
                              <div>
                                <div class="fw-semibold"><?= htmlspecialchars($r['provider_name']) ?></div>
                                <small class="text-muted">Provider</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="text-warning me-2">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                  <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                              </div>
                              <span class="badge bg-warning text-dark">
                                <?= $r['rating'] ?>/5
                              </span>
                            </div>
                          </td>
                          <td>
                            <div class="fw-semibold"><?= htmlspecialchars($r['comment']) ?></div>
                          </td>
                          <td class="date-cell">
                            <i class="bi bi-calendar3 me-2"></i>
                            <?= date('M d, Y', strtotime($r['created_at'])) ?>
                          </td>
                          <td class="actions-cell">
                            <div class="btn-group" role="group">
                              <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </button>
                              <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="viewReviewDetails(<?= $r['id'] ?>, '<?= htmlspecialchars($r['provider_name']) ?>', <?= $r['rating'] ?>, '<?= htmlspecialchars($r['comment']) ?>', '<?= $r['created_at'] ?>')">
                                  <i class="bi bi-eye me-2"></i>View Details
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="editReview(<?= $r['id'] ?>, <?= $r['rating'] ?>, '<?= htmlspecialchars($r['comment']) ?>')">
                                  <i class="bi bi-pencil me-2"></i>Edit Review
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteReview(<?= $r['id'] ?>)">
                                  <i class="bi bi-trash me-2"></i>Delete Review
                                </a></li>
                              </ul>
                            </div>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php else: ?>
              <div class="text-center py-5">
                <i class="bi bi-star display-1 text-muted"></i>
                <h4 class="mt-3 mb-2">No Reviews Yet</h4>
                <p class="text-muted mb-4">Complete bookings to leave reviews and help other users</p>
                <a href="client_bookings.php" class="btn btn-primary">
                  <i class="bi bi-calendar-check me-2"></i>View My Bookings
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Edit Review Modal -->
  <div class="modal fade" id="editReviewModal" tabindex="-1" aria-labelledby="editReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
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
  <footer class="footer-glass text-center py-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <p class="mb-2">&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</p>
          <p class="mb-0">
            <a href="#privacy" class="text-decoration-none me-3">Privacy Policy</a>
            <a href="#terms" class="text-decoration-none me-3">Terms of Service</a>
            <a href="#contact" class="text-decoration-none">Contact Us</a>
          </p>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
  <script>
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Scroll to reviews section
    function scrollToReviews() {
      document.getElementById('my-reviews').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }

    // View review details
    function viewReviewDetails(reviewId, provider_name, rating, comment, created_at) {
      // Create a simple modal for viewing review details
      const modal = document.createElement('div');
      modal.className = 'modal fade';
      modal.id = 'viewReviewModal';
      modal.innerHTML = `
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title gradient-text">
                <i class="bi bi-star me-2"></i>Review Details
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Review ID</label>
                    <input type="text" class="form-control" value="${reviewId}" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Rating</label>
                    <input type="text" class="form-control" value="${rating} Stars" readonly>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Provider</label>
                <input type="text" class="form-control" value="${provider_name}" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Comment</label>
                <textarea class="form-control" rows="4" readonly>${comment || 'No comment provided'}</textarea>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Created Date</label>
                <input type="text" class="form-control" value="${new Date(created_at).toLocaleDateString('en-US', {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                })}" readonly>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
      const bootstrapModal = new bootstrap.Modal(modal);
      bootstrapModal.show();
      
      // Remove modal from DOM after it's hidden
      modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
      });
    }

    // Edit review
    function editReview(id, rating, comment) {
      document.getElementById('edit_review_id').value = id;
      document.getElementById('edit_rating').value = rating;
      document.getElementById('edit_comment').value = comment;
      
      var modal = new bootstrap.Modal(document.getElementById('editReviewModal'));
      modal.show();
    }

    // Delete review
    function deleteReview(reviewId) {
      if (confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        // This would typically submit a delete request
        alert('Delete review for ID: ' + reviewId);
      }
    }
  </script>
</body>
</html> 
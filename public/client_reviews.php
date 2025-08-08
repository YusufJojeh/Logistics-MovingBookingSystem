<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/multilanguage.php';
require_once __DIR__ . '/../includes/auth.php';

// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) session_start();
if (!is_logged_in() || !is_client()) { 
    header('Location: login.php'); 
    exit; 
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$CSRF = $_SESSION['csrf_token'];

// Helper functions
function e($v) { 
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); 
}

function flash($key) { 
    if (!empty($_SESSION[$key])) { 
        $m = $_SESSION[$key]; 
        unset($_SESSION[$key]); 
        return $m; 
    } 
    return null; 
}

$user = current_user();
$client_id = $user['id'];

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Security check failed. Please try again.";
        header('Location: client_reviews.php'); 
        exit;
    }

    $action = $_POST['action'] ?? '';

    // Delete review
    if ($action === 'delete') {
        $review_id = (int)($_POST['review_id'] ?? 0);
        if ($review_id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ? AND reviewer_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $review_id, $client_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = "Review deleted successfully.";
            } else {
                $_SESSION['flash_error'] = "Delete failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
        header('Location: client_reviews.php'); 
        exit;
    }

    // Edit review
    if ($action === 'edit') {
        $review_id = (int)($_POST['review_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($review_id && $rating >= 1 && $rating <= 5 && $comment !== '') {
            $stmt = mysqli_prepare($conn, "UPDATE reviews SET rating = ?, comment = ? WHERE id = ? AND reviewer_id = ?");
            mysqli_stmt_bind_param($stmt, 'isii', $rating, $comment, $review_id, $client_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = "Review updated successfully.";
            } else {
                $_SESSION['flash_error'] = "Update failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['flash_error'] = "Please provide a valid rating (1-5) and comment.";
        }
        header('Location: client_reviews.php'); 
        exit;
    }

    // Add new review
    if ($action === 'add') {
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        $provider_id = (int)($_POST['provider_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        $errors = [];
        if (!$booking_id) $errors[] = "Booking is required.";
        if (!$provider_id) $errors[] = "Provider is required.";
        if ($rating < 1 || $rating > 5) $errors[] = "Rating must be between 1-5.";
        if ($comment === '') $errors[] = "Comment is required.";

        // Validate booking exists, belongs to client, and is completed
        if (!$errors) {
            $chk = mysqli_prepare($conn, "
                SELECT b.id 
                FROM bookings b
                WHERE b.id = ? AND b.client_id = ? AND b.provider_id = ? AND b.status = 'completed'
                LIMIT 1
            ");
            mysqli_stmt_bind_param($chk, 'iii', $booking_id, $client_id, $provider_id);
            mysqli_stmt_execute($chk);
            $res = mysqli_stmt_get_result($chk);
            if (!$res || mysqli_num_rows($res) === 0) {
                $errors[] = "Invalid booking. Only completed bookings can be reviewed.";
            }
            mysqli_stmt_close($chk);
        }

                // Check if already reviewed
        if (!$errors) {
            $exists = mysqli_prepare($conn, "SELECT COUNT(*) FROM reviews WHERE booking_id = ? AND reviewer_id = ?");
            mysqli_stmt_bind_param($exists, 'ii', $booking_id, $client_id);
            mysqli_stmt_execute($exists);
            mysqli_stmt_bind_result($exists, $count);
            mysqli_stmt_fetch($exists);
            mysqli_stmt_close($exists);
            
            if ($count > 0) {
                $errors[] = "You have already reviewed this booking.";
            }
        }

        if (!$errors) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO reviews (booking_id, reviewer_id, provider_id, rating, comment, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            mysqli_stmt_bind_param($stmt, 'iiiis', $booking_id, $client_id, $provider_id, $rating, $comment);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['flash_success'] = "Review submitted successfully.";
            } else {
                $_SESSION['flash_error'] = "Submit failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['flash_error'] = implode(' ', $errors);
        }
        header('Location: client_reviews.php'); 
        exit;
    }

    header('Location: client_reviews.php'); 
    exit;
}

// Get review statistics
$stats_query = mysqli_query($conn, "
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
$stats_data = mysqli_fetch_assoc($stats_query);

// Get completed bookings not yet reviewed
$to_review_query = mysqli_query($conn, "
    SELECT b.id, s.title, u.name AS provider_name, b.provider_id, b.booking_date
    FROM bookings b 
    JOIN services s ON b.service_id = s.id 
    JOIN users u ON b.provider_id = u.id 
    WHERE b.client_id = $client_id 
    AND b.status = 'completed' 
    AND b.id NOT IN (SELECT booking_id FROM reviews WHERE reviewer_id = $client_id)
    ORDER BY b.booking_date DESC
");

// Get user's reviews
$reviews_query = mysqli_query($conn, "
    SELECT r.*, u.name AS provider_name, s.title as service_title, b.booking_date
    FROM reviews r 
    JOIN users u ON r.provider_id = u.id 
    JOIN bookings b ON r.booking_id = b.id 
    JOIN services s ON b.service_id = s.id 
    WHERE r.reviewer_id = $client_id 
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MovePro Client - My Reviews</title>
    <link rel="icon" href="../assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .review-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1.2em;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .pending-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }
        .floating-add-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: white;
            font-size: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .floating-add-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
            color: white;
        }
        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 5px;
        }
        .rating-input input {
            display: none;
        }
        .rating-input label {
            font-size: 2em;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #ffc107;
        }
        .modal-review-preview {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
        }
    </style>
</head>
<body class="modern-bg">
    <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-glass">
    <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-3" href="dashboard_client.php">
                <img src="../assets/img/logo.svg" alt="MovePro Client Logo" class="logo-svg">
                <span class="logo-text-white">MovePro</span><span class="logo-text-blue">Client</span>
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

    <!-- Flash Messages -->
    <?php if ($s = flash('flash_success')): ?>
        <section class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i><?= e($s) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
    
    <?php if ($e = flash('flash_error')): ?>
        <section class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= e($e) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Statistics -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Review Overview</h2>
      </div>
    </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
    <div class="row g-4">
                    <div class="col-md-2">
                        <div class="kpi-card text-center">
                            <div class="kpi-icon"><i class="bi bi-star"></i></div>
                            <div class="kpi-number"><?= number_format($stats_data['total_reviews'] ?? 0) ?></div>
          <div class="kpi-label">Total Reviews</div>
                            <div class="kpi-trend text-success"><i class="bi bi-arrow-up"></i> Submitted</div>
          </div>
        </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center">
                            <div class="kpi-icon"><i class="bi bi-star-fill"></i></div>
          <div class="kpi-number"><?= number_format($stats_data['avg_rating'] ?? 0, 1) ?></div>
                            <div class="kpi-label">Average Rating</div>
                            <div class="kpi-trend text-warning"><i class="bi bi-star"></i> Out of 5</div>
          </div>
        </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center">
                            <div class="kpi-icon"><i class="bi bi-star-fill text-warning"></i></div>
                            <div class="kpi-number"><?= number_format($stats_data['five_star'] ?? 0) ?></div>
          <div class="kpi-label">5-Star Reviews</div>
                            <div class="kpi-trend text-success"><i class="bi bi-arrow-up"></i> Outstanding</div>
          </div>
        </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center">
                            <div class="kpi-icon"><i class="bi bi-star-fill text-warning"></i></div>
                            <div class="kpi-number"><?= number_format($stats_data['four_star'] ?? 0) ?></div>
          <div class="kpi-label">4-Star Reviews</div>
                            <div class="kpi-trend text-success"><i class="bi bi-arrow-up"></i> Very Good</div>
          </div>
        </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center">
                            <div class="kpi-icon"><i class="bi bi-star text-warning"></i></div>
                            <div class="kpi-number"><?= number_format($stats_data['three_star'] ?? 0) ?></div>
          <div class="kpi-label">3-Star Reviews</div>
                            <div class="kpi-trend text-info"><i class="bi bi-dash"></i> Good</div>
          </div>
        </div>
                    <div class="col-md-2">
                        <div class="kpi-card text-center">
                            <div class="kpi-icon"><i class="bi bi-star text-muted"></i></div>
                            <div class="kpi-number"><?= number_format(($stats_data['two_star'] ?? 0) + ($stats_data['one_star'] ?? 0)) ?></div>
          <div class="kpi-label">Lower Ratings</div>
                            <div class="kpi-trend text-danger"><i class="bi bi-arrow-down"></i> Needs Improvement</div>
                        </div>
          </div>
        </div>
      </div>
    </div>
  </section>

    <!-- Review Management -->
    <section class="container-fluid py-6 section-glass">
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
                            <button type="button" class="btn btn-primary btn-sm" onclick="showAddReviewModal()">
                                <i class="bi bi-plus-circle me-1"></i>Add New Review
              </button>
            </div>
          </div>

          <!-- Leave Reviews Section -->
                    <?php if (mysqli_num_rows($to_review_query) > 0): ?>
            <div class="mb-5">
              <h3 class="gradient-text mb-4">
                <i class="bi bi-plus-circle me-2"></i>Leave a Review
                                <span class="pending-badge ms-2"><?= mysqli_num_rows($to_review_query) ?> pending</span>
              </h3>
              <div class="row g-3">
                                <?php while($booking = mysqli_fetch_assoc($to_review_query)): ?>
                  <div class="col-12">
                                        <div class="review-card">
                      <form method="POST" class="row g-3 align-items-end">
                                                <input type="hidden" name="csrf_token" value="<?= e($CSRF) ?>">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                                                <input type="hidden" name="provider_id" value="<?= (int)$booking['provider_id'] ?>">
                        
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Service</label>
                          <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                              <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                                            <div class="fw-semibold"><?= e($booking['title']) ?></div>
                                                            <small class="text-muted">Booking #<?= (int)$booking['id'] ?></small>
                            </div>
                          </div>
                        </div>
                        
                        <div class="col-md-3">
                          <label class="form-label fw-semibold">Provider</label>
                          <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                              <i class="bi bi-building"></i>
                            </div>
                                                        <div class="fw-semibold"><?= e($booking['provider_name']) ?></div>
                          </div>
                        </div>
                        
                        <div class="col-md-2">
                                                    <label for="rating_<?= $booking['id'] ?>" class="form-label fw-semibold">Rating</label>
                                                    <select name="rating" id="rating_<?= $booking['id'] ?>" class="form-select" required>
                            <option value="">Select</option>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Very Good</option>
                            <option value="3">⭐⭐⭐ Good</option>
                            <option value="2">⭐⭐ Fair</option>
                            <option value="1">⭐ Poor</option>
                          </select>
                        </div>
                        
                        <div class="col-md-3">
                                                    <label for="comment_<?= $booking['id'] ?>" class="form-label fw-semibold">Comment</label>
                                                    <input type="text" name="comment" id="comment_<?= $booking['id'] ?>" 
                                                           class="form-control" placeholder="Share your experience..." required>
                        </div>
                        
                        <div class="col-md-12">
                                                    <div class="d-flex gap-2">
                          <button type="submit" class="btn btn-success">
                            <i class="bi bi-star me-2"></i>Submit Review
                          </button>
                                                        <button type="button" class="btn btn-outline-primary" 
                                                                onclick="quickReview(<?= (int)$booking['id'] ?>, '<?= e($booking['title']) ?>', '<?= e($booking['provider_name']) ?>', <?= (int)$booking['provider_id'] ?>)">
                                                            <i class="bi bi-lightning me-2"></i>Quick Review
                                                        </button>
                                                    </div>
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
                            <span class="badge bg-primary ms-2"><?= mysqli_num_rows($reviews_query) ?> total</span>
            </h3>
            
                        <?php if (mysqli_num_rows($reviews_query) > 0): ?>
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
                                            <?php while($review = mysqli_fetch_assoc($reviews_query)): ?>
                        <tr>
                          <td class="number-cell">
                            <div class="d-flex align-items-center">
                              <div class="user-avatar me-3">
                                <i class="bi bi-hash"></i>
                              </div>
                              <div>
                                                                <div class="fw-semibold">#<?= (int)$review['id'] ?></div>
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
                                                                <div class="fw-semibold"><?= e($review['service_title']) ?></div>
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
                                                                <div class="fw-semibold"><?= e($review['provider_name']) ?></div>
                                <small class="text-muted">Provider</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                                                            <div class="rating-stars me-2">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="bi bi-star<?= $i <= (int)$review['rating'] ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                              </div>
                              <span class="badge bg-warning text-dark">
                                                                <?= (int)$review['rating'] ?>/5
                              </span>
                            </div>
                          </td>
                          <td>
                                                        <div class="fw-semibold"><?= e($review['comment']) ?></div>
                          </td>
                          <td class="date-cell">
                            <i class="bi bi-calendar3 me-2"></i>
                                                        <?= date('M d, Y', strtotime($review['created_at'])) ?>
                          </td>
                          <td class="actions-cell">
                            <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                                    data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                              </button>
                              <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="#" 
                                                                       onclick="viewReviewDetails(<?= (int)$review['id'] ?>, '<?= e($review['provider_name']) ?>', <?= (int)$review['rating'] ?>, '<?= e($review['comment']) ?>', '<?= e($review['created_at']) ?>')">
                                  <i class="bi bi-eye me-2"></i>View Details
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="#" 
                                                                       onclick="editReview(<?= (int)$review['id'] ?>, <?= (int)$review['rating'] ?>, '<?= e($review['comment']) ?>')">
                                  <i class="bi bi-pencil me-2"></i>Edit Review
                                                                    </a>
                                                                </li>
                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form method="POST" class="d-inline" 
                                                                          onsubmit="return confirm('Delete this review? This action cannot be undone.');">
                                                                        <input type="hidden" name="csrf_token" value="<?= e($CSRF) ?>">
                                                                        <input type="hidden" name="action" value="delete">
                                                                        <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                                                        <button type="submit" class="dropdown-item text-danger">
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

    <!-- View Review Modal -->
    <div class="modal fade" id="viewReviewModal" tabindex="-1" aria-labelledby="viewReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title gradient-text" id="viewReviewModalLabel">
                        <i class="bi bi-star me-2"></i>Review Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Review ID</label>
                            <input type="text" class="form-control" id="view_review_id" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Rating</label>
                            <input type="text" class="form-control" id="view_review_rating" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Created</label>
                            <input type="text" class="form-control" id="view_review_created_at" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Provider</label>
                            <input type="text" class="form-control" id="view_review_provider" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Comment</label>
                            <textarea class="form-control" id="view_review_comment" rows="4" readonly></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Review Modal -->
    <div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="addReviewForm">
                    <input type="hidden" name="csrf_token" value="<?= e($CSRF) ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="booking_id" id="add_booking_id">
                    <input type="hidden" name="provider_id" id="add_provider_id">
                    <input type="hidden" name="rating" id="add_rating_value">
                    
                    <div class="modal-header">
                        <h5 class="modal-title gradient-text" id="addReviewModalLabel">
                            <i class="bi bi-star me-2"></i>Add New Review
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Select Booking *</label>
                                <select class="form-select" id="add_booking_select" required onchange="updateBookingInfo()">
                                    <option value="">Choose a completed booking...</option>
                                    <?php 
                                    // Reset the query pointer
                                    mysqli_data_seek($to_review_query, 0);
                                    while($booking = mysqli_fetch_assoc($to_review_query)): 
                                    ?>
                                        <option value="<?= (int)$booking['id'] ?>" 
                                                data-provider-id="<?= (int)$booking['provider_id'] ?>"
                                                data-service-title="<?= e($booking['title']) ?>"
                                                data-provider-name="<?= e($booking['provider_name']) ?>">
                                            #<?= (int)$booking['id'] ?> - <?= e($booking['title']) ?> (<?= e($booking['provider_name']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Service</label>
                                <input type="text" class="form-control" id="add_service_title" readonly placeholder="Select booking first">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Provider</label>
                                <input type="text" class="form-control" id="add_provider_name" readonly placeholder="Select booking first">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Rating *</label>
                                <div class="rating-input">
                                    <input type="radio" id="star5" name="rating_stars" value="5">
                                    <label for="star5">⭐</label>
                                    <input type="radio" id="star4" name="rating_stars" value="4">
                                    <label for="star4">⭐</label>
                                    <input type="radio" id="star3" name="rating_stars" value="3">
                                    <label for="star3">⭐</label>
                                    <input type="radio" id="star2" name="rating_stars" value="2">
                                    <label for="star2">⭐</label>
                                    <input type="radio" id="star1" name="rating_stars" value="1">
                                    <label for="star1">⭐</label>
                                </div>
                                <div class="text-center mt-2">
                                    <small class="text-muted" id="rating_text">Click stars to rate</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Comment *</label>
                                <textarea class="form-control" name="comment" id="add_comment" rows="4" 
                                          placeholder="Share your experience with this service..." required></textarea>
                                <div class="form-text">Tell others about your experience with this service.</div>
                            </div>
                            
                            <!-- Review Preview -->
                            <div class="col-12" id="review_preview" style="display: none;">
                                <label class="form-label fw-bold">Review Preview</label>
                                <div class="modal-review-preview">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong id="preview_service">Service Name</strong>
                                        <div id="preview_rating">⭐⭐⭐⭐⭐</div>
                                    </div>
                                    <p id="preview_comment" class="mb-0">Your comment will appear here...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="submit_review_btn" disabled>
                            <i class="bi bi-star me-2"></i>Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Add Review Modal -->
    <div class="modal fade" id="quickAddReviewModal" tabindex="-1" aria-labelledby="quickAddReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($CSRF) ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="booking_id" id="quick_booking_id">
                    <input type="hidden" name="provider_id" id="quick_provider_id">
                    <div class="modal-header">
                        <h5 class="modal-title gradient-text" id="quickAddReviewModalLabel">
                            <i class="bi bi-star me-2"></i>Quick Review
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Service</label>
                                <input type="text" class="form-control" id="quick_service_title" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Provider</label>
                                <input type="text" class="form-control" id="quick_provider_name" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rating *</label>
                                <select class="form-select" name="rating" id="quick_rating" required>
                                    <option value="">Select Rating</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                    <option value="4">⭐⭐⭐⭐ Very Good</option>
                                    <option value="3">⭐⭐⭐ Good</option>
                                    <option value="2">⭐⭐ Fair</option>
                                    <option value="1">⭐ Poor</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Comment *</label>
                                <textarea class="form-control" name="comment" id="quick_comment" rows="4" 
                                          placeholder="Share your experience with this service..." required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-star me-2"></i>Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

  <!-- Edit Review Modal -->
  <div class="modal fade" id="editReviewModal" tabindex="-1" aria-labelledby="editReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e($CSRF) ?>">
                    <input type="hidden" name="action" value="edit">
          <input type="hidden" name="review_id" id="edit_review_id">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="editReviewModalLabel">
              <i class="bi bi-pencil-square me-2"></i>Edit Review
            </h5>
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
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-2"></i>Update Review
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

    <!-- Floating Add Review Button -->
    <button type="button" class="floating-add-btn" onclick="showAddReviewModal()" title="Add New Review">
        <i class="bi bi-plus"></i>
    </button>

  <!-- Footer -->
    <footer class="footer-glass text-center py-4 mt-5">
        <small>&copy; <?= date('Y'); ?> MovePro. All rights reserved.</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
  <script>
    // Scroll to reviews section
    function scrollToReviews() {
      document.getElementById('my-reviews').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }

    // View review details
        function viewReviewDetails(id, providerName, rating, comment, createdAt) {
            document.getElementById('view_review_id').value = '#' + id;
            document.getElementById('view_review_provider').value = providerName;
            document.getElementById('view_review_rating').value = rating + ' Star' + (rating > 1 ? 's' : '');
            document.getElementById('view_review_comment').value = comment || 'No comment provided';
            
            try {
                const d = new Date(createdAt);
                document.getElementById('view_review_created_at').value = d.toLocaleString();
            } catch(e) {
                document.getElementById('view_review_created_at').value = createdAt;
            }
            
            new bootstrap.Modal(document.getElementById('viewReviewModal')).show();
    }

    // Edit review
    function editReview(id, rating, comment) {
      document.getElementById('edit_review_id').value = id;
      document.getElementById('edit_rating').value = rating;
      document.getElementById('edit_comment').value = comment;
            new bootstrap.Modal(document.getElementById('editReviewModal')).show();
        }

        // Show add review modal
        function showAddReviewModal() {
            // Reset form
            document.getElementById('addReviewForm').reset();
            document.getElementById('add_service_title').value = '';
            document.getElementById('add_provider_name').value = '';
            document.getElementById('add_booking_id').value = '';
            document.getElementById('add_provider_id').value = '';
            document.getElementById('add_rating_value').value = '';
            document.getElementById('review_preview').style.display = 'none';
            document.getElementById('submit_review_btn').disabled = true;
            document.getElementById('rating_text').textContent = 'Click stars to rate';
            
            new bootstrap.Modal(document.getElementById('addReviewModal')).show();
        }

        // Update booking information when selection changes
        function updateBookingInfo() {
            const select = document.getElementById('add_booking_select');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                document.getElementById('add_service_title').value = selectedOption.getAttribute('data-service-title');
                document.getElementById('add_provider_name').value = selectedOption.getAttribute('data-provider-name');
                document.getElementById('add_booking_id').value = selectedOption.value;
                document.getElementById('add_provider_id').value = selectedOption.getAttribute('data-provider-id');
            } else {
                document.getElementById('add_service_title').value = '';
                document.getElementById('add_provider_name').value = '';
                document.getElementById('add_booking_id').value = '';
                document.getElementById('add_provider_id').value = '';
            }
            updateSubmitButton();
        }

        // Update rating display and hidden input
        function updateRating(rating) {
            document.getElementById('add_rating_value').value = rating;
            const ratingText = document.getElementById('rating_text');
            const ratingLabels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
            ratingText.textContent = ratingLabels[rating] || 'Click stars to rate';
            updateSubmitButton();
            updateReviewPreview();
        }

        // Update submit button state
        function updateSubmitButton() {
            const bookingId = document.getElementById('add_booking_id').value;
            const rating = document.getElementById('add_rating_value').value;
            const comment = document.getElementById('add_comment').value.trim();
            
            const submitBtn = document.getElementById('submit_review_btn');
            submitBtn.disabled = !(bookingId && rating && comment);
        }

        // Update review preview
        function updateReviewPreview() {
            const service = document.getElementById('add_service_title').value;
            const rating = document.getElementById('add_rating_value').value;
            const comment = document.getElementById('add_comment').value.trim();
            
            if (service && rating && comment) {
                document.getElementById('preview_service').textContent = service;
                document.getElementById('preview_rating').innerHTML = '⭐'.repeat(rating);
                document.getElementById('preview_comment').textContent = comment;
                document.getElementById('review_preview').style.display = 'block';
            } else {
                document.getElementById('review_preview').style.display = 'none';
            }
        }

        // Quick review modal
        function quickReview(bookingId, serviceTitle, providerName, providerId) {
            document.getElementById('quick_booking_id').value = bookingId;
            document.getElementById('quick_provider_id').value = providerId;
            document.getElementById('quick_service_title').value = serviceTitle;
            document.getElementById('quick_provider_name').value = providerName;
            document.getElementById('quick_rating').value = '';
            document.getElementById('quick_comment').value = '';
            new bootstrap.Modal(document.getElementById('quickAddReviewModal')).show();
        }

        // Initialize Bootstrap tooltips and event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Add event listeners for rating stars
            document.querySelectorAll('input[name="rating_stars"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    updateRating(this.value);
                });
            });

            // Add event listener for comment textarea
            document.getElementById('add_comment').addEventListener('input', function() {
                updateSubmitButton();
                updateReviewPreview();
            });

            // Add event listener for form submission
            document.getElementById('addReviewForm').addEventListener('submit', function(e) {
                const bookingId = document.getElementById('add_booking_id').value;
                const rating = document.getElementById('add_rating_value').value;
                const comment = document.getElementById('add_comment').value.trim();
                
                if (!bookingId || !rating || !comment) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }
            });
        });
  </script>
</body>
</html> 

<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
require_once '../includes/auth.php';

// Check if user is logged in and is provider
if (!is_logged_in() || !is_provider()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
$provider_id = $user['id'];

// Get provider statistics
$stats = mysqli_query($conn, "
    SELECT 
        (SELECT COUNT(*) FROM services WHERE provider_id = $provider_id) as total_services,
        (SELECT COUNT(*) FROM bookings WHERE provider_id = $provider_id) as total_bookings,
        (SELECT COUNT(*) FROM bookings WHERE provider_id = $provider_id AND status = 'completed') as completed_bookings,
        (SELECT COUNT(*) FROM reviews WHERE provider_id = $provider_id) as total_reviews,
        (SELECT AVG(rating) FROM reviews WHERE provider_id = $provider_id) as avg_rating,
        (SELECT COUNT(*) FROM bookings WHERE provider_id = $provider_id AND status = 'completed') as total_completed
");

$stats_data = mysqli_fetch_assoc($stats);

// Get recent bookings
$recent_bookings = mysqli_query($conn, "
    SELECT b.*, s.title as service_title, u.name as client_name, u.email as client_email
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.client_id = u.id
    WHERE b.provider_id = $provider_id
    ORDER BY b.created_at DESC
    LIMIT 5
");

// Get recent reviews
$recent_reviews = mysqli_query($conn, "
    SELECT r.*, s.title as service_title, u.name as client_name
    FROM reviews r
    JOIN bookings b ON r.booking_id = b.id
    JOIN services s ON b.service_id = s.id
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.provider_id = $provider_id
    ORDER BY r.created_at DESC
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Provider Dashboard - Logistics & Moving Booking System</title>
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
      <a class="navbar-brand fw-bold fs-3" href="dashboard_provider.php">
        <img src="../assets/img/logo.svg" alt="MovePro Provider Logo" class="logo-svg">
        <span class="logo-text-white">MovePro</span><span class="logo-text-blue">Provider</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#providerNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="providerNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link active" href="dashboard_provider.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="provider_services.php">My Services</a></li>
          <li class="nav-item"><a class="nav-link" href="provider_bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="provider_reviews.php">Reviews</a></li>
          <li class="nav-item"><a class="nav-link" href="provider_profile.php">Profile</a></li>
          <li class="nav-item ms-3">
            <div class="dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= get_profile_image_html($user, 'small', false) ?>
                <span class="ms-2"><?= htmlspecialchars($user['name']) ?></span>
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="provider_profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              </ul>
            </div>
          </li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
            <p class="lead mb-0">Manage your services, bookings, and grow your business</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Your Business Overview</h2>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total active services you offer">
          <div class="kpi-icon">
            <i class="bi bi-gear-wide-connected"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_services'] ?></div>
          <div class="kpi-label">Active Services</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Active
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total bookings received">
          <div class="kpi-icon">
            <i class="bi bi-calendar-check"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_bookings'] ?></div>
          <div class="kpi-label">Total Bookings</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Growing
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Successfully completed bookings">
          <div class="kpi-icon">
            <i class="bi bi-check-circle"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['completed_bookings'] ?></div>
          <div class="kpi-label">Completed</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> <?= $stats_data['total_bookings'] > 0 ? round(($stats_data['completed_bookings'] / $stats_data['total_bookings']) * 100) : 0 ?>%
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Customer reviews received">
          <div class="kpi-icon">
            <i class="bi bi-star"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_reviews'] ?></div>
          <div class="kpi-label">Reviews</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Feedback
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Average customer rating">
          <div class="kpi-icon">
            <i class="bi bi-star-fill"></i>
          </div>
          <div class="kpi-number"><?= number_format($stats_data['avg_rating'], 1) ?></div>
          <div class="kpi-label">Avg Rating</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Excellent
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total completed bookings">
          <div class="kpi-icon">
            <i class="bi bi-currency-dollar"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_completed'] ?></div>
          <div class="kpi-label">Completed</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Successful
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Quick Actions Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Quick Actions</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <div class="col-lg-3 col-md-6">
            <div class="feature-glass-card text-center">
              <div class="feature-icon">
                <i class="bi bi-plus-circle"></i>
              </div>
              <h4 class="gradient-text mb-3">Add Service</h4>
              <p>Create a new service offering to attract more clients and grow your business.</p>
              <a href="provider_services.php" class="btn btn-outline-primary">Add Service</a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-glass-card text-center">
              <div class="feature-icon">
                <i class="bi bi-calendar-check"></i>
              </div>
              <h4 class="gradient-text mb-3">Manage Bookings</h4>
              <p>View and manage all your bookings, update statuses, and communicate with clients.</p>
              <a href="provider_bookings.php" class="btn btn-outline-primary">Manage Bookings</a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-glass-card text-center">
              <div class="feature-icon">
                <i class="bi bi-star"></i>
              </div>
              <h4 class="gradient-text mb-3">View Reviews</h4>
              <p>Check customer feedback and ratings to improve your services and reputation.</p>
              <a href="provider_reviews.php" class="btn btn-outline-primary">View Reviews</a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-glass-card text-center">
              <div class="feature-icon">
                <i class="bi bi-person-circle"></i>
              </div>
              <h4 class="gradient-text mb-3">Update Profile</h4>
              <p>Keep your profile information up to date to maintain professional credibility.</p>
              <a href="provider_profile.php" class="btn btn-outline-primary">Update Profile</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Recent Activity Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Recent Activity</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <!-- Recent Bookings -->
          <div class="col-lg-8">
            <div class="glass-card">
              <h3 class="gradient-text mb-4">
                <i class="bi bi-calendar-check me-2"></i>Recent Bookings
              </h3>
              <?php if (mysqli_num_rows($recent_bookings) > 0): ?>
                <div class="table-container">
                  <div class="table-responsive">
                    <table class="table table-dark align-middle">
                      <thead>
                        <tr>
                          <th>Service</th>
                          <th>Client</th>
                          <th>Date</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                          <tr>
                            <td>
                              <div class="d-flex align-items-center">
                                <div class="user-avatar me-3">
                                  <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                  <div class="fw-semibold"><?= htmlspecialchars($booking['service_title']) ?></div>
                                  <small class="text-muted">Booking #<?= $booking['id'] ?></small>
                                </div>
                              </div>
                            </td>
                            <td>
                              <div class="d-flex align-items-center">
                                <div class="user-avatar me-3">
                                  <i class="bi bi-person"></i>
                                </div>
                                <div>
                                  <div class="fw-semibold"><?= htmlspecialchars($booking['client_name']) ?></div>
                                  <small class="text-muted"><?= htmlspecialchars($booking['client_email']) ?></small>
                                </div>
                              </div>
                            </td>
                            <td class="date-cell">
                              <i class="bi bi-calendar3 me-2"></i>
                              <?= htmlspecialchars($booking['booking_date']) ?>
                            </td>
                            <td>
                              <span class="badge <?= $booking['status'] === 'completed' ? 'bg-success' : ($booking['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning') ?>">
                                <i class="bi bi-<?= $booking['status'] === 'completed' ? 'check-circle' : ($booking['status'] === 'cancelled' ? 'x-circle' : 'clock') ?> me-1"></i>
                                <?= ucfirst($booking['status']) ?>
                              </span>
                            </td>
                            <td class="actions-cell">
                              <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                  <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu">
                                  <li><a class="dropdown-item" href="provider_bookings.php?id=<?= $booking['id'] ?>">
                                    <i class="bi bi-eye me-2"></i>View Details
                                  </a></li>
                                  <li><a class="dropdown-item" href="provider_bookings.php?edit=<?= $booking['id'] ?>">
                                    <i class="bi bi-pencil me-2"></i>Update Status
                                  </a></li>
                                  <li><hr class="dropdown-divider"></li>
                                  <li><a class="dropdown-item text-primary" href="provider_bookings.php">
                                    <i class="bi bi-list me-2"></i>Manage All
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
                <div class="text-center py-4">
                  <i class="bi bi-calendar-x display-1 text-muted"></i>
                  <p class="lead mt-3">No bookings yet</p>
                  <a href="provider_services.php" class="btn btn-primary">Add Services</a>
                </div>
              <?php endif; ?>
              <div class="text-center mt-4">
                <a href="provider_bookings.php" class="btn btn-primary">View All Bookings</a>
              </div>
            </div>
          </div>

          <!-- Recent Reviews -->
          <div class="col-lg-4">
            <div class="glass-card">
              <h3 class="gradient-text mb-4">
                <i class="bi bi-star me-2"></i>Recent Reviews
              </h3>
              <?php if (mysqli_num_rows($recent_reviews) > 0): ?>
                <?php while($review = mysqli_fetch_assoc($recent_reviews)): ?>
                  <div class="testimonial-glass-card mb-3">
                    <div class="testimonial-rating mb-2">
                      <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?> text-warning"></i>
                      <?php endfor; ?>
                      <span class="ms-2 text-muted"><?= $review['rating'] ?>/5</span>
                    </div>
                    <div class="testimonial-text mb-2">
                      "<?= htmlspecialchars($review['comment']) ?>"
                    </div>
                    <div class="testimonial-author">
                      <i class="bi bi-person-circle me-2"></i>
                      <span class="fw-semibold"><?= htmlspecialchars($review['client_name']) ?></span>
                      <small class="text-muted d-block"><?= htmlspecialchars($review['service_title']) ?></small>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="text-center py-4">
                  <i class="bi bi-star display-1 text-muted"></i>
                  <p class="lead mt-3">No reviews yet</p>
                  <small class="text-muted">Complete bookings to receive reviews</small>
                </div>
              <?php endif; ?>
              <div class="text-center mt-4">
                <a href="provider_reviews.php" class="btn btn-primary">View All Reviews</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

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
  </script>
</body>
</html> 
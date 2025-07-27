<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_client()) { header('Location: /login.php'); exit; }

$user_id = $_SESSION['user_id'];

// Get client statistics
$total_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE client_id = $user_id"))[0];
$active_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE client_id = $user_id AND status IN ('pending', 'confirmed', 'in_progress')"))[0];
$completed_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE client_id = $user_id AND status = 'completed'"))[0];
$total_spent = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(s.price) FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.client_id = $user_id AND b.status = 'completed'"))[0];

// Get recent bookings
$recent_bookings = mysqli_query($conn, "SELECT b.*, s.title AS service_title, s.price, p.name AS provider_name FROM bookings b JOIN services s ON b.service_id = s.id JOIN users p ON b.provider_id = p.id WHERE b.client_id = $user_id ORDER BY b.created_at DESC LIMIT 5");

// Get recent reviews
$recent_reviews = mysqli_query($conn, "SELECT r.*, p.name AS provider_name FROM reviews r JOIN users p ON r.provider_id = p.id WHERE r.reviewer_id = $user_id ORDER BY r.created_at DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client Dashboard - Logistics & Moving Booking System</title>
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
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="dashboard_client.php">
        <i class="bi bi-person me-2"></i>Client<span class="text-gradient-secondary">&</span>Dashboard
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#clientNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="clientNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link active" href="dashboard_client.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="client_services.php">Browse Services</a></li>
          <li class="nav-item"><a class="nav-link" href="client_bookings.php">My Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="client_reviews.php">My Reviews</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">Welcome Back!</h1>
            <p class="lead mb-0">Manage your logistics bookings and track your shipments</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Your Overview</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-calendar-check"></i>
              </div>
              <div class="kpi-number"><?= $total_bookings ?></div>
              <div class="kpi-label">Total Bookings</div>
              <div class="kpi-trend text-success">
                <i class="bi bi-arrow-up"></i> All Time
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-clock"></i>
              </div>
              <div class="kpi-number"><?= $active_bookings ?></div>
              <div class="kpi-label">Active Bookings</div>
              <div class="kpi-trend text-warning">
                <i class="bi bi-arrow-up"></i> In Progress
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="kpi-number"><?= $completed_bookings ?></div>
              <div class="kpi-label">Completed</div>
              <div class="kpi-trend text-success">
                <i class="bi bi-arrow-up"></i> <?= $total_bookings > 0 ? round(($completed_bookings/$total_bookings)*100) : 0 ?>%
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-currency-dollar"></i>
              </div>
              <div class="kpi-number">$<?= number_format($total_spent, 2) ?></div>
              <div class="kpi-label">Total Spent</div>
              <div class="kpi-trend text-info">
                <i class="bi bi-arrow-up"></i> Lifetime
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Quick Actions Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Quick Actions</h2>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="feature-glass-card text-center">
              <div class="feature-icon mx-auto mb-3">
                <i class="bi bi-search"></i>
              </div>
              <h4>Browse Services</h4>
              <p class="text-muted">Find and book logistics services from trusted providers</p>
              <a href="client_services.php" class="btn btn-primary">
                <i class="bi bi-arrow-right me-2"></i>Explore Services
              </a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-glass-card text-center">
              <div class="feature-icon mx-auto mb-3">
                <i class="bi bi-calendar-plus"></i>
              </div>
              <h4>New Booking</h4>
              <p class="text-muted">Create a new booking for your logistics needs</p>
              <a href="client_services.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Book Now
              </a>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-glass-card text-center">
              <div class="feature-icon mx-auto mb-3">
                <i class="bi bi-geo-alt"></i>
              </div>
              <h4>Track Shipments</h4>
              <p class="text-muted">Track your active shipments in real-time</p>
              <a href="tracking.php" class="btn btn-primary">
                <i class="bi bi-location me-2"></i>Track Now
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Recent Activity Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <!-- Recent Bookings -->
          <div class="col-lg-8">
            <div class="glass-card">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="gradient-text mb-0">Recent Bookings</h3>
                <a href="client_bookings.php" class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-arrow-right me-2"></i>View All
                </a>
              </div>
              
              <?php if (mysqli_num_rows($recent_bookings) > 0): ?>
                <div class="table-container">
                  <div class="table-responsive">
                    <table class="table table-dark align-middle">
                      <thead>
                        <tr>
                          <th>Service</th>
                          <th>Provider</th>
                          <th>Status</th>
                          <th>Date</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while($b = mysqli_fetch_assoc($recent_bookings)): ?>
                          <tr>
                            <td>
                              <div class="fw-semibold"><?= htmlspecialchars($b['service_title']) ?></div>
                              <div class="text-sm text-muted">$<?= number_format($b['price'], 2) ?></div>
                            </td>
                            <td>
                              <div class="d-flex align-items-center">
                                <div class="user-avatar me-2">
                                  <i class="bi bi-building"></i>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($b['provider_name']) ?></span>
                              </div>
                            </td>
                            <td>
                              <?php
                                $status_colors = [
                                  'pending' => 'bg-warning',
                                  'confirmed' => 'bg-info',
                                  'in_progress' => 'bg-primary',
                                  'completed' => 'bg-success',
                                  'cancelled' => 'bg-danger'
                                ];
                                $status_icons = [
                                  'pending' => 'clock',
                                  'confirmed' => 'check-circle',
                                  'in_progress' => 'arrow-clockwise',
                                  'completed' => 'check-circle-fill',
                                  'cancelled' => 'x-circle'
                                ];
                              ?>
                              <span class="badge <?= $status_colors[$b['status']] ?? 'bg-secondary' ?>">
                                <i class="bi bi-<?= $status_icons[$b['status']] ?? 'question' ?> me-1"></i>
                                <?= ucfirst(str_replace('_', ' ', $b['status'])) ?>
                              </span>
                            </td>
                            <td class="date-cell"><?= date('M d, Y', strtotime($b['booking_date'])) ?></td>
                            <td class="actions-cell">
                              <div class="btn-group">
                                <a href="tracking.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary">
                                  <i class="bi bi-geo-alt"></i> Track
                                </a>
                                <a href="client_reviews.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-warning">
                                  <i class="bi bi-star"></i> Review
                                </a>
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
                  <h4 class="mt-3">No Bookings Yet</h4>
                  <p class="text-muted">Start by browsing our services and making your first booking</p>
                  <a href="client_services.php" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i>Browse Services
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Recent Reviews -->
          <div class="col-lg-4">
            <div class="glass-card">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="gradient-text mb-0">Recent Reviews</h3>
                <a href="client_reviews.php" class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-arrow-right me-2"></i>View All
                </a>
              </div>
              
              <?php if (mysqli_num_rows($recent_reviews) > 0): ?>
                <?php while($r = mysqli_fetch_assoc($recent_reviews)): ?>
                  <div class="testimonial-glass-card mb-3">
                    <div class="d-flex align-items-center mb-2">
                      <div class="user-avatar me-2">
                        <i class="bi bi-person"></i>
                      </div>
                      <div>
                        <div class="fw-semibold"><?= htmlspecialchars($r['provider_name']) ?></div>
                        <div class="testimonial-rating">
                          <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?>"></i>
                          <?php endfor; ?>
                        </div>
                      </div>
                    </div>
                    <div class="testimonial-text">
                      "<?= htmlspecialchars(substr($r['comment'], 0, 100)) ?><?= strlen($r['comment']) > 100 ? '...' : '' ?>"
                    </div>
                    <div class="text-sm text-muted">
                      <?= date('M d, Y', strtotime($r['created_at'])) ?>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="text-center py-4">
                  <i class="bi bi-star display-1 text-muted"></i>
                  <h4 class="mt-3">No Reviews Yet</h4>
                  <p class="text-muted">Share your experience by reviewing completed bookings</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

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
  </script>
</body>
</html> 
<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
require_once '../includes/auth.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Get statistics
$stats = mysqli_query($conn, "
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role != 'admin') as total_users,
        (SELECT COUNT(*) FROM users WHERE role = 'provider') as total_providers,
        (SELECT COUNT(*) FROM users WHERE role = 'client') as total_clients,
        (SELECT COUNT(*) FROM services) as total_services,
        (SELECT COUNT(*) FROM bookings) as total_bookings,
        (SELECT COUNT(*) FROM reviews) as total_reviews
");

$stats_data = mysqli_fetch_assoc($stats);

// Get recent activities
$recent_bookings = mysqli_query($conn, "
    SELECT b.*, s.title as service_title, u.name as client_name, p.name as provider_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.client_id = u.id
    JOIN users p ON b.provider_id = p.id
    ORDER BY b.created_at DESC
    LIMIT 5
");

$recent_users = mysqli_query($conn, "
    SELECT * FROM users 
    WHERE role != 'admin' 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Get revenue data (simulated)
$total_revenue = 0;
$recent_revenue = mysqli_query($conn, "
    SELECT SUM(s.price) as revenue
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.status = 'completed'
");
if ($revenue_data = mysqli_fetch_assoc($recent_revenue)) {
    $total_revenue = $revenue_data['revenue'] ?: 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Logistics & Moving Booking System</title>
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
          <li class="nav-item"><a class="nav-link active" href="admin.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_users.php">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_reviews.php">Reviews</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">Admin Dashboard</h1>
            <p class="lead mb-0">Manage your logistics platform with comprehensive tools and insights</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Platform Overview</h2>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_users'] ?></div>
          <div class="kpi-label">Total Users</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +15% this month
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-truck"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_providers'] ?></div>
          <div class="kpi-label">Service Providers</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +8% this month
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-person-check"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_clients'] ?></div>
          <div class="kpi-label">Clients</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +12% this month
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-gear-fill"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_services'] ?></div>
          <div class="kpi-label">Services</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +20% this month
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-calendar-check-fill"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_bookings'] ?></div>
          <div class="kpi-label">Bookings</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +25% this month
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-star-fill"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_reviews'] ?></div>
          <div class="kpi-label">Reviews</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +18% this month
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Revenue Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Revenue Overview</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="glass-card">
          <div class="row g-4">
            <div class="col-md-6">
              <div class="kpi-card text-center">
                <div class="kpi-icon mb-3">
                  <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="kpi-number">$<?= number_format($total_revenue, 2) ?></div>
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-trend text-success">
                  <i class="bi bi-arrow-up"></i> +30% this month
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="kpi-card text-center">
                <div class="kpi-icon mb-3">
                  <i class="bi bi-graph-up"></i>
                </div>
                <div class="kpi-number">$<?= number_format($total_revenue * 0.15, 2) ?></div>
                <div class="kpi-label">Platform Fee (15%)</div>
                <div class="kpi-trend text-info">
                  <i class="bi bi-info-circle"></i> Commission earned
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Management Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Platform Management</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card">
          <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                <i class="bi bi-people me-2"></i>Users
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab">
                <i class="bi bi-gear me-2"></i>Services
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">
                <i class="bi bi-calendar-check me-2"></i>Bookings
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                <i class="bi bi-star me-2"></i>Reviews
              </button>
            </li>
          </ul>
          
          <div class="tab-content" id="adminTabsContent">
            <!-- Users Tab -->
            <div class="tab-pane fade show active" id="users" role="tabpanel">
              <div class="table-container">
                <div class="table-responsive">
                  <table class="table table-dark align-middle">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while($user = mysqli_fetch_assoc($recent_users)): ?>
                        <tr>
                          <td><?= $user['id'] ?></td>
                          <td><?= htmlspecialchars($user['name']) ?></td>
                          <td><?= htmlspecialchars($user['email']) ?></td>
                          <td><span class="badge <?= $user['role'] === 'provider' ? 'bg-info' : 'bg-secondary' ?>"><?= ucfirst($user['role']) ?></span></td>
                          <td><span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-warning' ?>"><?= ucfirst($user['status']) ?></span></td>
                          <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                          <td>
                            <a href="admin_users.php" class="btn btn-sm btn-outline-primary">Manage</a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="text-center mt-4">
                <a href="admin_users.php" class="btn btn-primary">View All Users</a>
              </div>
            </div>

            <!-- Services Tab -->
            <div class="tab-pane fade" id="services" role="tabpanel">
              <div class="table-container">
                <div class="table-responsive">
                  <table class="table table-dark align-middle">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Provider</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      $recent_services = mysqli_query($conn, "
                        SELECT s.*, u.name as provider_name 
                        FROM services s 
                        JOIN users u ON s.provider_id = u.id 
                        ORDER BY s.created_at DESC 
                        LIMIT 5
                      ");
                      while($service = mysqli_fetch_assoc($recent_services)): 
                      ?>
                        <tr>
                          <td><?= $service['id'] ?></td>
                          <td><?= htmlspecialchars($service['title']) ?></td>
                          <td><?= htmlspecialchars($service['provider_name']) ?></td>
                          <td><?= htmlspecialchars($service['category']) ?></td>
                          <td>$<?= number_format($service['price'], 2) ?></td>
                          <td><span class="badge <?= $service['status'] === 'active' ? 'bg-success' : 'bg-warning' ?>"><?= ucfirst($service['status']) ?></span></td>
                          <td>
                            <a href="admin_services.php" class="btn btn-sm btn-outline-primary">Manage</a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="text-center mt-4">
                <a href="admin_services.php" class="btn btn-primary">View All Services</a>
              </div>
            </div>

            <!-- Bookings Tab -->
            <div class="tab-pane fade" id="bookings" role="tabpanel">
              <div class="table-container">
                <div class="table-responsive">
                  <table class="table table-dark align-middle">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Service</th>
                        <th>Client</th>
                        <th>Provider</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                        <tr>
                          <td><?= $booking['id'] ?></td>
                          <td><?= htmlspecialchars($booking['service_title']) ?></td>
                          <td><?= htmlspecialchars($booking['client_name']) ?></td>
                          <td><?= htmlspecialchars($booking['provider_name']) ?></td>
                          <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                          <td><span class="badge <?= $booking['status'] === 'completed' ? 'bg-success' : ($booking['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning') ?>"><?= ucfirst($booking['status']) ?></span></td>
                          <td>
                            <a href="admin_bookings.php" class="btn btn-sm btn-outline-primary">Manage</a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="text-center mt-4">
                <a href="admin_bookings.php" class="btn btn-primary">View All Bookings</a>
              </div>
            </div>

            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
              <div class="table-container">
                <div class="table-responsive">
                  <table class="table table-dark align-middle">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Service</th>
                        <th>Client</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                      $recent_reviews = mysqli_query($conn, "
                        SELECT r.*, s.title as service_title, u.name as client_name 
                        FROM reviews r 
                        JOIN services s ON r.service_id = s.id 
                        JOIN users u ON r.client_id = u.id 
                        ORDER BY r.created_at DESC 
                        LIMIT 5
                      ");
                      while($review = mysqli_fetch_assoc($recent_reviews)): 
                      ?>
                        <tr>
                          <td><?= $review['id'] ?></td>
                          <td><?= htmlspecialchars($review['service_title']) ?></td>
                          <td><?= htmlspecialchars($review['client_name']) ?></td>
                          <td>
                            <div class="text-warning">
                              <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?>"></i>
                              <?php endfor; ?>
                            </div>
                          </td>
                          <td><?= htmlspecialchars(substr($review['comment'], 0, 50)) ?>...</td>
                          <td><?= date('Y-m-d', strtotime($review['created_at'])) ?></td>
                          <td>
                            <a href="admin_reviews.php" class="btn btn-sm btn-outline-primary">Manage</a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="text-center mt-4">
                <a href="admin_reviews.php" class="btn btn-primary">View All Reviews</a>
              </div>
            </div>
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
              <div class="feature-icon mb-3">
                <i class="bi bi-people"></i>
              </div>
              <h4 class="gradient-text mb-3">Manage Users</h4>
              <p>View, edit, and manage all platform users including clients and providers.</p>
              <a href="admin_users.php" class="btn btn-outline-primary">Go to Users</a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-glass-card text-center">
              <div class="feature-icon mb-3">
                <i class="bi bi-gear"></i>
              </div>
              <h4 class="gradient-text mb-3">Manage Services</h4>
              <p>Oversee all services offered by providers on the platform.</p>
              <a href="admin_services.php" class="btn btn-outline-primary">Go to Services</a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-glass-card text-center">
              <div class="feature-icon mb-3">
                <i class="bi bi-calendar-check"></i>
              </div>
              <h4 class="gradient-text mb-3">Manage Bookings</h4>
              <p>Monitor and manage all bookings and transactions on the platform.</p>
              <a href="admin_bookings.php" class="btn btn-outline-primary">Go to Bookings</a>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="feature-glass-card text-center">
              <div class="feature-icon mb-3">
                <i class="bi bi-star"></i>
              </div>
              <h4 class="gradient-text mb-3">Manage Reviews</h4>
              <p>Oversee customer reviews and maintain platform quality standards.</p>
              <a href="admin_reviews.php" class="btn btn-outline-primary">Go to Reviews</a>
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
</body>
</html> 
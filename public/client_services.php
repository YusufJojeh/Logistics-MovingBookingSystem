<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
require_once '../includes/auth.php';

// Check if user is logged in and is client
if (!is_logged_in() || !is_client()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
$client_id = $user['id'];

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_service'])) {
    $service_id = intval($_POST['service_id']);
    $booking_date = $_POST['booking_date'];
    $scheduled_time = $_POST['scheduled_time'];
    $details = trim($_POST['details']);
    
    if (empty($booking_date)) {
        $error = "Please select a booking date.";
    } else {
        // Get service details
        $service_query = mysqli_query($conn, "SELECT * FROM services WHERE id = $service_id AND status = 'active'");
        if ($service = mysqli_fetch_assoc($service_query)) {
            $provider_id = $service['provider_id'];
            
            // Check if booking already exists
            $existing = mysqli_query($conn, "SELECT id FROM bookings WHERE service_id = $service_id AND client_id = $client_id AND booking_date = '$booking_date'");
            if (mysqli_num_rows($existing) > 0) {
                $error = "You already have a booking for this service on this date.";
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO bookings (service_id, client_id, provider_id, booking_date, scheduled_time, details) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'iiisss', $service_id, $client_id, $provider_id, $booking_date, $scheduled_time, $details);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Service booked successfully!";
                } else {
                    $error = "Failed to book service. Please try again.";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "Service not found or not available.";
        }
    }
}

// Get service statistics
$stats = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_services,
        COUNT(DISTINCT provider_id) as total_providers,
        AVG(price) as avg_price,
        MIN(price) as min_price,
        MAX(price) as max_price,
        COUNT(DISTINCT type) as service_types
    FROM services 
    WHERE status = 'active'
");

$stats_data = mysqli_fetch_assoc($stats);

// Search and filter
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$city_filter = $_GET['city'] ?? '';

$where_conditions = ["s.status = 'active'"];
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where_conditions[] = "(s.title LIKE '%$search_escaped%' OR s.description LIKE '%$search_escaped%' OR u.name LIKE '%$search_escaped%')";
}
if (!empty($type_filter)) {
    $type_escaped = mysqli_real_escape_string($conn, $type_filter);
    $where_conditions[] = "s.type = '$type_escaped'";
}
if (!empty($city_filter)) {
    $city_escaped = mysqli_real_escape_string($conn, $city_filter);
    $where_conditions[] = "(s.city_from LIKE '%$city_escaped%' OR s.city_to LIKE '%$city_escaped%')";
}

$where_clause = implode(' AND ', $where_conditions);

// Get services with provider info
$services = mysqli_query($conn, "
    SELECT s.*, u.name as provider_name, u.rating as provider_rating,
           (SELECT COUNT(*) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.service_id = s.id) as review_count,
           (SELECT AVG(r.rating) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.service_id = s.id) as avg_rating
    FROM services s
    JOIN users u ON s.provider_id = u.id
    WHERE $where_clause
    ORDER BY s.created_at DESC
");

// Get unique types and cities for filters
$types = mysqli_query($conn, "SELECT DISTINCT type FROM services WHERE status = 'active' AND type IS NOT NULL");
$cities = mysqli_query($conn, "SELECT DISTINCT city_from FROM services WHERE status = 'active' AND city_from IS NOT NULL UNION SELECT DISTINCT city_to FROM services WHERE status = 'active' AND city_to IS NOT NULL");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Services - Client Dashboard</title>
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
        <i class="bi bi-person-circle me-2"></i>Client<span class="text-gradient-secondary">&</span>Dashboard
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#clientNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="clientNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="dashboard_client.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" href="client_services.php">Browse Services</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">Browse Services</h1>
            <p class="lead mb-0">Find the perfect logistics and moving services from trusted providers</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Service Overview</h2>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total available services">
          <div class="kpi-icon">
            <i class="bi bi-box-seam"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_services'] ?></div>
          <div class="kpi-label">Available Services</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Active
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total service providers">
          <div class="kpi-icon">
            <i class="bi bi-building"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_providers'] ?></div>
          <div class="kpi-label">Providers</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Trusted
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Average service price">
          <div class="kpi-icon">
            <i class="bi bi-currency-dollar"></i>
          </div>
          <div class="kpi-number">$<?= number_format($stats_data['avg_price'] ?? 0, 0) ?></div>
          <div class="kpi-label">Avg Price</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Competitive
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Lowest service price">
          <div class="kpi-icon">
            <i class="bi bi-tags"></i>
          </div>
          <div class="kpi-number">$<?= number_format($stats_data['min_price'] ?? 0, 0) ?></div>
          <div class="kpi-label">Starting From</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-down"></i> Affordable
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Highest service price">
          <div class="kpi-icon">
            <i class="bi bi-graph-up"></i>
          </div>
          <div class="kpi-number">$<?= number_format($stats_data['max_price'] ?? 0, 0) ?></div>
          <div class="kpi-label">Up To</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Premium
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Different service types available">
          <div class="kpi-icon">
            <i class="bi bi-collection"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['service_types'] ?></div>
          <div class="kpi-label">Service Types</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Varied
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Search and Filter Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="gradient-text mb-0">
              <i class="bi bi-search me-2"></i>Find Your Perfect Service
            </h2>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="clearFilters()">
                <i class="bi bi-arrow-clockwise me-1"></i>Clear Filters
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

          <!-- Search and Filter Form -->
          <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
              <label for="search" class="form-label fw-semibold">Search Services</label>
              <div class="input-group">
                <span class="input-group-text">
                  <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title, description, or provider...">
              </div>
            </div>
            <div class="col-md-3">
              <label for="type" class="form-label fw-semibold">Service Type</label>
              <select class="form-select" id="type" name="type">
                <option value="">All Types</option>
                <?php while($type = mysqli_fetch_assoc($types)): ?>
                  <option value="<?= htmlspecialchars($type['type']) ?>" <?= $type_filter === $type['type'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(ucfirst($type['type'])) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label for="city" class="form-label fw-semibold">City</label>
              <select class="form-select" id="city" name="city">
                <option value="">All Cities</option>
                <?php while($city = mysqli_fetch_assoc($cities)): ?>
                  <option value="<?= htmlspecialchars($city['city_from']) ?>" <?= $city_filter === $city['city_from'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($city['city_from']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-funnel me-2"></i>Filter
                </button>
              </div>
            </div>
          </form>

          <!-- Services Table -->
          <div class="mt-5">
            <h3 class="gradient-text mb-4">
              <i class="bi bi-list-ul me-2"></i>Available Services
              <span class="badge bg-primary ms-2"><?= mysqli_num_rows($services) ?> found</span>
            </h3>
            <?php if (mysqli_num_rows($services) > 0): ?>
              <div class="table-container">
                <div class="table-responsive">
                  <table class="table table-dark align-middle">
                    <thead>
                      <tr>
                        <th>Service Details</th>
                        <th>Provider</th>
                        <th>Type</th>
                        <th>Route</th>
                        <th>Price</th>
                        <th>Rating</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while($service = mysqli_fetch_assoc($services)): ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="user-avatar me-3">
                                <i class="bi bi-box-seam"></i>
                              </div>
                              <div>
                                <div class="fw-semibold"><?= htmlspecialchars($service['title']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars(substr($service['description'], 0, 80)) ?>...</small>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="user-avatar me-3">
                                <i class="bi bi-building"></i>
                              </div>
                              <div>
                                <div class="fw-semibold"><?= htmlspecialchars($service['provider_name']) ?></div>
                                <div class="text-warning">
                                  <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $service['provider_rating'] ? '-fill' : '' ?>"></i>
                                  <?php endfor; ?>
                                  <small class="text-muted ms-1">(<?= number_format($service['provider_rating'], 1) ?>)</small>
                                </div>
                              </div>
                            </div>
                          </td>
                          <td>
                            <span class="badge bg-info">
                              <i class="bi bi-tag me-1"></i><?= htmlspecialchars(ucfirst($service['type'])) ?>
                            </span>
                          </td>
                          <td class="date-cell">
                            <div>
                              <div class="fw-semibold"><?= htmlspecialchars($service['city_from']) ?></div>
                              <i class="bi bi-arrow-right text-muted"></i>
                              <div class="fw-semibold"><?= htmlspecialchars($service['city_to']) ?></div>
                            </div>
                          </td>
                          <td class="number-cell">
                            <div class="fw-bold text-success">$<?= number_format($service['price'], 2) ?></div>
                          </td>
                          <td>
                            <?php if ($service['avg_rating']): ?>
                              <div class="text-warning">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                  <i class="bi bi-star<?= $i <= $service['avg_rating'] ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                              </div>
                              <small class="text-muted">(<?= $service['review_count'] ?> reviews)</small>
                            <?php else: ?>
                              <small class="text-muted">No reviews yet</small>
                            <?php endif; ?>
                          </td>
                          <td class="actions-cell">
                            <div class="btn-group" role="group">
                              <button class="btn btn-outline-primary btn-sm" onclick="viewServiceDetails(<?= $service['id'] ?>, '<?= htmlspecialchars($service['title']) ?>', '<?= htmlspecialchars($service['type']) ?>', '<?= htmlspecialchars($service['description']) ?>', <?= $service['price'] ?>, '<?= htmlspecialchars($service['city_from']) ?>', '<?= htmlspecialchars($service['city_to']) ?>', '<?= htmlspecialchars($service['provider_name']) ?>', '<?= $service['created_at'] ?>')">
                                <i class="bi bi-eye me-1"></i>View Details
                              </button>
                              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bookingModal<?= $service['id'] ?>">
                                <i class="bi bi-calendar-check me-1"></i>Book Now
                              </button>
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
                <i class="bi bi-search display-1 text-muted"></i>
                <h4 class="mt-3 mb-2">No Services Found</h4>
                <p class="text-muted mb-4">Try adjusting your search criteria or check back later for new services.</p>
                <a href="client_services.php" class="btn btn-primary">
                  <i class="bi bi-arrow-clockwise me-2"></i>Clear Filters
                </a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Booking Modals -->
  <?php 
  mysqli_data_seek($services, 0); // Reset pointer to beginning
  while($service = mysqli_fetch_assoc($services)): 
  ?>
    <div class="modal fade" id="bookingModal<?= $service['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title gradient-text">
              <i class="bi bi-calendar-check me-2"></i>Book Service
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form method="POST">
            <div class="modal-body">
              <input type="hidden" name="book_service" value="1">
              <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
              
              <div class="mb-3">
                <label class="form-label fw-semibold">Service</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($service['title']) ?>" readonly>
              </div>
              
              <div class="mb-3">
                <label class="form-label fw-semibold">Provider</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($service['provider_name']) ?>" readonly>
              </div>
              
              <div class="mb-3">
                <label class="form-label fw-semibold">Price</label>
                <input type="text" class="form-control" value="$<?= number_format($service['price'], 2) ?>" readonly>
              </div>
              
              <div class="mb-3">
                <label for="booking_date" class="form-label fw-semibold">Booking Date *</label>
                <input type="date" class="form-control" id="booking_date" name="booking_date" required min="<?= date('Y-m-d') ?>">
              </div>
              
              <div class="mb-3">
                <label for="scheduled_time" class="form-label fw-semibold">Preferred Time</label>
                <input type="datetime-local" class="form-control" id="scheduled_time" name="scheduled_time">
              </div>
              
              <div class="mb-3">
                <label for="details" class="form-label fw-semibold">Additional Details</label>
                <textarea class="form-control" id="details" name="details" rows="3" placeholder="Any special requirements or notes..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-calendar-check me-2"></i>Confirm Booking
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endwhile; ?>

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

    // Clear all filters
    function viewServiceDetails(id, title, type, description, price, city_from, city_to, provider_name, created_at) {
      // Create a simple modal for viewing service details
      const modal = document.createElement('div');
      modal.className = 'modal fade';
      modal.id = 'viewServiceModal';
      modal.innerHTML = `
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title gradient-text">
                <i class="bi bi-box-seam me-2"></i>Service Details
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Service ID</label>
                    <input type="text" class="form-control" value="${id}" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Service Title</label>
                    <input type="text" class="form-control" value="${title}" readonly>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Service Type</label>
                    <input type="text" class="form-control" value="${type}" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Provider</label>
                    <input type="text" class="form-control" value="${provider_name}" readonly>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Price</label>
                    <input type="text" class="form-control" value="$${parseFloat(price).toFixed(2)}" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Created Date</label>
                    <input type="text" class="form-control" value="${new Date(created_at).toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric'
                    })}" readonly>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">From City</label>
                    <input type="text" class="form-control" value="${city_from || 'Not specified'}" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">To City</label>
                    <input type="text" class="form-control" value="${city_to || 'Not specified'}" readonly>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Description</label>
                <textarea class="form-control" rows="3" readonly>${description || 'No description provided'}</textarea>
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

    function clearFilters() {
      window.location.href = 'client_services.php';
    }
  </script>
</body>
</html>
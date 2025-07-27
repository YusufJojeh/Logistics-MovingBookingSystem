<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_client()) { header('Location: /login.php'); exit; }
$user = current_user();
$client_id = $user['id'];

// Handle cancel/reschedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $id = intval($_POST['booking_id']);
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $stmt = mysqli_prepare($conn, "UPDATE bookings SET status='cancelled' WHERE id=? AND client_id=? AND status IN ('pending','confirmed','in_progress')");
        mysqli_stmt_bind_param($stmt, 'ii', $id, $client_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['action']) && $_POST['action'] === 'reschedule' && isset($_POST['new_date'])) {
        $new_date = $_POST['new_date'];
        $stmt = mysqli_prepare($conn, "UPDATE bookings SET booking_date=? WHERE id=? AND client_id=? AND status IN ('pending','confirmed')");
        mysqli_stmt_bind_param($stmt, 'sii', $new_date, $id, $client_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: client_bookings.php');
    exit;
}

// Get booking statistics
$stats = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as total_completed
    FROM bookings 
    WHERE client_id = $client_id
");

$stats_data = mysqli_fetch_assoc($stats);

// Fetch own bookings (with service cities)
$bookings = mysqli_query($conn, "SELECT b.*, s.title AS service_title, s.city_from, s.city_to, u.name AS provider_name FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.provider_id = u.id WHERE b.client_id = $client_id ORDER BY b.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - Client Dashboard</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    #map { width: 100%; height: 350px; border-radius: 1rem; }
  </style>
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
          <li class="nav-item"><a class="nav-link active" href="client_bookings.php">My Bookings</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">My Bookings</h1>
            <p class="lead mb-0">Track, manage, and review all your logistics and moving bookings</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Booking Overview</h2>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total bookings made">
          <div class="kpi-icon">
            <i class="bi bi-calendar-check"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_bookings'] ?></div>
          <div class="kpi-label">Total Bookings</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Active
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
        <div class="kpi-card" data-tooltip="Pending bookings">
          <div class="kpi-icon">
            <i class="bi bi-clock"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['pending_bookings'] ?></div>
          <div class="kpi-label">Pending</div>
          <div class="kpi-trend neutral">
            <i class="bi bi-dash"></i> Waiting
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Cancelled bookings">
          <div class="kpi-icon">
            <i class="bi bi-x-circle"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['cancelled_bookings'] ?></div>
          <div class="kpi-label">Cancelled</div>
          <div class="kpi-trend negative">
            <i class="bi bi-arrow-down"></i> <?= $stats_data['total_bookings'] > 0 ? round(($stats_data['cancelled_bookings'] / $stats_data['total_bookings']) * 100) : 0 ?>%
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
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Average booking value">
          <div class="kpi-icon">
            <i class="bi bi-graph-up"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['completed_bookings'] > 0 ? round(($stats_data['completed_bookings'] / $stats_data['total_bookings']) * 100) : 0 ?>%</div>
          <div class="kpi-label">Success Rate</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Completion
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Bookings Management Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Booking Management</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="gradient-text mb-0">
              <i class="bi bi-list-ul me-2"></i>All Bookings
            </h3>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-funnel me-1"></i>Filter
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="filterBookings('all')">All Bookings</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterBookings('pending')">Pending</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterBookings('completed')">Completed</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterBookings('cancelled')">Cancelled</a></li>
              </ul>
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
          
          <?php if (mysqli_num_rows($bookings) > 0): ?>
            <div class="table-container">
              <div class="table-responsive">
                <table class="table table-dark align-middle">
                  <thead>
                    <tr>
                      <th>Booking ID</th>
                      <th>Service</th>
                      <th>Provider</th>
                      <th>Route</th>
                      <th>Date</th>
                      <th>Status</th>

                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php while($b = mysqli_fetch_assoc($bookings)): ?>
                    <tr data-status="<?= $b['status'] ?>">
                      <td class="number-cell">
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-3">
                            <i class="bi bi-hash"></i>
                          </div>
                          <div>
                            <div class="fw-semibold">#<?= $b['id'] ?></div>
                            <small class="text-muted"><?= date('M d, Y', strtotime($b['created_at'])) ?></small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-3">
                            <i class="bi bi-box-seam"></i>
                          </div>
                          <div>
                            <div class="fw-semibold"><?= htmlspecialchars($b['service_title']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($b['details']) ?></small>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-3">
                            <i class="bi bi-building"></i>
                          </div>
                          <div>
                            <div class="fw-semibold"><?= htmlspecialchars($b['provider_name']) ?></div>
                            <small class="text-muted">Provider</small>
                          </div>
                        </div>
                      </td>
                      <td class="date-cell">
                        <div>
                          <div class="fw-semibold"><?= htmlspecialchars($b['city_from']) ?></div>
                          <i class="bi bi-arrow-right text-muted"></i>
                          <div class="fw-semibold"><?= htmlspecialchars($b['city_to']) ?></div>
                        </div>
                      </td>
                      <td class="date-cell">
                        <i class="bi bi-calendar3 me-2"></i>
                        <?= htmlspecialchars($b['booking_date']) ?>
                      </td>
                      <td>
                        <span class="badge <?= $b['status']==='completed'?'bg-success':($b['status']==='cancelled'?'bg-danger':($b['status']==='pending'?'bg-warning':'bg-info')) ?>">
                          <i class="bi bi-<?= $b['status']==='completed'?'check-circle':($b['status']==='cancelled'?'x-circle':($b['status']==='pending'?'clock':'arrow-clockwise')) ?> me-1"></i>
                          <?= ucfirst($b['status']) ?>
                        </span>
                      </td>

                      <td class="actions-cell">
                        <div class="btn-group" role="group">
                          <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="viewBookingDetails(<?= $b['id'] ?>, '<?= htmlspecialchars($b['service_title']) ?>', '<?= htmlspecialchars($b['provider_name']) ?>', '<?= htmlspecialchars($b['details']) ?>', '<?= $b['booking_date'] ?>', '<?= $b['status'] ?>', '<?= $b['created_at'] ?>', '<?= htmlspecialchars($b['city_from']) ?>', '<?= htmlspecialchars($b['city_to']) ?>')">
                              <i class="bi bi-eye me-2"></i>View Details
                            </a></li>
                            <?php if($b['status']!=='cancelled'): ?>
                              <li><a class="dropdown-item" href="tracking.php?tracking=<?= urlencode($b['id']) ?>" target="_blank">
                                <i class="bi bi-geo-alt me-2"></i>Track Shipment
                              </a></li>
                            <?php endif; ?>
                            <?php if(in_array($b['status'],['pending','confirmed'])): ?>
                              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#resModal<?= $b['id'] ?>">
                                <i class="bi bi-calendar-event me-2"></i>Reschedule
                              </a></li>
                            <?php endif; ?>
                            <?php if(in_array($b['status'],['pending','confirmed','in_progress'])): ?>
                              <li><hr class="dropdown-divider"></li>
                              <li><a class="dropdown-item text-danger" href="#" onclick="cancelBooking(<?= $b['id'] ?>)">
                                <i class="bi bi-x-circle me-2"></i>Cancel Booking
                              </a></li>
                            <?php endif; ?>
                          </ul>
                        </div>
                      </td>
                    </tr>
                    
                    <!-- Reschedule Modal -->
                    <?php if(in_array($b['status'],['pending','confirmed'])): ?>
                      <div class="modal fade" id="resModal<?= $b['id'] ?>" tabindex="-1" aria-labelledby="resModalLabel<?= $b['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <form method="POST">
                              <div class="modal-header">
                                <h5 class="modal-title gradient-text" id="resModalLabel<?= $b['id'] ?>">
                                  <i class="bi bi-calendar-event me-2"></i>Reschedule Booking
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <div class="mb-3">
                                  <label class="form-label fw-bold">Current Date</label>
                                  <input type="text" class="form-control" value="<?= htmlspecialchars($b['booking_date']) ?>" readonly>
                                </div>
                                <div class="mb-3">
                                  <label class="form-label fw-bold">New Date</label>
                                  <input type="date" class="form-control" name="new_date" required min="<?= date('Y-m-d') ?>">
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="action" value="reschedule" class="btn btn-success">
                                  <i class="bi bi-calendar-check me-2"></i>Reschedule
                                </button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center py-5">
              <i class="bi bi-calendar-x display-1 text-muted"></i>
              <h4 class="mt-3 mb-2">No Bookings Yet</h4>
              <p class="text-muted mb-4">Start by browsing our services and making your first booking</p>
              <a href="client_services.php" class="btn btn-primary">
                <i class="bi bi-search me-2"></i>Browse Services
              </a>
            </div>
          <?php endif; ?>
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

    // Filter bookings by status
    function filterBookings(status) {
      const rows = document.querySelectorAll('tbody tr');
      rows.forEach(row => {
        if (status === 'all' || row.getAttribute('data-status') === status) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // View booking details
    function viewBookingDetails(id, service_title, provider_name, details, booking_date, status, created_at, city_from, city_to) {
      // Create a simple modal for viewing booking details
      const modal = document.createElement('div');
      modal.className = 'modal fade';
      modal.id = 'viewBookingModal';
      modal.innerHTML = `
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title gradient-text">
                <i class="bi bi-calendar-check me-2"></i>Booking Details
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Booking ID</label>
                    <input type="text" class="form-control" value="${id}" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <input type="text" class="form-control" value="${status.charAt(0).toUpperCase() + status.slice(1)}" readonly>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Service</label>
                    <input type="text" class="form-control" value="${service_title}" readonly>
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
                    <label class="form-label fw-bold">From City</label>
                    <input type="text" class="form-control" value="${city_from}" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">To City</label>
                    <input type="text" class="form-control" value="${city_to}" readonly>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Booking Date</label>
                    <input type="text" class="form-control" value="${new Date(booking_date).toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric'
                    })}" readonly>
                  </div>
                </div>
                <div class="col-md-6">
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
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Details</label>
                <textarea class="form-control" rows="3" readonly>${details || 'No details provided'}</textarea>
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

    // Cancel booking confirmation
    function cancelBooking(bookingId) {
      if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
          <input type="hidden" name="booking_id" value="${bookingId}">
          <input type="hidden" name="action" value="cancel">
        `;
        document.body.appendChild(form);
        form.submit();
      }
    }
  </script>
</body>
</html> 
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['booking_id'] ?? 0);
    if ($id) {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM bookings WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'status' && isset($_POST['new_status'])) {
            $status = $_POST['new_status'];
            $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $status, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $details = trim($_POST['details']);
            $booking_date = $_POST['booking_date'];
            $status = $_POST['status'];
            
            if ($details && $booking_date) {
                $stmt = mysqli_prepare($conn, "UPDATE bookings SET details=?, booking_date=?, status=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'sssi', $details, $booking_date, $status, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'add') {
        $client_id = intval($_POST['client_id']);
        $provider_id = intval($_POST['provider_id']);
        $service_id = intval($_POST['service_id']);
        $details = trim($_POST['details']);
        $booking_date = $_POST['booking_date'];
        $status = $_POST['status'];
        
        if ($client_id && $provider_id && $service_id && $details && $booking_date) {
            $stmt = mysqli_prepare($conn, "INSERT INTO bookings (client_id, provider_id, service_id, details, booking_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, 'iiisss', $client_id, $provider_id, $service_id, $details, $booking_date, $status);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin_bookings.php');
    exit;
}

// Get booking statistics
$total_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings"))[0];
$pending_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='pending'"))[0];
$completed_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='completed'"))[0];
$total_revenue = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(s.price) FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.status='completed'"))[0];

// Search/filter
$where = "WHERE 1";
$search = trim($_GET['search'] ?? '');
$provider_id = intval($_GET['provider_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
if ($search) {
    $where .= " AND (b.details LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR b.id LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR u.name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR p.name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
if ($provider_id) {
    $where .= " AND b.provider_id = $provider_id";
}
if ($status && in_array($status, ['pending','confirmed','in_progress','completed','cancelled'])) {
    $where .= " AND b.status = '" . mysqli_real_escape_string($conn, $status) . "'";
}
$bookings = mysqli_query($conn, "SELECT b.*, u.name AS client_name, p.name AS provider_name, s.title AS service_title, s.price FROM bookings b JOIN users u ON b.client_id = u.id JOIN users p ON b.provider_id = p.id JOIN services s ON b.service_id = s.id $where ORDER BY b.created_at DESC");

// Get data for dropdowns
$clients = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'client' AND status = 'active' ORDER BY name");
$providers = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'provider' AND status = 'active' ORDER BY name");
$services = mysqli_query($conn, "SELECT id, title FROM services WHERE status = 'active' ORDER BY title");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Booking Management - Logistics & Moving Booking System</title>
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
          <li class="nav-item"><a class="nav-link active" href="admin_bookings.php">Bookings</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">Booking Management</h1>
            <p class="lead mb-0">Monitor and manage all logistics bookings across the platform</p>
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
              <div class="kpi-number"><?= $pending_bookings ?></div>
              <div class="kpi-label">Pending</div>
              <div class="kpi-trend text-warning">
                <i class="bi bi-arrow-up"></i> Awaiting
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
                <i class="bi bi-arrow-up"></i> <?= round(($completed_bookings/$total_bookings)*100) ?>%
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-currency-dollar"></i>
              </div>
              <div class="kpi-number">$<?= number_format($total_revenue, 2) ?></div>
              <div class="kpi-label">Revenue</div>
              <div class="kpi-trend text-info">
                <i class="bi bi-arrow-up"></i> Generated
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Booking Management Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="gradient-text mb-0">Booking List</h2>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" onclick="exportBookings()">
                <i class="bi bi-download me-2"></i>Export
              </button>
              <button class="btn btn-primary" onclick="showAddBookingModal()">
                <i class="bi bi-plus-circle me-2"></i>Add Booking
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
                <input type="text" name="search" class="form-control search-input" placeholder="Search by details, ID, client, or provider" value="<?= htmlspecialchars($search) ?>">
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
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
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
                    <th data-sort>Service</th>
                    <th data-sort>Client</th>
                    <th data-sort>Provider</th>
                    <th data-sort>Date</th>
                    <th data-sort>Status</th>
                    <th data-sort>Price</th>
                    <th data-sort>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($b = mysqli_fetch_assoc($bookings)): ?>
                    <tr>
                      <td class="number-cell">#<?= $b['id'] ?></td>
                      <td>
                        <div class="fw-semibold"><?= htmlspecialchars($b['service_title']) ?></div>
                        <div class="text-sm text-muted"><?= htmlspecialchars($b['details']) ?></div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-2">
                            <i class="bi bi-person"></i>
                          </div>
                          <span class="fw-semibold"><?= htmlspecialchars($b['client_name']) ?></span>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-2">
                            <i class="bi bi-building"></i>
                          </div>
                          <span class="fw-semibold"><?= htmlspecialchars($b['provider_name']) ?></span>
                        </div>
                      </td>
                      <td class="date-cell"><?= date('M d, Y', strtotime($b['booking_date'])) ?></td>
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
                      <td class="number-cell">$<?= number_format($b['price'], 2) ?></td>
                      <td class="date-cell"><?= date('M d, Y', strtotime($b['created_at'])) ?></td>
                      <td class="actions-cell">
                        <div class="btn-group">
                          <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Actions
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="viewBookingDetails(<?= $b['id'] ?>)">
                              <i class="bi bi-eye me-2"></i>View Details
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="viewBookingDetails(<?= $b['id'] ?>, '<?= htmlspecialchars($b['client_name']) ?>', '<?= htmlspecialchars($b['provider_name']) ?>', '<?= htmlspecialchars($b['service_title']) ?>', '<?= htmlspecialchars($b['details']) ?>', '<?= $b['booking_date'] ?>', '<?= $b['status'] ?>', '<?= $b['created_at'] ?>')">
                              <i class="bi bi-eye me-2"></i>View Details
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="editBooking(<?= $b['id'] ?>, '<?= htmlspecialchars($b['details']) ?>', '<?= $b['booking_date'] ?>', '<?= $b['status'] ?>')">
                              <i class="bi bi-pencil me-2"></i>Edit Booking
                            </a></li>
                            <li><a class="dropdown-item" href="tracking.php?booking_id=<?= $b['id'] ?>">
                              <i class="bi bi-geo-alt me-2"></i>Track Booking
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <select name="new_status" class="form-select form-select-sm mb-2" onchange="this.form.submit()">
                                  <option value="">Change Status</option>
                                  <option value="pending">Pending</option>
                                  <option value="confirmed">Confirmed</option>
                                  <option value="in_progress">In Progress</option>
                                  <option value="completed">Completed</option>
                                  <option value="cancelled">Cancelled</option>
                                </select>
                                <input type="hidden" name="action" value="status">
                              </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this booking? This action cannot be undone.');">
                                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                <button type="submit" name="action" value="delete" class="dropdown-item text-danger">
                                  <i class="bi bi-trash me-2"></i>Delete Booking
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
              Showing all bookings (<?= mysqli_num_rows($bookings) ?> results)
            </div>
            <nav aria-label="Booking pagination">
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

  <!-- Add Booking Modal -->
  <div class="modal fade" id="addBookingModal" tabindex="-1" aria-labelledby="addBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="add">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="addBookingModalLabel">
              <i class="bi bi-plus-circle me-2"></i>Add New Booking
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
                  <label class="form-label fw-bold">Service *</label>
                  <select class="form-select" name="service_id" required>
                    <option value="">Select Service</option>
                    <?php while($s = mysqli_fetch_assoc($services)): ?>
                      <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['title']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Booking Date *</label>
                  <input type="date" class="form-control" name="booking_date" required min="<?= date('Y-m-d') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Status *</label>
                  <select class="form-select" name="status" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Details *</label>
              <textarea class="form-control" name="details" rows="3" required placeholder="Enter booking details..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-circle me-2"></i>Add Booking
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Booking Details Modal -->
  <div class="modal fade" id="viewBookingModal" tabindex="-1" aria-labelledby="viewBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title gradient-text" id="viewBookingModalLabel">
            <i class="bi bi-calendar-check me-2"></i>Booking Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Booking ID</label>
                <input type="text" class="form-control" id="view_booking_id" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Status</label>
                <input type="text" class="form-control" id="view_booking_status" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Client</label>
                <input type="text" class="form-control" id="view_client_name" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Provider</label>
                <input type="text" class="form-control" id="view_provider_name" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Service</label>
                <input type="text" class="form-control" id="view_service_title" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Booking Date</label>
                <input type="text" class="form-control" id="view_booking_date" readonly>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Details</label>
            <textarea class="form-control" id="view_booking_details" rows="3" readonly></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Created Date</label>
            <input type="text" class="form-control" id="view_booking_created_at" readonly>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Booking Modal -->
  <div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="booking_id" id="edit_booking_id">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="editBookingModalLabel">
              <i class="bi bi-pencil-square me-2"></i>Edit Booking
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Booking Date *</label>
                  <input type="date" class="form-control" name="booking_date" id="edit_booking_date" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Status *</label>
                  <select class="form-select" name="status" id="edit_status" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Details *</label>
              <textarea class="form-control" name="details" id="edit_details" rows="3" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-2"></i>Update Booking
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

    function exportBookings() {
      // Export functionality placeholder
      showToast('Export feature coming soon!', 'info');
    }

    function showAddBookingModal() {
      var modal = new bootstrap.Modal(document.getElementById('addBookingModal'));
      modal.show();
    }

    function viewBookingDetails(id, client_name, provider_name, service_title, details, booking_date, status, created_at) {
      document.getElementById('view_booking_id').value = id;
      document.getElementById('view_client_name').value = client_name;
      document.getElementById('view_provider_name').value = provider_name;
      document.getElementById('view_service_title').value = service_title;
      document.getElementById('view_booking_details').value = details || 'No details provided';
      document.getElementById('view_booking_date').value = new Date(booking_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
      document.getElementById('view_booking_status').value = status.charAt(0).toUpperCase() + status.slice(1);
      document.getElementById('view_booking_created_at').value = new Date(created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
      
      var modal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
      modal.show();
    }

    function editBooking(id, details, booking_date, status) {
      document.getElementById('edit_booking_id').value = id;
      document.getElementById('edit_details').value = details;
      document.getElementById('edit_booking_date').value = booking_date;
      document.getElementById('edit_status').value = status;
      
      var modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
      modal.show();
    }

    function viewBookingDetails(bookingId) {
      // View booking details functionality placeholder
      showToast('Booking details feature coming soon!', 'info');
    }
  </script>
</body>
</html> 
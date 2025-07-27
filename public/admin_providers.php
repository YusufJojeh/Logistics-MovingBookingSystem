<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['provider_id'] ?? 0);
    if ($id) {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role = 'provider'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET status = IF(status='active','inactive','active') WHERE id = ? AND role = 'provider'");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin_providers.php');
    exit;
}

// Get provider statistics
$total_providers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='provider'"))[0];
$active_providers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='provider' AND status='active'"))[0];
$total_services = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM services"))[0];
$total_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings"))[0];

// Search/filter
$where = "WHERE role = 'provider'";
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
if ($search) {
    $where .= " AND (name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR email LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR company_name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
if ($status && in_array($status, ['active','inactive'])) {
    $where .= " AND status = '" . mysqli_real_escape_string($conn, $status) . "'";
}
$providers = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Provider Management - Logistics & Moving Booking System</title>
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
          <li class="nav-item"><a class="nav-link active" href="admin_providers.php">Providers</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">Provider Management</h1>
            <p class="lead mb-0">Manage service providers and monitor their performance</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Provider Overview</h2>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-people"></i>
              </div>
              <div class="kpi-number"><?= $total_providers ?></div>
              <div class="kpi-label">Total Providers</div>
              <div class="kpi-trend text-success">
                <i class="bi bi-arrow-up"></i> Growing
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="kpi-number"><?= $active_providers ?></div>
              <div class="kpi-label">Active Providers</div>
              <div class="kpi-trend text-success">
                <i class="bi bi-arrow-up"></i> <?= round(($active_providers/$total_providers)*100) ?>%
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-box"></i>
              </div>
              <div class="kpi-number"><?= $total_services ?></div>
              <div class="kpi-label">Total Services</div>
              <div class="kpi-trend text-info">
                <i class="bi bi-arrow-up"></i> Available
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-calendar-check"></i>
              </div>
              <div class="kpi-number"><?= $total_bookings ?></div>
              <div class="kpi-label">Total Bookings</div>
              <div class="kpi-trend text-warning">
                <i class="bi bi-arrow-up"></i> Active
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Provider Management Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="gradient-text mb-0">Provider List</h2>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" onclick="exportProviders()">
                <i class="bi bi-download me-2"></i>Export
              </button>
              <button class="btn btn-primary" onclick="showAddProviderModal()">
                <i class="bi bi-plus-circle me-2"></i>Add Provider
              </button>
            </div>
          </div>

          <!-- Search and Filter Form -->
          <form class="row g-3 mb-4" method="get">
            <div class="col-md-6">
              <div class="input-group">
                <span class="input-group-text">
                  <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control search-input" placeholder="Search by name, email, or company" value="<?= htmlspecialchars($search) ?>">
              </div>
            </div>
            <div class="col-md-3">
              <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
            <div class="col-md-3">
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
                    <th data-sort>Provider</th>
                    <th data-sort>Company</th>
                    <th data-sort>Status</th>
                    <th data-sort>Services</th>
                    <th data-sort>Bookings</th>
                    <th data-sort>Registered</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($p = mysqli_fetch_assoc($providers)): ?>
                    <?php
                      $sid = $p['id'];
                      $service_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM services WHERE provider_id=$sid"))[0];
                      $booking_count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE provider_id=$sid"))[0];
                    ?>
                    <tr>
                      <td class="number-cell"><?= $p['id'] ?></td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-3">
                            <i class="bi bi-person"></i>
                          </div>
                          <div>
                            <div class="fw-semibold"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="text-sm text-muted"><?= htmlspecialchars($p['email']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <span class="fw-semibold"><?= htmlspecialchars($p['company_name'] ?: 'N/A') ?></span>
                      </td>
                      <td>
                        <span class="badge <?= $p['status']==='active'?'bg-success':'bg-secondary' ?>">
                          <i class="bi bi-<?= $p['status']==='active'?'check-circle':'x-circle' ?> me-1"></i>
                          <?= ucfirst($p['status']) ?>
                        </span>
                      </td>
                      <td>
                        <a href="admin_services.php?provider_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info">
                          <i class="bi bi-box me-1"></i>View (<?= $service_count ?>)
                        </a>
                      </td>
                      <td>
                        <a href="admin_bookings.php?provider_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-warning">
                          <i class="bi bi-calendar-check me-1"></i>View (<?= $booking_count ?>)
                        </a>
                      </td>
                      <td class="date-cell"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                      <td class="actions-cell">
                        <div class="btn-group">
                          <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Actions
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="admin_services.php?provider_id=<?= $p['id'] ?>">
                              <i class="bi bi-box me-2"></i>View Services
                            </a></li>
                            <li><a class="dropdown-item" href="admin_bookings.php?provider_id=<?= $p['id'] ?>">
                              <i class="bi bi-calendar-check me-2"></i>View Bookings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
                                <button type="submit" name="action" value="toggle" class="dropdown-item">
                                  <i class="bi bi-<?= $p['status']==='active'?'pause':'play' ?> me-2"></i>
                                  <?= $p['status']==='active'?'Deactivate':'Activate' ?>
                                </button>
                              </form>
                            </li>
                            <li>
                              <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this provider? This action cannot be undone.');">
                                <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
                                <button type="submit" name="action" value="delete" class="dropdown-item text-danger">
                                  <i class="bi bi-trash me-2"></i>Delete Provider
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
              Showing all providers (<?= mysqli_num_rows($providers) ?> results)
            </div>
            <nav aria-label="Provider pagination">
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

    function exportProviders() {
      // Export functionality placeholder
      showToast('Export feature coming soon!', 'info');
    }

    function showAddProviderModal() {
      // Add provider modal placeholder
      showToast('Add provider feature coming soon!', 'info');
    }
  </script>
</body>
</html> 
<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_admin()) { header('Location: /login.php'); exit; }

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['service_id'] ?? 0);
    if ($id) {
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM services WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $title = trim($_POST['title']);
            $type = trim($_POST['type']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $city_from = trim($_POST['city_from']);
            $city_to = trim($_POST['city_to']);
            $status = $_POST['status'];
            
            if ($title && $price) {
                $stmt = mysqli_prepare($conn, "UPDATE services SET title=?, type=?, description=?, price=?, city_from=?, city_to=?, status=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'sssdsssi', $title, $type, $description, $price, $city_from, $city_to, $status, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'toggle') {
            $stmt = mysqli_prepare($conn, "UPDATE services SET status = IF(status='active','inactive','active') WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'add') {
        $title = trim($_POST['title']);
        $type = trim($_POST['type']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $city_from = trim($_POST['city_from']);
        $city_to = trim($_POST['city_to']);
        $provider_id = intval($_POST['provider_id']);
        $status = $_POST['status'];
        
        if ($title && $price && $provider_id) {
            $stmt = mysqli_prepare($conn, "INSERT INTO services (title, type, description, price, city_from, city_to, provider_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, 'sssdssis', $title, $type, $description, $price, $city_from, $city_to, $provider_id, $status);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: admin_services.php');
    exit;
}

// Get service statistics
$total_services = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM services"))[0];
$active_services = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM services WHERE status='active'"))[0];
$total_revenue = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(price) FROM services WHERE status='active'"))[0];
$avg_price = mysqli_fetch_row(mysqli_query($conn, "SELECT AVG(price) FROM services WHERE status='active'"))[0];

// Search/filter
$where = "WHERE 1";
$search = trim($_GET['search'] ?? '');
$provider_id = intval($_GET['provider_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
if ($search) {
    $where .= " AND (s.title LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR s.type LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
if ($provider_id) {
    $where .= " AND s.provider_id = $provider_id";
}
if ($status && in_array($status, ['active','inactive'])) {
    $where .= " AND s.status = '" . mysqli_real_escape_string($conn, $status) . "'";
}
$services = mysqli_query($conn, "SELECT s.*, u.name AS provider_name FROM services s JOIN users u ON s.provider_id = u.id $where ORDER BY s.created_at DESC");

// Get providers for dropdown
$providers = mysqli_query($conn, "SELECT id, name FROM users WHERE role = 'provider' AND status = 'active' ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Service Management - Logistics & Moving Booking System</title>
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
          <li class="nav-item"><a class="nav-link active" href="admin_services.php">Services</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">Service Management</h1>
            <p class="lead mb-0">Manage and monitor all logistics services across the platform</p>
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
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-box"></i>
              </div>
              <div class="kpi-number"><?= $total_services ?></div>
              <div class="kpi-label">Total Services</div>
              <div class="kpi-trend text-success">
                <i class="bi bi-arrow-up"></i> Available
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-check-circle"></i>
              </div>
              <div class="kpi-number"><?= $active_services ?></div>
              <div class="kpi-label">Active Services</div>
              <div class="kpi-trend text-success">
                <i class="bi bi-arrow-up"></i> <?= round(($active_services/$total_services)*100) ?>%
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-currency-dollar"></i>
              </div>
              <div class="kpi-number">$<?= number_format($total_revenue, 2) ?></div>
              <div class="kpi-label">Total Value</div>
              <div class="kpi-trend text-warning">
                <i class="bi bi-arrow-up"></i> Revenue
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="kpi-card">
              <div class="kpi-icon">
                <i class="bi bi-graph-up"></i>
              </div>
              <div class="kpi-number">$<?= number_format($avg_price, 2) ?></div>
              <div class="kpi-label">Average Price</div>
              <div class="kpi-trend text-info">
                <i class="bi bi-arrow-up"></i> Per Service
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Service Management Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="gradient-text mb-0">Service List</h2>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" onclick="exportServices()">
                <i class="bi bi-download me-2"></i>Export
              </button>
              <button class="btn btn-primary" onclick="showAddServiceModal()">
                <i class="bi bi-plus-circle me-2"></i>Add Service
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
                <input type="text" name="search" class="form-control search-input" placeholder="Search by title or type" value="<?= htmlspecialchars($search) ?>">
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
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
                    <th data-sort>Provider</th>
                    <th data-sort>Type</th>
                    <th data-sort>Price</th>
                    <th data-sort>Status</th>
                    <th data-sort>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while($s = mysqli_fetch_assoc($services)): ?>
                    <tr>
                      <td class="number-cell"><?= $s['id'] ?></td>
                      <td>
                        <div class="fw-semibold"><?= htmlspecialchars($s['title']) ?></div>
                        <div class="text-sm text-muted"><?= htmlspecialchars($s['city_from']) ?> → <?= htmlspecialchars($s['city_to']) ?></div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="user-avatar me-2">
                            <i class="bi bi-person"></i>
                          </div>
                          <span class="fw-semibold"><?= htmlspecialchars($s['provider_name']) ?></span>
                        </div>
                      </td>
                      <td>
                        <span class="badge bg-info">
                          <i class="bi bi-tag me-1"></i><?= htmlspecialchars($s['type']) ?>
                        </span>
                      </td>
                      <td class="number-cell">$<?= number_format($s['price'], 2) ?></td>
                      <td>
                        <span class="badge <?= $s['status']==='active'?'bg-success':'bg-secondary' ?>">
                          <i class="bi bi-<?= $s['status']==='active'?'check-circle':'x-circle' ?> me-1"></i>
                          <?= ucfirst($s['status']) ?>
                        </span>
                      </td>
                      <td class="date-cell"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                      <td class="actions-cell">
                        <div class="btn-group">
                          <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Actions
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="viewServiceDetails(<?= $s['id'] ?>, '<?= htmlspecialchars($s['title']) ?>', '<?= htmlspecialchars($s['type']) ?>', '<?= htmlspecialchars($s['description']) ?>', <?= $s['price'] ?>, '<?= htmlspecialchars($s['city_from']) ?>', '<?= htmlspecialchars($s['city_to']) ?>', '<?= $s['status'] ?>', '<?= htmlspecialchars($s['provider_name']) ?>', '<?= $s['created_at'] ?>')">
                              <i class="bi bi-eye me-2"></i>View Details
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="editService(<?= $s['id'] ?>, '<?= htmlspecialchars($s['title']) ?>', '<?= htmlspecialchars($s['type']) ?>', '<?= htmlspecialchars($s['description']) ?>', <?= $s['price'] ?>, '<?= htmlspecialchars($s['city_from']) ?>', '<?= htmlspecialchars($s['city_to']) ?>', '<?= $s['status'] ?>')">
                              <i class="bi bi-pencil me-2"></i>Edit Service
                            </a></li>
                            <li><a class="dropdown-item" href="admin_bookings.php?service_id=<?= $s['id'] ?>">
                              <i class="bi bi-calendar-check me-2"></i>View Bookings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                              <form method="POST" class="d-inline">
                                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                <button type="submit" name="action" value="toggle" class="dropdown-item">
                                  <i class="bi bi-<?= $s['status']==='active'?'pause':'play' ?> me-2"></i>
                                  <?= $s['status']==='active'?'Deactivate':'Activate' ?>
                                </button>
                              </form>
                            </li>
                            <li>
                              <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this service? This action cannot be undone.');">
                                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                <button type="submit" name="action" value="delete" class="dropdown-item text-danger">
                                  <i class="bi bi-trash me-2"></i>Delete Service
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
              Showing all services (<?= mysqli_num_rows($services) ?> results)
            </div>
            <nav aria-label="Service pagination">
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

  <!-- Add Service Modal -->
  <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="add">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="addServiceModalLabel">
              <i class="bi bi-plus-circle me-2"></i>Add New Service
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Service Title *</label>
                  <input type="text" class="form-control" name="title" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Service Type *</label>
                  <select class="form-select" name="type" required>
                    <option value="">Select Type</option>
                    <option value="Moving">Moving</option>
                    <option value="Delivery">Delivery</option>
                    <option value="Storage">Storage</option>
                    <option value="Logistics">Logistics</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
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
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Price *</label>
                  <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">From City *</label>
                  <input type="text" class="form-control" name="city_from" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">To City *</label>
                  <input type="text" class="form-control" name="city_to" required>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Description</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Status *</label>
              <select class="form-select" name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-plus-circle me-2"></i>Add Service
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Service Details Modal -->
  <div class="modal fade" id="viewServiceModal" tabindex="-1" aria-labelledby="viewServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title gradient-text" id="viewServiceModalLabel">
            <i class="bi bi-box-seam me-2"></i>Service Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Service ID</label>
                <input type="text" class="form-control" id="view_service_id" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Service Title</label>
                <input type="text" class="form-control" id="view_title" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Service Type</label>
                <input type="text" class="form-control" id="view_type" readonly>
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
                <label class="form-label fw-bold">Price</label>
                <input type="text" class="form-control" id="view_price" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">Status</label>
                <input type="text" class="form-control" id="view_status" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">From City</label>
                <input type="text" class="form-control" id="view_city_from" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label fw-bold">To City</label>
                <input type="text" class="form-control" id="view_city_to" readonly>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea class="form-control" id="view_description" rows="3" readonly></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Created Date</label>
            <input type="text" class="form-control" id="view_created_at" readonly>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Service Modal -->
  <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="service_id" id="edit_service_id">
          <div class="modal-header">
            <h5 class="modal-title gradient-text" id="editServiceModalLabel">
              <i class="bi bi-pencil-square me-2"></i>Edit Service
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Service Title *</label>
                  <input type="text" class="form-control" name="title" id="edit_title" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Service Type *</label>
                  <select class="form-select" name="type" id="edit_type" required>
                    <option value="Moving">Moving</option>
                    <option value="Delivery">Delivery</option>
                    <option value="Storage">Storage</option>
                    <option value="Logistics">Logistics</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Price *</label>
                  <input type="number" class="form-control" name="price" id="edit_price" step="0.01" min="0" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Status *</label>
                  <select class="form-select" name="status" id="edit_status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">From City *</label>
                  <input type="text" class="form-control" name="city_from" id="edit_city_from" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">To City *</label>
                  <input type="text" class="form-control" name="city_to" id="edit_city_to" required>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Description</label>
              <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-circle me-2"></i>Update Service
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

    function exportServices() {
      // Export functionality placeholder
      showToast('Export feature coming soon!', 'info');
    }

    function showAddServiceModal() {
      var modal = new bootstrap.Modal(document.getElementById('addServiceModal'));
      modal.show();
    }

    function viewServiceDetails(id, title, type, description, price, city_from, city_to, status, provider_name, created_at) {
      document.getElementById('view_service_id').value = id;
      document.getElementById('view_title').value = title;
      document.getElementById('view_type').value = type;
      document.getElementById('view_description').value = description || 'No description provided';
      document.getElementById('view_price').value = '$' + parseFloat(price).toFixed(2);
      document.getElementById('view_city_from').value = city_from || 'Not specified';
      document.getElementById('view_city_to').value = city_to || 'Not specified';
      document.getElementById('view_status').value = status.charAt(0).toUpperCase() + status.slice(1);
      document.getElementById('view_provider_name').value = provider_name;
      document.getElementById('view_created_at').value = new Date(created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
      
      var modal = new bootstrap.Modal(document.getElementById('viewServiceModal'));
      modal.show();
    }

    function editService(id, title, type, description, price, city_from, city_to, status) {
      document.getElementById('edit_service_id').value = id;
      document.getElementById('edit_title').value = title;
      document.getElementById('edit_type').value = type;
      document.getElementById('edit_description').value = description;
      document.getElementById('edit_price').value = price;
      document.getElementById('edit_city_from').value = city_from;
      document.getElementById('edit_city_to').value = city_to;
      document.getElementById('edit_status').value = status;
      
      var modal = new bootstrap.Modal(document.getElementById('editServiceModal'));
      modal.show();
    }
  </script>
</body>
</html> 
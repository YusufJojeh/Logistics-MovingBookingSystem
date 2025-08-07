<?php
/**
 * Admin Service Management
 *
 * This page allows administrators to manage services for the Logistics & Moving
 * Booking System.  It supports searching, filtering, viewing details, adding,
 * editing, toggling status, and deleting services.  All database operations
 * use prepared statements to mitigate SQL injection risks.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

// Only allow admins
if (!is_admin()) {
    header('Location: /login.php');
    exit;
}

// -----------------------------------------------------------------------------
// Handle form submissions (POST)
//
// We look for an action parameter to determine which operation to perform.  To
// prevent double submissions we always redirect back to this page after the
// operation completes.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $serviceId = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;

    switch ($action) {
        case 'delete':
            if ($serviceId > 0) {
                $stmt = mysqli_prepare($conn, "DELETE FROM services WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $serviceId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            break;
        case 'toggle':
            if ($serviceId > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE services SET status = IF(status='active','inactive','active') WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'i', $serviceId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            break;
        case 'edit':
            if ($serviceId > 0) {
                $title       = trim($_POST['title'] ?? '');
                $type        = trim($_POST['type'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $price       = floatval($_POST['price'] ?? 0);
                $city_from   = trim($_POST['city_from'] ?? '');
                $city_to     = trim($_POST['city_to'] ?? '');
                $status      = $_POST['status'] ?? '';
                if ($title && $price) {
                    $stmt = mysqli_prepare($conn, "UPDATE services SET title=?, type=?, description=?, price=?, city_from=?, city_to=?, status=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, 'sssdsssi', $title, $type, $description, $price, $city_from, $city_to, $status, $serviceId);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            break;
        case 'add':
            $title       = trim($_POST['title'] ?? '');
            $type        = trim($_POST['type'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price       = floatval($_POST['price'] ?? 0);
            $city_from   = trim($_POST['city_from'] ?? '');
            $city_to     = trim($_POST['city_to'] ?? '');
            $provider_id = intval($_POST['provider_id'] ?? 0);
            $status      = $_POST['status'] ?? 'inactive';
            if ($title && $price && $provider_id > 0) {
                $stmt = mysqli_prepare($conn, "INSERT INTO services (title, type, description, price, city_from, city_to, provider_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                mysqli_stmt_bind_param($stmt, 'sssdssis', $title, $type, $description, $price, $city_from, $city_to, $provider_id, $status);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            break;
        default:
            // Unknown action; ignore
            break;
    }
    // Redirect to avoid resubmission on refresh
    header('Location: admin_services.php');
    exit;
}

// -----------------------------------------------------------------------------
// Fetch summary statistics
$statsQuery = "
    SELECT
        COUNT(*) AS total_services,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS total_active,
        SUM(price) AS total_value,
        AVG(price) AS average_price
    FROM services
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats_data = mysqli_fetch_assoc($statsResult) ?: [
    'total_services' => 0,
    'total_active'   => 0,
    'total_value'    => 0,
    'average_price'  => 0,
];

// -----------------------------------------------------------------------------
// Build services query with search and filters
$where = "WHERE 1";
$search      = trim($_GET['search'] ?? '');
$provider_id = intval($_GET['provider_id'] ?? 0);
$status      = trim($_GET['status'] ?? '');

if ($search !== '') {
    $escaped = mysqli_real_escape_string($conn, $search);
    $where .= " AND (s.title LIKE '%$escaped%' OR s.type LIKE '%$escaped%')";
}
if ($provider_id > 0) {
    $where .= " AND s.provider_id = $provider_id";
}
if ($status !== '' && in_array($status, ['active','inactive'], true)) {
    $escapedStatus = mysqli_real_escape_string($conn, $status);
    $where .= " AND s.status = '$escapedStatus'";
}

$servicesQuery = "SELECT s.*, u.name AS provider_name FROM services s JOIN users u ON s.provider_id = u.id $where ORDER BY s.created_at DESC";
$servicesResult = mysqli_query($conn, $servicesQuery);

// Get providers for dropdown
$providersResult = mysqli_query($conn, "SELECT id, name FROM users WHERE role='provider' AND status='active' ORDER BY name");

// -----------------------------------------------------------------------------
// Helper function to escape values for JS strings
function js_escape($string) {
    return str_replace(["'", "\n", "\r"], ["\\'", "\\n", "\\r"], htmlspecialchars($string, ENT_QUOTES));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Service Management - Logistics &amp; Moving Booking System</title>
    <link rel="icon" href="../assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Custom fix: allow dropdowns to overflow outside the table container -->
    <style>
    .table-responsive {
        overflow-y: visible;
    }
    </style>
</head>
<body class="modern-bg">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-glass">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-3 gradient-text" href="admin.php">
                <i class="bi bi-shield-check me-2"></i>Admin<span class="text-gradient-secondary">&amp;</span>Dashboard
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

    <!-- Hero section -->
    <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 25vh;">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="glass-card">
                        <h1 class="display-5 fw-black mb-3 gradient-text">Service Management</h1>
                        <p class="lead mb-0">Manage and monitor logistics services across the platform</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics section -->
    <section class="container-fluid py-6 section-glass">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-box"></i></div>
                    <div class="kpi-number"><?= (int)$stats_data['total_services'] ?></div>
                    <div class="kpi-label">Total Services</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-check-circle"></i></div>
                    <div class="kpi-number"><?= (int)$stats_data['total_active'] ?></div>
                    <div class="kpi-label">Active Services</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-currency-dollar"></i></div>
                    <div class="kpi-number">$<?= number_format($stats_data['total_value'] ?? 0, 2) ?></div>
                    <div class="kpi-label">Total Value</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-graph-up"></i></div>
                    <div class="kpi-number">$<?= number_format($stats_data['average_price'] ?? 0, 2) ?></div>
                    <div class="kpi-label">Average Price</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service management section -->
    <section class="container-fluid py-6 section-gradient">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="gradient-text mb-0">Service List</h2>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="exportServices()"><i class="bi bi-download me-2"></i>Export</button>
                            <button class="btn btn-primary" onclick="showAddServiceModal()"><i class="bi bi-plus-circle me-2"></i>Add Service</button>
                        </div>
                    </div>
                    <!-- Search/filter form -->
                    <div class="glass-card mb-4">
                        <form class="row g-3" method="get">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Search by title or type" value="<?= htmlspecialchars($search) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="provider_id" class="form-select">
                                    <option value="">All Providers</option>
                                    <?php mysqli_data_seek($providersResult, 0); while ($p = mysqli_fetch_assoc($providersResult)): ?>
                                        <option value="<?= (int)$p['id'] ?>" <?= $provider_id == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
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
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-2"></i>Filter</button>
                            </div>
                        </form>
                    </div>
                    <!-- Services table -->
                    <div class="table-responsive">
                        <table class="table table-dark align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Service</th>
                                    <th>Provider</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($s = mysqli_fetch_assoc($servicesResult)): ?>
                                    <?php
                                    // Determine status badge and icon
                                    $statusBadge = $s['status'] === 'active' ? 'bg-success' : 'bg-secondary';
                                    $statusIcon  = $s['status'] === 'active' ? 'bi-check-circle' : 'bi-x-circle';
                                    $statusText  = ucfirst($s['status']);
                                    ?>
                                    <tr>
                                        <td><span class="badge bg-secondary">#<?= (int)$s['id'] ?></span></td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($s['title']) ?></div>
                                            <div class="text-sm text-muted"><?= htmlspecialchars($s['city_from']) ?> &rarr; <?= htmlspecialchars($s['city_to']) ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-2"><i class="bi bi-person"></i></div>
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
                                            <span class="badge <?= $statusBadge ?>">
                                                <i class="bi <?= $statusIcon ?> me-1"></i><?= $statusText ?>
                                            </span>
                                        </td>
                                        <td class="date-cell"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                                    <i class="bi bi-gear"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="viewServiceDetails(
                                                            <?= (int)$s['id'] ?>,
                                                            '<?= js_escape($s['title']) ?>',
                                                            '<?= js_escape($s['type']) ?>',
                                                            '<?= js_escape($s['description']) ?>',
                                                            <?= (float)$s['price'] ?>,
                                                            '<?= js_escape($s['city_from']) ?>',
                                                            '<?= js_escape($s['city_to']) ?>',
                                                            '<?= $s['status'] ?>',
                                                            '<?= js_escape($s['provider_name']) ?>',
                                                            '<?= $s['created_at'] ?>'
                                                        )">
                                                            <i class="bi bi-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="editService(
                                                            <?= (int)$s['id'] ?>,
                                                            '<?= js_escape($s['title']) ?>',
                                                            '<?= js_escape($s['type']) ?>',
                                                            '<?= js_escape($s['description']) ?>',
                                                            <?= (float)$s['price'] ?>,
                                                            '<?= js_escape($s['city_from']) ?>',
                                                            '<?= js_escape($s['city_to']) ?>',
                                                            '<?= $s['status'] ?>'
                                                        )">
                                                            <i class="bi bi-pencil me-2"></i>Edit Service
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="admin_bookings.php?service_id=<?= (int)$s['id'] ?>">
                                                            <i class="bi bi-calendar-check me-2"></i>View Bookings
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="service_id" value="<?= (int)$s['id'] ?>">
                                                            <button type="submit" name="action" value="toggle" class="dropdown-item">
                                                                <i class="bi bi-<?= $s['status']==='active' ? 'pause' : 'play' ?> me-2"></i>
                                                                <?= $s['status']==='active' ? 'Deactivate' : 'Activate' ?>
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this service? This action cannot be undone.');">
                                                            <input type="hidden" name="service_id" value="<?= (int)$s['id'] ?>">
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
                    <!-- Pagination placeholder (optional) -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">Showing <?= mysqli_num_rows($servicesResult) ?> services</div>
                        <nav aria-label="Service pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
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
                        <h5 class="modal-title gradient-text" id="addServiceModalLabel"><i class="bi bi-plus-circle me-2"></i>Add New Service</h5>
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
                                        <?php mysqli_data_seek($providersResult, 0); while ($p = mysqli_fetch_assoc($providersResult)): ?>
                                            <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Service Modal -->
    <div class="modal fade" id="viewServiceModal" tabindex="-1" aria-labelledby="viewServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title gradient-text" id="viewServiceModalLabel"><i class="bi bi-box-seam me-2"></i>Service Details</h5>
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
                        <h5 class="modal-title gradient-text" id="editServiceModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Service</h5>
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
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Update Service</button>
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
                    <p class="mb-2">&copy; <?= date('Y') ?> Logistics &amp; Moving Booking System. All rights reserved.</p>
                    <p class="mb-0">
                        <a href="#privacy" class="text-decoration-none me-3">Privacy Policy</a>
                        <a href="#terms" class="text-decoration-none me-3">Terms of Service</a>
                        <a href="#contact" class="text-decoration-none">Contact Us</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Placeholder: export services
        function exportServices() {
            alert('Export functionality will be implemented here.');
        }
        // Show Add Service modal
        function showAddServiceModal() {
            new bootstrap.Modal(document.getElementById('addServiceModal')).show();
        }
        // Populate and show View Service modal
        function viewServiceDetails(id, title, type, description, price, city_from, city_to, status, provider_name, created_at) {
            document.getElementById('view_service_id').value    = id;
            document.getElementById('view_title').value         = title;
            document.getElementById('view_type').value          = type;
            document.getElementById('view_description').value   = description || 'No description provided';
            document.getElementById('view_price').value         = '$' + parseFloat(price).toFixed(2);
            document.getElementById('view_city_from').value     = city_from || 'Not specified';
            document.getElementById('view_city_to').value       = city_to || 'Not specified';
            document.getElementById('view_status').value        = status.charAt(0).toUpperCase() + status.slice(1);
            document.getElementById('view_provider_name').value = provider_name;
            document.getElementById('view_created_at').value    = new Date(created_at).toLocaleString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
            new bootstrap.Modal(document.getElementById('viewServiceModal')).show();
        }
        // Populate and show Edit Service modal
        function editService(id, title, type, description, price, city_from, city_to, status) {
            document.getElementById('edit_service_id').value = id;
            document.getElementById('edit_title').value       = title;
            document.getElementById('edit_type').value        = type;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_price').value       = price;
            document.getElementById('edit_city_from').value   = city_from;
            document.getElementById('edit_city_to').value     = city_to;
            document.getElementById('edit_status').value      = status;
            new bootstrap.Modal(document.getElementById('editServiceModal')).show();
        }
    </script>
</body>
</html>
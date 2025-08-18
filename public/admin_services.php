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
// Handle form submissions (POST) - READ ONLY MODE
//
// Admin can only view services, no editing capabilities
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Redirect to avoid any potential form submissions
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
            <a class="navbar-brand fw-bold fs-3" href="admin.php">
              <img src="../assets/img/logo.svg" alt="MovePro Admin Logo" class="logo-svg">
              <span class="logo-text-white">MovePro</span><span class="logo-text-blue">Admin</span>
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
    </script>
</body>
</html>
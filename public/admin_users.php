<?php
/**
 * Admin User Management (Rewrite from scratch)
 *
 * This page allows administrators to manage users for the Logistics & Moving
 * Booking System.  It supports searching, filtering, viewing details, adding,
 * editing, promoting/demoting, toggling status, resetting passwords and
 * deleting users.  All database operations use prepared statements to
 * mitigate SQL injection risks.
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
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    switch ($action) {
        case 'delete':
            if ($userId > 0) {
                $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role != 'admin'");
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            break;
        case 'toggle':
            if ($userId > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE users SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ? AND role != 'admin'");
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            break;
        case 'promote':
            if ($userId > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE users SET role = 'provider' WHERE id = ? AND role = 'client'");
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            break;
        case 'demote':
            if ($userId > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE users SET role = 'client' WHERE id = ? AND role = 'provider'");
                mysqli_stmt_bind_param($stmt, 'i', $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            break;
        case 'resetpw':
            if ($userId > 0) {
                $newHash = password_hash('changeme123', PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ? AND role != 'admin'");
                mysqli_stmt_bind_param($stmt, 'si', $newHash, $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            break;
        case 'edit':
            if ($userId > 0) {
                $name         = trim($_POST['name'] ?? '');
                $email        = trim($_POST['email'] ?? '');
                $phone        = trim($_POST['phone'] ?? '');
                $companyName  = trim($_POST['company_name'] ?? '');
                $role         = $_POST['role'] ?? '';
                $status       = $_POST['status'] ?? '';
                if ($name && $email) {
                    $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, phone = ?, company_name = ?, role = ?, status = ? WHERE id = ? AND role != 'admin'");
                    mysqli_stmt_bind_param($stmt, 'ssssssi', $name, $email, $phone, $companyName, $role, $status, $userId);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            break;
        case 'add':
            // Only add a user if required fields are provided
            $name         = trim($_POST['name'] ?? '');
            $email        = trim($_POST['email'] ?? '');
            $phone        = trim($_POST['phone'] ?? '');
            $companyName  = trim($_POST['company_name'] ?? '');
            $role         = $_POST['role'] ?? 'client';
            $status       = $_POST['status'] ?? 'inactive';
            $passwordRaw  = $_POST['password'] ?? '';
            if ($name && $email && $passwordRaw) {
                // Ensure email is unique
                $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                $exists = mysqli_stmt_num_rows($stmt) > 0;
                mysqli_stmt_close($stmt);
                if (!$exists) {
                    $passwordHash = password_hash($passwordRaw, PASSWORD_BCRYPT);
                    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, phone, company_name, role, status, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    mysqli_stmt_bind_param($stmt, 'sssssss', $name, $email, $phone, $companyName, $role, $status, $passwordHash);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
            break;
        default:
            // Unknown action; ignore
            break;
    }
    // Redirect to avoid resubmission on refresh
    header('Location: admin_users.php');
    exit;
}

// -----------------------------------------------------------------------------
// Fetch summary statistics (excluding admins)
$statsQuery = "
    SELECT
        COUNT(*) AS total_users,
        SUM(CASE WHEN role = 'client' THEN 1 ELSE 0 END) AS total_clients,
        SUM(CASE WHEN role = 'provider' THEN 1 ELSE 0 END) AS total_providers,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_users,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) AS inactive_users
    FROM users
    WHERE role != 'admin'
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats_data = mysqli_fetch_assoc($statsResult) ?: [
    'total_users'     => 0,
    'total_clients'   => 0,
    'total_providers' => 0,
    'active_users'    => 0,
    'inactive_users'  => 0,
];

// -----------------------------------------------------------------------------
// Build user query with search and filters
$where = "WHERE role != 'admin'";
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $escaped = mysqli_real_escape_string($conn, $search);
    $where .= " AND (name LIKE '%$escaped%' OR email LIKE '%$escaped%')";
}
$roleFilter = $_GET['role'] ?? '';
if ($roleFilter !== '' && in_array($roleFilter, ['client','provider'], true)) {
    $escaped = mysqli_real_escape_string($conn, $roleFilter);
    $where .= " AND role = '$escaped'";
}
$statusFilter = $_GET['status'] ?? '';
if ($statusFilter !== '' && in_array($statusFilter, ['active','inactive'], true)) {
    $escaped = mysqli_real_escape_string($conn, $statusFilter);
    $where .= " AND status = '$escaped'";
}

$usersQuery = "SELECT id, name, email, phone, company_name, role, status, created_at FROM users $where ORDER BY created_at DESC";
$usersResult = mysqli_query($conn, $usersQuery);

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
    <title>Admin - User Management - Logistics &amp; Moving Booking System</title>
    <link rel="icon" href="../assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link active" href="admin_users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_services.php">Services</a></li>
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
                        <h1 class="display-5 fw-black mb-3 gradient-text">User Management</h1>
                        <p class="lead mb-0">Manage all platform users, their roles, and account status</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics section -->
    <section class="container-fluid py-6 section-glass">
        <div class="row g-4">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-people-fill"></i></div>
                    <div class="kpi-number"><?= (int)$stats_data['total_users'] ?></div>
                    <div class="kpi-label">Total Users</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-person-check"></i></div>
                    <div class="kpi-number"><?= (int)$stats_data['total_clients'] ?></div>
                    <div class="kpi-label">Clients</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-truck"></i></div>
                    <div class="kpi-number"><?= (int)$stats_data['total_providers'] ?></div>
                    <div class="kpi-label">Providers</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-check-circle"></i></div>
                    <div class="kpi-number"><?= (int)$stats_data['active_users'] ?></div>
                    <div class="kpi-label">Active Users</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-pause-circle"></i></div>
                    <div class="kpi-number"><?= (int)$stats_data['inactive_users'] ?></div>
                    <div class="kpi-label">Inactive Users</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="kpi-card text-center">
                    <div class="kpi-icon mb-3"><i class="bi bi-graph-up"></i></div>
                    <?php
                    $activeRate = ($stats_data['total_users'] ?? 0) > 0
                        ? round(($stats_data['active_users'] / $stats_data['total_users']) * 100, 1)
                        : 0;
                    ?>
                    <div class="kpi-number"><?= $activeRate ?>%</div>
                    <div class="kpi-label">Active Rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- User management section -->
    <section class="container-fluid py-6 section-gradient">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="glass-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="gradient-text mb-0">User List</h2>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="exportUsers()"><i class="bi bi-download me-2"></i>Export</button>
                            <button class="btn btn-primary" onclick="showAddUserModal()"><i class="bi bi-person-plus me-2"></i>Add User</button>
                        </div>
                    </div>
                    <!-- Search/filter form -->
                    <div class="glass-card mb-4">
                        <form class="row g-3" method="get">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="client" <?= $roleFilter === 'client' ? 'selected' : '' ?>>Client</option>
                                    <option value="provider" <?= $roleFilter === 'provider' ? 'selected' : '' ?>>Provider</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-2"></i>Filter</button>
                            </div>
                            <div class="col-md-2">
                                <a href="admin_users.php" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-clockwise me-2"></i>Reset</a>
                            </div>
                        </form>
                    </div>
                    <!-- Users table -->
                    <div class="table-responsive">
                        <table class="table table-dark align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Company</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($usersResult)): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary">#<?= htmlspecialchars($row['id']) ?></span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?= get_profile_image_html($row, 'small', false) ?>
                                                <div class="ms-3">
                                                    <div class="fw-semibold"><?= htmlspecialchars($row['name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <span class="badge <?= $row['role'] === 'provider' ? 'bg-info' : 'bg-secondary' ?>">
                                                <i class="bi <?= $row['role'] === 'provider' ? 'bi-truck' : 'bi-person' ?> me-1"></i>
                                                <?= ucfirst(htmlspecialchars($row['role'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-warning'?>">
                                                <i class="bi <?= $row['status'] === 'active' ? 'bi-check-circle' : 'bi-pause-circle' ?> me-1"></i>
                                                <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['company_name'])): ?>
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars($row['company_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-sm">
                                                <div><?= date('M d, Y', strtotime($row['created_at'])) ?></div>
                                                <small class="text-muted"><?= date('H:i', strtotime($row['created_at'])) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                                    <i class="bi bi-gear"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                            <button type="submit" name="action" value="toggle" class="dropdown-item">
                                                                <i class="bi <?= $row['status'] === 'active' ? 'bi-pause-circle' : 'bi-play-circle' ?> me-2"></i>
                                                                <?= $row['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <?php if ($row['role'] === 'client'): ?>
                                                        <li>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                                <button type="submit" name="action" value="promote" class="dropdown-item">
                                                                    <i class="bi bi-arrow-up-circle me-2"></i>Promote to Provider
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php elseif ($row['role'] === 'provider'): ?>
                                                        <li>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                                <button type="submit" name="action" value="demote" class="dropdown-item">
                                                                    <i class="bi bi-arrow-down-circle me-2"></i>Demote to Client
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="viewUserDetails(<?= $row['id'] ?>, '<?= js_escape($row['name']) ?>', '<?= js_escape($row['email']) ?>', '<?= js_escape($row['phone']) ?>', '<?= js_escape($row['company_name']) ?>', '<?= $row['role'] ?>', '<?= $row['status'] ?>', '<?= $row['created_at'] ?>')">
                                                            <i class="bi bi-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="editUser(<?= $row['id'] ?>, '<?= js_escape($row['name']) ?>', '<?= js_escape($row['email']) ?>', '<?= js_escape($row['phone']) ?>', '<?= js_escape($row['company_name']) ?>', '<?= $row['role'] ?>', '<?= $row['status'] ?>')">
                                                            <i class="bi bi-pencil me-2"></i>Edit User
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                            <button type="submit" name="action" value="resetpw" class="dropdown-item">
                                                                <i class="bi bi-key me-2"></i>Reset Password
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                            <button type="submit" name="action" value="delete" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-2"></i>Delete User
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
                    <!-- Table ends -->
                    <!-- Pagination placeholder (optional) -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">Showing <?= mysqli_num_rows($usersResult) ?> users</div>
                        <nav aria-label="User pagination">
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title gradient-text" id="addUserModalLabel"><i class="bi bi-person-plus me-2"></i>Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Name *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Address *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Company Name</label>
                                    <input type="text" class="form-control" name="company_name">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Role *</label>
                                    <select class="form-select" name="role" required>
                                        <option value="client">Client</option>
                                        <option value="provider">Provider</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status *</label>
                                    <select class="form-select" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Password *</label>
                                    <input type="password" class="form-control" name="password" required minlength="6">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title gradient-text" id="viewUserModalLabel"><i class="bi bi-person-circle me-2"></i>User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">User ID</label>
                                <input type="text" class="form-control" id="view_user_id" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control" id="view_name" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control" id="view_email" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" class="form-control" id="view_phone" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Company Name</label>
                                <input type="text" class="form-control" id="view_company_name" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Role</label>
                                <input type="text" class="form-control" id="view_role" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <input type="text" class="form-control" id="view_status" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Registration Date</label>
                                <input type="text" class="form-control" id="view_created_at" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-header">
                        <h5 class="modal-title gradient-text" id="editUserModalLabel"><i class="bi bi-person-gear me-2"></i>Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Full Name *</label>
                                    <input type="text" class="form-control" name="name" id="edit_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email Address *</label>
                                    <input type="email" class="form-control" name="email" id="edit_email" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" id="edit_phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Company Name</label>
                                    <input type="text" class="form-control" name="company_name" id="edit_company_name">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Role *</label>
                                    <select class="form-select" name="role" id="edit_role" required>
                                        <option value="client">Client</option>
                                        <option value="provider">Provider</option>
                                    </select>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Update User</button>
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
        // Placeholder: export users
        function exportUsers() {
            alert('Export functionality will be implemented here.');
        }
        // Show Add User modal
        function showAddUserModal() {
            new bootstrap.Modal(document.getElementById('addUserModal')).show();
        }
        // Populate and show View User modal
        function viewUserDetails(id, name, email, phone, company, role, status, createdAt) {
            document.getElementById('view_user_id').value    = id;
            document.getElementById('view_name').value       = name;
            document.getElementById('view_email').value      = email;
            document.getElementById('view_phone').value      = phone || 'Not provided';
            document.getElementById('view_company_name').value= company || 'Not provided';
            document.getElementById('view_role').value       = role.charAt(0).toUpperCase() + role.slice(1);
            document.getElementById('view_status').value     = status.charAt(0).toUpperCase() + status.slice(1);
            document.getElementById('view_created_at').value = new Date(createdAt).toLocaleString('en-US', {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'});
            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
        }
        // Populate and show Edit User modal
        function editUser(id, name, email, phone, company, role, status) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_name').value     = name;
            document.getElementById('edit_email').value    = email;
            document.getElementById('edit_phone').value    = phone;
            document.getElementById('edit_company_name').value = company;
            document.getElementById('edit_role').value     = role;
            document.getElementById('edit_status').value   = status;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
    </script>
</body>
</html>
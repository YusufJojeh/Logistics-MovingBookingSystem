<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
require_once '../includes/auth.php';

// Check if user is logged in and is provider
if (!is_logged_in() || !is_provider()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
$provider_id = $user['id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $type = trim($_POST['type']);
                $price = floatval($_POST['price']);
                $city_from = trim($_POST['city_from']);
                $city_to = trim($_POST['city_to']);
                $available_from = $_POST['available_from'];
                $available_to = $_POST['available_to'];
                
                if (empty($title) || empty($type) || $price <= 0) {
                    $error = "Please fill in all required fields with valid values.";
                } else {
                    $stmt = mysqli_prepare($conn, "INSERT INTO services (provider_id, title, description, type, price, city_from, city_to, available_from, available_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, 'isssdssss', $provider_id, $title, $description, $type, $price, $city_from, $city_to, $available_from, $available_to);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Service added successfully!";
                    } else {
                        $error = "Failed to add service. Please try again.";
                    }
                    mysqli_stmt_close($stmt);
                }
                break;
                
            case 'update':
                $service_id = intval($_POST['service_id']);
                $title = trim($_POST['title']);
                $type = trim($_POST['type']);
                $price = floatval($_POST['price']);
                $city_from = trim($_POST['city_from']);
                $city_to = trim($_POST['city_to']);
                $status = $_POST['status'];
                
                if (empty($title) || empty($type) || $price <= 0) {
                    $error = "Please fill in all required fields with valid values.";
                } else {
                    $stmt = mysqli_prepare($conn, "UPDATE services SET title=?, type=?, price=?, city_from=?, city_to=?, status=? WHERE id=? AND provider_id=?");
                    mysqli_stmt_bind_param($stmt, 'ssdsssii', $title, $type, $price, $city_from, $city_to, $status, $service_id, $provider_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Service updated successfully!";
                    } else {
                        $error = "Failed to update service. Please try again.";
                    }
                    mysqli_stmt_close($stmt);
                }
                break;
                
            case 'delete':
                $service_id = intval($_POST['service_id']);
                $stmt = mysqli_prepare($conn, "DELETE FROM services WHERE id=? AND provider_id=?");
                mysqli_stmt_bind_param($stmt, 'ii', $service_id, $provider_id);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Service deleted successfully!";
                } else {
                    $error = "Failed to delete service. Please try again.";
                }
                mysqli_stmt_close($stmt);
                break;
        }
    }
}

// Get all services for this provider
$services = mysqli_query($conn, "SELECT * FROM services WHERE provider_id = $provider_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Services - Provider Dashboard</title>
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
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="dashboard_provider.php">
        <i class="bi bi-building me-2"></i>Provider<span class="text-gradient-secondary">&</span>Dashboard
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#providerNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="providerNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="dashboard_provider.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" href="provider_services.php">My Services</a></li>
          <li class="nav-item"><a class="nav-link" href="provider_bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="provider_reviews.php">Reviews</a></li>
          <li class="nav-item"><a class="nav-link" href="provider_profile.php">Profile</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">My Services</h1>
            <p class="lead mb-0">Manage your service offerings and grow your business</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-12">
        <div class="glass-card">
          <h2 class="gradient-text mb-4 text-center">Manage Your Services</h2>
          
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

          <!-- Add Service Form -->
          <div class="mb-5">
            <h3 class="gradient-text mb-4">
              <i class="bi bi-plus-circle me-2"></i>Add New Service
            </h3>
            <form method="POST" class="row g-3">
              <input type="hidden" name="action" value="add">
              <div class="col-md-6">
                <label for="title" class="form-label fw-semibold">Service Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
              </div>
              <div class="col-md-6">
                <label for="type" class="form-label fw-semibold">Service Type</label>
                <input type="text" class="form-control" id="type" name="type" required>
              </div>
              <div class="col-md-4">
                <label for="price" class="form-label fw-semibold">Price ($)</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
              </div>
              <div class="col-md-4">
                <label for="city_from" class="form-label fw-semibold">From City</label>
                <input type="text" class="form-control" id="city_from" name="city_from">
              </div>
              <div class="col-md-4">
                <label for="city_to" class="form-label fw-semibold">To City</label>
                <input type="text" class="form-control" id="city_to" name="city_to">
              </div>
              <div class="col-md-6">
                <label for="available_from" class="form-label fw-semibold">Available From</label>
                <input type="date" class="form-control" id="available_from" name="available_from">
              </div>
              <div class="col-md-6">
                <label for="available_to" class="form-label fw-semibold">Available To</label>
                <input type="date" class="form-control" id="available_to" name="available_to">
              </div>
              <div class="col-12">
                <label for="description" class="form-label fw-semibold">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-plus-circle me-2"></i>Add Service
                </button>
              </div>
            </form>
          </div>

          <!-- Services Table -->
          <div class="mt-5">
            <h3 class="gradient-text mb-4">
              <i class="bi bi-list-ul me-2"></i>Your Services
            </h3>
            <?php if (mysqli_num_rows($services) > 0): ?>
              <div class="table-container">
                <div class="table-responsive" style="width: 100vw !important; max-width: 100vw !important; margin-left: calc(-50vw + 50%) !important; margin-right: calc(-50vw + 50%) !important; padding: 0 !important;">
                  <table class="table table-dark align-middle" style="width: 100% !important; min-width: 100% !important;">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php while($service = mysqli_fetch_assoc($services)): ?>
                        <tr>
                          <td><?= $service['id'] ?></td>
                          <td><?= htmlspecialchars($service['title']) ?></td>
                          <td><?= htmlspecialchars($service['type']) ?></td>
                          <td>$<?= number_format($service['price'], 2) ?></td>
                          <td><?= htmlspecialchars($service['city_from']) ?></td>
                          <td><?= htmlspecialchars($service['city_to']) ?></td>
                          <td>
                            <span class="badge <?= $service['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                              <?= ucfirst($service['status']) ?>
                            </span>
                          </td>
                          <td><?= date('Y-m-d', strtotime($service['created_at'])) ?></td>
                          <td>
                            <div class="btn-group" role="group">
                              <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-gear"></i> Actions
                              </button>
                              <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="viewServiceDetails(<?= $service['id'] ?>, '<?= htmlspecialchars($service['title']) ?>', '<?= htmlspecialchars($service['type']) ?>', '<?= htmlspecialchars($service['description']) ?>', <?= $service['price'] ?>, '<?= htmlspecialchars($service['city_from']) ?>', '<?= htmlspecialchars($service['city_to']) ?>', '<?= $service['status'] ?>', '<?= $service['created_at'] ?>')">
                                  <i class="bi bi-eye me-2"></i>View Details
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="editService(<?= $service['id'] ?>, '<?= htmlspecialchars($service['title']) ?>', '<?= htmlspecialchars($service['type']) ?>', <?= $service['price'] ?>, '<?= htmlspecialchars($service['city_from']) ?>', '<?= htmlspecialchars($service['city_to']) ?>', '<?= $service['status'] ?>')">
                                  <i class="bi bi-pencil me-2"></i>Edit Service
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                  <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this service?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                    <button type="submit" class="dropdown-item text-danger">
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
            <?php else: ?>
              <div class="text-center py-5">
                <i class="bi bi-box display-1 text-muted"></i>
                <h4 class="mt-3">No Services Yet</h4>
                <p class="text-muted">Start by adding your first service above.</p>
              </div>
            <?php endif; ?>
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
                  <label class="form-label fw-bold">Price *</label>
                  <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label fw-bold">Available From</label>
                  <input type="date" class="form-control" name="available_from">
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
              <label class="form-label fw-bold">Available To</label>
              <input type="date" class="form-control" name="available_to">
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Description</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
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
                <label class="form-label fw-bold">Price</label>
                <input type="text" class="form-control" id="view_price" readonly>
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
                <label class="form-label fw-bold">Created Date</label>
                <input type="text" class="form-control" id="view_created_at" readonly>
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
          <input type="hidden" name="action" value="update">
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

    function showAddServiceModal() {
      var modal = new bootstrap.Modal(document.getElementById('addServiceModal'));
      modal.show();
    }

    function viewServiceDetails(id, title, type, description, price, city_from, city_to, status, created_at) {
      document.getElementById('view_service_id').value = id;
      document.getElementById('view_title').value = title;
      document.getElementById('view_type').value = type;
      document.getElementById('view_description').value = description || 'No description provided';
      document.getElementById('view_price').value = '$' + parseFloat(price).toFixed(2);
      document.getElementById('view_city_from').value = city_from || 'Not specified';
      document.getElementById('view_city_to').value = city_to || 'Not specified';
      document.getElementById('view_status').value = status.charAt(0).toUpperCase() + status.slice(1);
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

    function editService(id, title, type, price, city_from, city_to, status) {
      document.getElementById('edit_service_id').value = id;
      document.getElementById('edit_title').value = title;
      document.getElementById('edit_type').value = type;
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
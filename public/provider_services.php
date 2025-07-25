<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_provider()) { header('Location: /login.php'); exit; }
$user = current_user();
$provider_id = $user['id'];

// Handle add/edit/delete/toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        $type = trim($_POST['type']);
        $price = floatval($_POST['price']);
        $city_from = trim($_POST['city_from']);
        $city_to = trim($_POST['city_to']);
        $from = $_POST['available_from'];
        $to = $_POST['available_to'];
        $stmt = mysqli_prepare($conn, "INSERT INTO services (provider_id, title, description, type, price, city_from, city_to, available_from, available_to) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isssdssss', $provider_id, $title, $desc, $type, $price, $city_from, $city_to, $from, $to);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['action'], $_POST['service_id'])) {
        $id = intval($_POST['service_id']);
        if ($_POST['action'] === 'delete') {
            $stmt = mysqli_prepare($conn, "DELETE FROM services WHERE id = ? AND provider_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $id, $provider_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($_POST['action'] === 'toggle') {
            $stmt = mysqli_prepare($conn, "UPDATE services SET status = IF(status='active','inactive','active') WHERE id = ? AND provider_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $id, $provider_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($_POST['action'] === 'edit' && isset($_POST['title'], $_POST['price'], $_POST['status'])) {
            $title = trim($_POST['title']);
            $desc = trim($_POST['description']);
            $type = trim($_POST['type']);
            $price = floatval($_POST['price']);
            $city_from = trim($_POST['city_from']);
            $city_to = trim($_POST['city_to']);
            $from = $_POST['available_from'];
            $to = $_POST['available_to'];
            $status = $_POST['status'];
            $stmt = mysqli_prepare($conn, "UPDATE services SET title=?, description=?, type=?, price=?, city_from=?, city_to=?, available_from=?, available_to=?, status=? WHERE id=? AND provider_id=?");
            mysqli_stmt_bind_param($stmt, 'ssssssssssi', $title, $desc, $type, $price, $city_from, $city_to, $from, $to, $status, $id, $provider_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    header('Location: provider_services.php');
    exit;
}

// Fetch own services
$services = mysqli_query($conn, "SELECT * FROM services WHERE provider_id=$provider_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Services - Provider Dashboard</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold fs-3 gradient-text" href="/dashboard_provider.php">Provider<span class="text-primary">&</span>Dashboard</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#providerNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="providerNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item"><a class="nav-link" href="provider_services.php">My Services</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_bookings.php">Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="provider_profile.php">Profile</a></li>
        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 30vh;">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="glass-card p-4 mb-4">
          <h1 class="display-5 fw-black mb-2 gradient-text">Manage Your Services</h1>
          <p class="lead mb-2 text-body-secondary">Add, edit, or remove your logistics and moving services. All changes are instantly reflected in the marketplace.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container-fluid py-4 section-glass">
  <div class="glass-card p-4 mb-4">
    <h2 class="gradient-text mb-3">My Services</h2>
    <form class="row g-2 mb-4" method="POST">
      <input type="hidden" name="action" value="add">
      <div class="col-md-3"><input type="text" name="title" class="form-control" placeholder="Title" required></div>
      <div class="col-md-2"><input type="text" name="type" class="form-control" placeholder="Type" required></div>
      <div class="col-md-2"><input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required></div>
      <div class="col-md-2"><input type="text" name="city_from" class="form-control" placeholder="From" required></div>
      <div class="col-md-2"><input type="text" name="city_to" class="form-control" placeholder="To" required></div>
      <div class="col-md-3 mt-2"><input type="date" name="available_from" class="form-control" placeholder="Available From" required></div>
      <div class="col-md-3 mt-2"><input type="date" name="available_to" class="form-control" placeholder="Available To" required></div>
      <div class="col-md-12 mt-2"><textarea name="description" class="form-control" placeholder="Description" rows="2" required></textarea></div>
      <div class="col-md-2 mt-2"><button class="btn btn-success w-100">Add Service</button></div>
    </form>
    <div class="table-responsive">
      <table class="table admin-table align-middle">
        <thead><tr><th>ID</th><th>Title</th><th>Type</th><th>Price</th><th>From</th><th>To</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($s = mysqli_fetch_assoc($services)): ?>
          <tr>
            <form method="POST" class="align-middle">
              <td><?= $s['id'] ?></td>
              <td><input type="text" name="title" value="<?= htmlspecialchars($s['title']) ?>" class="form-control form-control-sm"></td>
              <td><input type="text" name="type" value="<?= htmlspecialchars($s['type']) ?>" class="form-control form-control-sm"></td>
              <td><input type="number" step="0.01" name="price" value="<?= $s['price'] ?>" class="form-control form-control-sm" style="width:100px"></td>
              <td><input type="text" name="city_from" value="<?= htmlspecialchars($s['city_from']) ?>" class="form-control form-control-sm"></td>
              <td><input type="text" name="city_to" value="<?= htmlspecialchars($s['city_to']) ?>" class="form-control form-control-sm"></td>
              <td>
                <select name="status" class="form-select form-select-sm">
                  <option value="active" <?= $s['status']==='active'?'selected':'' ?>>Active</option>
                  <option value="inactive" <?= $s['status']==='inactive'?'selected':'' ?>>Inactive</option>
                </select>
              </td>
              <td><?= date('Y-m-d', strtotime($s['created_at'])) ?></td>
              <td>
                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                <button name="action" value="edit" class="btn btn-sm btn-outline-success">Save</button>
                <button name="action" value="toggle" class="btn btn-sm btn-outline-primary"><?= $s['status']==='active'?'Deactivate':'Activate' ?></button>
                <button name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this service?');">Delete</button>
              </td>
            </form>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<footer class="footer-glass text-center py-4 mt-5">
  <small>&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/main.js"></script>
</body>
</html> 
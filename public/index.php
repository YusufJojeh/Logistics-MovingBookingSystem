<?php
require_once '../config/config.php';
require_once '../includes/auth.php';

if (!is_logged_in() || !is_admin()) {
  header('Location: login.php');
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid service ID.");
}

$service_id = (int) $_GET['id'];

$query = mysqli_query($conn, "
  SELECT s.*, u.name AS provider_name, u.company_name, u.status AS provider_status, u.rating AS provider_rating
  FROM services s
  JOIN users u ON s.provider_id = u.id
  WHERE s.id = $service_id
");

if (mysqli_num_rows($query) === 0) {
  die("Service not found.");
}

$service = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Service Details - Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="../assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      font-family: 'Inter', sans-serif;
    }
    .glass-container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border-radius: 20px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 40px;
      margin-top: 40px;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    }
    .gradient-title {
      background: linear-gradient(to right, #6a11cb 0%, #2575fc 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .badge-status {
      font-size: 14px;
      padding: 5px 10px;
    }
  </style>
</head>
<body class="modern-bg">

  <div class="container">
    <a href="admin_services.php" class="btn btn-outline-secondary mt-4">
      <i class="bi bi-arrow-left"></i> Back to Services
    </a>

    <div class="glass-container">
      <h1 class="gradient-title fw-bold mb-4"><i class="bi bi-box-seam-fill me-2"></i><?= htmlspecialchars($service['title']) ?></h1>

      <div class="row mb-4">
        <div class="col-md-6">
          <h5 class="text-primary fw-semibold mb-3">Service Information</h5>
          <p><strong>Type:</strong> <?= htmlspecialchars($service['type']) ?></p>
          <p><strong>Price:</strong> $<?= number_format($service['price'], 2) ?></p>
          <p><strong>Route:</strong> <?= htmlspecialchars($service['city_from']) ?> → <?= htmlspecialchars($service['city_to']) ?></p>
          <p><strong>Available From:</strong> <?= $service['available_from'] ?: 'N/A' ?></p>
          <p><strong>Available To:</strong> <?= $service['available_to'] ?: 'N/A' ?></p>
          <p><strong>Status:</strong>
            <span class="badge badge-status <?= $service['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
              <?= ucfirst($service['status']) ?>
            </span>
          </p>
        </div>
        <div class="col-md-6">
          <h5 class="text-primary fw-semibold mb-3">Provider Information</h5>
          <p><strong>Name:</strong> <?= htmlspecialchars($service['provider_name']) ?></p>
          <p><strong>Company:</strong> <?= htmlspecialchars($service['company_name']) ?></p>
          <p><strong>Rating:</strong> <?= number_format($service['provider_rating'], 1) ?> / 5</p>
          <p><strong>Status:</strong>
            <span class="badge badge-status <?= $service['provider_status'] === 'active' ? 'bg-success' : 'bg-warning text-dark' ?>">
              <?= ucfirst($service['provider_status']) ?>
            </span>
          </p>
        </div>
      </div>

      <h5 class="text-primary fw-semibold mb-3">Description</h5>
      <p><?= htmlspecialchars($service['description'] ?: 'No description provided.') ?></p>

      <div class="text-end mt-4">
        <a href="admin_service_edit.php?id=<?= $service['id'] ?>" class="btn btn-primary px-4">
          <i class="bi bi-pencil-square me-1"></i>Edit Service
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

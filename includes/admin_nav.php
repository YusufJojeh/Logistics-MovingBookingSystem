<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold fs-3" href="/admin.php">
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
        <li class="nav-item"><a class="nav-link" href="admin_services.php">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_bookings.php">Bookings</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_reviews.php">Reviews</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_settings.php">Settings</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_audit.php">Audit Log</a></li>
        <li class="nav-item ms-3">
          <div class="dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?= get_profile_image_html($user, 'small', false) ?>
              <span class="ms-2"><?= htmlspecialchars($user['name']) ?></span>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="admin_profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav> 
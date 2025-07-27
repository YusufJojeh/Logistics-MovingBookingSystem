<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
if (!is_client()) { header('Location: /login.php'); exit; }
$user = current_user();
$client_id = $user['id'];

// Get user statistics
$stats = mysqli_query($conn, "
    SELECT 
        (SELECT COUNT(*) FROM bookings WHERE client_id = $client_id) as total_bookings,
        (SELECT COUNT(*) FROM bookings WHERE client_id = $client_id AND status = 'completed') as completed_bookings,
        (SELECT COUNT(*) FROM reviews WHERE reviewer_id = $client_id) as total_reviews,
        (SELECT AVG(rating) FROM reviews WHERE reviewer_id = $client_id) as avg_rating,
        (SELECT COUNT(*) FROM bookings WHERE client_id = $client_id AND status = 'completed') as total_completed
");

$stats_data = mysqli_fetch_assoc($stats);

$success = $error = '';
// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'profile') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        if (!$name || !$email) {
            $error = 'Name and email are required.';
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET name=?, email=?, phone=? WHERE id=? AND role='client'");
            mysqli_stmt_bind_param($stmt, 'sssi', $name, $email, $phone, $client_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $success = 'Profile updated successfully!';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'password') {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if (!$old || !$new || !$confirm) {
            $error = 'All password fields are required.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } else {
            $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'i', $client_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            if (!$row || !password_verify($old, $row['password'])) {
                $error = 'Old password is incorrect.';
            } else {
                $hash = password_hash($new, PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, 'si', $hash, $client_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $success = 'Password changed successfully!';
            }
        }
    }
    $user = current_user(); // Refresh
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - Client Dashboard</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
  <!-- Professional Navigation -->
  <nav class="navbar navbar-expand-lg navbar-glass">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="dashboard_client.php">
        <i class="bi bi-person-circle me-2"></i>Client<span class="text-gradient-secondary">&</span>Dashboard
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#clientNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="clientNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="client_services.php">Browse Services</a></li>
          <li class="nav-item"><a class="nav-link" href="client_bookings.php">My Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="client_reviews.php">My Reviews</a></li>
          <li class="nav-item"><a class="nav-link active" href="client_profile.php">Profile</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">My Profile</h1>
            <p class="lead mb-0">Manage your account settings and personal information</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10">
        <h2 class="gradient-text mb-4 text-center">Account Overview</h2>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total bookings made">
          <div class="kpi-icon">
            <i class="bi bi-calendar-check"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_bookings'] ?></div>
          <div class="kpi-label">Total Bookings</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Active
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Successfully completed bookings">
          <div class="kpi-icon">
            <i class="bi bi-check-circle"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['completed_bookings'] ?></div>
          <div class="kpi-label">Completed</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> <?= $stats_data['total_bookings'] > 0 ? round(($stats_data['completed_bookings'] / $stats_data['total_bookings']) * 100) : 0 ?>%
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Reviews submitted">
          <div class="kpi-icon">
            <i class="bi bi-star"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_reviews'] ?></div>
          <div class="kpi-label">Reviews</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Feedback
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Average rating given">
          <div class="kpi-icon">
            <i class="bi bi-star-fill"></i>
          </div>
          <div class="kpi-number"><?= number_format($stats_data['avg_rating'] ?? 0, 1) ?></div>
          <div class="kpi-label">Avg Rating</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Excellent
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Total completed bookings">
          <div class="kpi-icon">
            <i class="bi bi-check-circle-fill"></i>
          </div>
          <div class="kpi-number"><?= $stats_data['total_completed'] ?></div>
          <div class="kpi-label">Completed</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Successful
          </div>
        </div>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="kpi-card" data-tooltip="Account status">
          <div class="kpi-icon">
            <i class="bi bi-person-check"></i>
          </div>
          <div class="kpi-number"><?= $user['status'] === 'active' ? 'Active' : 'Inactive' ?></div>
          <div class="kpi-label">Status</div>
          <div class="kpi-trend positive">
            <i class="bi bi-arrow-up"></i> Verified
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Profile Management Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="gradient-text mb-0">
              <i class="bi bi-person-circle me-2"></i>Profile Management
            </h2>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="scrollToPassword()">
                <i class="bi bi-shield-lock me-1"></i>Change Password
              </button>
            </div>
          </div>
          
          <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>
          
          <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
              <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>

          <!-- Profile Information -->
          <div class="mb-5">
            <h3 class="gradient-text mb-4">
              <i class="bi bi-person me-2"></i>Personal Information
            </h3>
            <form method="POST" class="row g-3">
              <input type="hidden" name="action" value="profile">
              
              <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">
                  <i class="bi bi-person me-2"></i>Full Name
                </label>
                <input type="text" class="form-control form-control-lg" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
              </div>
              
              <div class="col-md-6">
                <label for="email" class="form-label fw-semibold">
                  <i class="bi bi-envelope me-2"></i>Email Address
                </label>
                <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
              </div>
              
              <div class="col-md-6">
                <label for="phone" class="form-label fw-semibold">
                  <i class="bi bi-telephone me-2"></i>Phone Number
                </label>
                <input type="tel" class="form-control form-control-lg" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="+1234567890">
              </div>
              
              <div class="col-md-6">
                <label class="form-label fw-semibold">
                  <i class="bi bi-calendar me-2"></i>Member Since
                </label>
                <input type="text" class="form-control form-control-lg" value="<?= date('M d, Y', strtotime($user['created_at'])) ?>" readonly>
              </div>
              
              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="bi bi-check-circle me-2"></i>Save Profile Changes
                </button>
              </div>
            </form>
          </div>

          <!-- Change Password -->
          <div id="change-password">
            <h3 class="gradient-text mb-4">
              <i class="bi bi-shield-lock me-2"></i>Change Password
            </h3>
            <form method="POST" class="row g-3">
              <input type="hidden" name="action" value="password">
              
              <div class="col-md-6">
                <label for="old_password" class="form-label fw-semibold">
                  <i class="bi bi-lock me-2"></i>Current Password
                </label>
                <div class="input-group">
                  <input type="password" class="form-control form-control-lg" id="old_password" name="old_password" required>
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('old_password')">
                    <i class="bi bi-eye" id="old_password_icon"></i>
                  </button>
                </div>
              </div>
              
              <div class="col-md-6">
                <label for="new_password" class="form-label fw-semibold">
                  <i class="bi bi-key me-2"></i>New Password
                </label>
                <div class="input-group">
                  <input type="password" class="form-control form-control-lg" id="new_password" name="new_password" required minlength="6">
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                    <i class="bi bi-eye" id="new_password_icon"></i>
                  </button>
                </div>
                <div class="form-text">
                  <i class="bi bi-info-circle me-1"></i>Password must be at least 6 characters long
                </div>
              </div>
              
              <div class="col-md-6">
                <label for="confirm_password" class="form-label fw-semibold">
                  <i class="bi bi-key-fill me-2"></i>Confirm New Password
                </label>
                <div class="input-group">
                  <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" required minlength="6">
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                    <i class="bi bi-eye" id="confirm_password_icon"></i>
                  </button>
                </div>
              </div>
              
              <div class="col-md-6">
                <label class="form-label fw-semibold">
                  <i class="bi bi-shield-check me-2"></i>Password Strength
                </label>
                <div class="progress mb-2" style="height: 8px;">
                  <div class="progress-bar" id="password-strength" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted" id="password-feedback">Enter a password to check strength</small>
              </div>
              
              <div class="col-12">
                <button type="submit" class="btn btn-secondary btn-lg">
                  <i class="bi bi-shield-lock me-2"></i>Change Password
                </button>
              </div>
            </form>
          </div>

          <!-- Account Security -->
          <div class="mt-5">
            <h3 class="gradient-text mb-4">
              <i class="bi bi-shield-check me-2"></i>Account Security
            </h3>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="glass-card p-3">
                  <div class="d-flex align-items-center">
                    <div class="user-avatar me-3">
                      <i class="bi bi-shield-check text-success"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">Account Status</div>
                      <small class="text-muted"><?= ucfirst($user['status']) ?></small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="glass-card p-3">
                  <div class="d-flex align-items-center">
                    <div class="user-avatar me-3">
                      <i class="bi bi-calendar-check text-primary"></i>
                    </div>
                    <div>
                      <div class="fw-semibold">Last Login</div>
                      <small class="text-muted">Recently active</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

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

    // Toggle password visibility
    function togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const icon = document.getElementById(fieldId + '_icon');
      
      if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
      } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
      }
    }

    // Password strength checker
    function checkPasswordStrength(password) {
      let strength = 0;
      let feedback = '';
      
      if (password.length >= 6) strength += 25;
      if (password.length >= 8) strength += 25;
      if (/[a-z]/.test(password)) strength += 25;
      if (/[A-Z]/.test(password)) strength += 25;
      if (/[0-9]/.test(password)) strength += 25;
      if (/[^A-Za-z0-9]/.test(password)) strength += 25;
      
      if (strength < 25) {
        feedback = 'Very Weak';
        document.getElementById('password-strength').className = 'progress-bar bg-danger';
      } else if (strength < 50) {
        feedback = 'Weak';
        document.getElementById('password-strength').className = 'progress-bar bg-warning';
      } else if (strength < 75) {
        feedback = 'Good';
        document.getElementById('password-strength').className = 'progress-bar bg-info';
      } else {
        feedback = 'Strong';
        document.getElementById('password-strength').className = 'progress-bar bg-success';
      }
      
      document.getElementById('password-strength').style.width = Math.min(strength, 100) + '%';
      document.getElementById('password-feedback').textContent = feedback;
    }

    // Add password strength checking
    document.getElementById('new_password').addEventListener('input', function() {
      checkPasswordStrength(this.value);
    });

    // Scroll to password section
    function scrollToPassword() {
      document.getElementById('change-password').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  </script>
</body>
</html> 
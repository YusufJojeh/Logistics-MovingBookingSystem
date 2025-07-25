<?php
require_once __DIR__ . '/../includes/auth.php';
// require_once __DIR__ . '/../includes/nav.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'client';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $company = $role === 'provider' ? trim($_POST['company_name'] ?? '') : null;

    if (!$name || !$email || !$password || ($role === 'provider' && !$company)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check for duplicate email
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Email already registered.';
        } else {
            if (register_user($name, $email, $password, $role, $company)) {
                $success = 'Registration successful! You can now <a href="/login.php">login</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Logistics & Moving Booking System</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
  <nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="/index.php">Logistics<span class="text-primary">&</span>Moving</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="/index.php#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="/index.php#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="/index.php#testimonials">Testimonials</a></li>
          <li class="nav-item"><a class="nav-link" href="/index.php#contact">Contact</a></li>
          <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="/register.php">Get Started</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO SECTION -->
  <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative" style="min-height: 100vh;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="glass-card p-5 mb-4">
            <h1 class="display-2 fw-black mb-3 gradient-text">Create Your Free Account</h1>
            <p class="lead mb-4 fs-4 text-body-secondary">Join the world’s most advanced logistics & moving platform. Compare, book, and manage all your transport needs in one beautiful, secure place.</p>
            <?php if ($error): ?>
              <div class="alert alert-danger"> <?= $error ?> </div>
            <?php elseif ($success): ?>
              <div class="alert alert-success"> <?= $success ?> </div>
            <?php endif; ?>
            <form method="POST" action="register.php" autocomplete="off" class="mx-auto" style="max-width: 420px;">
              <div class="mb-3 text-start">
                <label for="role" class="form-label fw-bold">Register as</label>
                <select class="form-select" id="role" name="role" required onchange="document.getElementById('company-group').style.display = this.value === 'provider' ? 'block' : 'none';">
                  <option value="client" <?= isset($_POST['role']) && $_POST['role'] === 'client' ? 'selected' : '' ?>>Client</option>
                  <option value="provider" <?= isset($_POST['role']) && $_POST['role'] === 'provider' ? 'selected' : '' ?>>Provider</option>
                </select>
              </div>
              <div class="mb-3 text-start" id="company-group" style="display:<?= (isset($_POST['role']) && $_POST['role'] === 'provider') ? 'block' : 'none' ?>;">
                <label for="company_name" class="form-label fw-bold">Company Name</label>
                <input type="text" class="form-control" id="company_name" name="company_name" value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
              </div>
              <div class="mb-3 text-start">
                <label for="name" class="form-label fw-bold">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
              </div>
              <div class="mb-3 text-start">
                <label for="email" class="form-label fw-bold">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              </div>
              <div class="mb-3 text-start">
                <label for="password" class="form-label fw-bold">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="mb-3 text-start">
                <label for="confirm_password" class="form-label fw-bold">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
              </div>
              <button type="submit" class="btn btn-primary w-100 py-2 fw-bold fs-5 mt-2">Register</button>
            </form>
            <div class="text-center mt-4">
              <span class="text-body-secondary">Already have an account?</span>
              <a href="login.php" class="fw-bold ms-2">Login</a>
            </div>
            <div class="hero-svg mt-4">
              <!-- Custom SVG illustration -->
              
            </div>
          </div>
        </div>
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
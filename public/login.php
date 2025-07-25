<?php
require_once __DIR__ . '/../includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        if (login_user($email, $password)) {
            // Redirect based on role
            if (is_admin()) {
                header('Location: admin.php');
            } elseif (is_provider()) {
                header('Location: dashboard_provider.php');
            } else {
                header('Location: dashboard_client.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Logistics & Moving Booking System</title>
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
            <h1 class="display-2 fw-black mb-3 gradient-text">Welcome Back</h1>
            <p class="lead mb-4 fs-4 text-body-secondary">Log in to your dashboard and manage your logistics, bookings, and more—all in one place.</p>
            <?php if ($error): ?>
              <div class="alert alert-danger"> <?= $error ?> </div>
            <?php endif; ?>
            <form method="POST" action="login.php" autocomplete="off" class="mx-auto" style="max-width: 420px;">
              <div class="mb-3 text-start">
                <label for="email" class="form-label fw-bold">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              </div>
              <div class="mb-3 text-start">
                <label for="password" class="form-label fw-bold">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <button type="submit" class="btn btn-primary w-100 py-2 fw-bold fs-5 mt-2">Login</button>
            </form>
            <div class="text-center mt-4">
              <span class="text-body-secondary">Don't have an account?</span>
              <a href="register.php" class="fw-bold ms-2">Register</a>
            </div>
            <div class="hero-svg mt-4">

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
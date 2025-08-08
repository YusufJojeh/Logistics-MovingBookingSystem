<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        $result = login_user($email, $password);
        if ($result === true) {
            // Redirect based on user role
            $user = current_user();
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } elseif ($user['role'] === 'provider') {
                header('Location: dashboard_provider.php');
            } else {
                header('Location: dashboard_client.php');
            }
            exit;
        } else {
            $error = $result;
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
      <a class="navbar-brand fw-bold fs-3" href="index.php">
        <img src="../assets/img/logo.svg" alt="MovePro Logo" class="logo-svg">
        <span class="logo-text-white">Move</span><span class="logo-text-blue">Pro</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
          <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="register.php">Register</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 40vh;">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="glass-card">
            <h1 class="display-4 fw-black mb-3 gradient-text">Welcome Back</h1>
            <p class="lead mb-0">Sign in to your account and continue managing your logistics</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Login Form Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-5 col-md-7">
        <div class="glass-card">
          <div class="text-center mb-4">
            <div class="login-icon mb-3">
              <i class="bi bi-person-circle"></i>
            </div>
            <h2 class="gradient-text mb-2">Sign In</h2>
            <p class="text-muted">Enter your credentials to access your account</p>
          </div>
          
          <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success" role="alert">
              <i class="bi bi-check-circle me-2"></i>Registration successful! Please sign in with your credentials.
            </div>
          <?php endif; ?>

          <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <form method="POST" class="needs-validation" novalidate>
            <div class="mb-4">
              <label for="email" class="form-label fw-semibold">
                <i class="bi bi-envelope me-2"></i>Email Address
              </label>
              <input type="email" class="form-control form-control-lg" id="email" name="email" 
                     placeholder="Enter your email address" required 
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              <div class="invalid-feedback">
                Please enter a valid email address.
              </div>
            </div>

            <div class="mb-4">
              <label for="password" class="form-label fw-semibold">
                <i class="bi bi-lock me-2"></i>Password
              </label>
              <div class="input-group">
                <input type="password" class="form-control form-control-lg" id="password" name="password" 
                       placeholder="Enter your password" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
              <div class="invalid-feedback">
                Please enter your password.
              </div>
            </div>

            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rememberMe">
                <label class="form-check-label" for="rememberMe">
                  Remember me for 30 days
                </label>
              </div>
            </div>

            <div class="d-grid mb-4">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
              </button>
            </div>

            <div class="text-center">
              <p class="mb-3">Don't have an account? 
                <a href="register.php" class="text-decoration-none gradient-text fw-semibold">Create one here</a>
              </p>
              <a href="#" class="text-decoration-none text-muted">
                <i class="bi bi-question-circle me-1"></i>Forgot your password?
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="gradient-text mb-4">Why Choose Our Platform?</h2>
        <p class="lead">Professional features designed for seamless logistics management</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="feature-glass-card text-center">
          <div class="feature-icon mb-3">
            <i class="bi bi-shield-check"></i>
          </div>
          <h4 class="gradient-text mb-3">Secure & Reliable</h4>
          <p>Your data and transactions are protected with enterprise-grade security. All providers are verified and insured.</p>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="feature-glass-card text-center">
          <div class="feature-icon mb-3">
            <i class="bi bi-graph-up"></i>
          </div>
          <h4 class="gradient-text mb-3">Real-Time Tracking</h4>
          <p>Track your shipments in real-time with our advanced tracking system. Get updates on location, status, and estimated delivery times.</p>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="feature-glass-card text-center">
          <div class="feature-icon mb-3">
            <i class="bi bi-headset"></i>
          </div>
          <h4 class="gradient-text mb-3">24/7 Support</h4>
          <p>Round-the-clock customer support available via phone, email, and live chat. We're here when you need us.</p>
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
    // Password toggle functionality
    document.getElementById('togglePassword').addEventListener('click', function() {
      const password = document.getElementById('password');
      const icon = this.querySelector('i');
      
      if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    });

    // Form validation
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  </script>
</body>
</html> 
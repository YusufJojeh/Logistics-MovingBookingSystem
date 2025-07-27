<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $company_name = trim($_POST['company_name'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($role)) {
        $errors[] = "Please select a role";
    }
    
    if ($role === 'provider' && empty($company_name)) {
        $errors[] = "Company name is required for service providers";
    }
    
    // Check if email already exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Email address is already registered";
    }
    mysqli_stmt_close($stmt);
    
    if (empty($errors)) {
        $result = register_user($name, $email, $password, $role, $company_name);
        if ($result) {
            header('Location: login.php?registered=1');
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Logistics & Moving Booking System</title>
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
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="index.php">
        <i class="bi bi-truck me-2"></i>Logistics<span class="text-gradient-secondary">&</span>Moving
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
          <li class="nav-item ms-3"><a class="btn btn-outline-primary px-4" href="login.php">Login</a></li>
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
            <h1 class="display-4 fw-black mb-3 gradient-text">Join Our Platform</h1>
            <p class="lead mb-0">Create your account and start your logistics journey today</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Registration Form Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <div class="glass-card">
          <div class="text-center mb-4">
            <div class="register-icon mb-3">
              <i class="bi bi-person-plus"></i>
            </div>
            <h2 class="gradient-text mb-2">Create Account</h2>
            <p class="text-muted">Fill in your details to get started</p>
          </div>
          
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                  <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="POST" class="needs-validation" novalidate>
            <div class="row">
              <div class="col-12 mb-4">
                <label for="name" class="form-label fw-semibold">
                  <i class="bi bi-person me-2"></i>Full Name
                </label>
                <input type="text" class="form-control form-control-lg" id="name" name="name" 
                       placeholder="Enter your full name" required 
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                <div class="invalid-feedback">
                  Please enter your full name.
                </div>
              </div>

              <div class="col-12 mb-4">
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

              <div class="col-md-6 mb-4">
                <label for="password" class="form-label fw-semibold">
                  <i class="bi bi-lock me-2"></i>Password
                </label>
                <div class="input-group">
                  <input type="password" class="form-control form-control-lg" id="password" name="password" 
                         placeholder="Create a password" required>
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div class="invalid-feedback">
                  Please create a password (minimum 6 characters).
                </div>
              </div>

              <div class="col-md-6 mb-4">
                <label for="confirm_password" class="form-label fw-semibold">
                  <i class="bi bi-lock-fill me-2"></i>Confirm Password
                </label>
                <div class="input-group">
                  <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" 
                         placeholder="Confirm your password" required>
                  <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
                <div class="invalid-feedback">
                  Please confirm your password.
                </div>
              </div>

              <div class="col-12 mb-4">
                <label for="role" class="form-label fw-semibold">
                  <i class="bi bi-person-badge me-2"></i>Account Type
                </label>
                <select class="form-select form-select-lg" id="role" name="role" required>
                  <option value="">Select your account type</option>
                  <option value="client" <?= ($_POST['role'] ?? '') === 'client' ? 'selected' : '' ?>>Client - I need logistics services</option>
                  <option value="provider" <?= ($_POST['role'] ?? '') === 'provider' ? 'selected' : '' ?>>Service Provider - I offer logistics services</option>
                </select>
                <div class="invalid-feedback">
                  Please select your account type.
                </div>
              </div>

              <div class="col-12 mb-4" id="companyNameField" style="display: none;">
                <label for="company_name" class="form-label fw-semibold">
                  <i class="bi bi-building me-2"></i>Company Name
                </label>
                <input type="text" class="form-control form-control-lg" id="company_name" name="company_name" 
                       placeholder="Enter your company name" 
                       value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
                <div class="form-text">Required for service providers</div>
              </div>
            </div>

            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                <label class="form-check-label" for="agreeTerms">
                  I agree to the <a href="#terms" class="text-decoration-none gradient-text">Terms of Service</a> and 
                  <a href="#privacy" class="text-decoration-none gradient-text">Privacy Policy</a>
                </label>
                <div class="invalid-feedback">
                  You must agree to the terms and conditions.
                </div>
              </div>
            </div>

            <div class="d-grid mb-4">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-person-plus me-2"></i>Create Account
              </button>
            </div>

            <div class="text-center">
              <p class="mb-0">Already have an account? 
                <a href="login.php" class="text-decoration-none gradient-text fw-semibold">Sign in here</a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Benefits Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="gradient-text mb-4">Platform Benefits</h2>
        <p class="lead">Discover what makes our platform the preferred choice for logistics</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="feature-glass-card text-center">
          <div class="feature-icon mb-3">
            <i class="bi bi-speedometer2"></i>
          </div>
          <h4 class="gradient-text mb-3">Fast & Efficient</h4>
          <p>Quick access to your dashboard and services. Streamlined workflows for maximum productivity.</p>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="feature-glass-card text-center">
          <div class="feature-icon mb-3">
            <i class="bi bi-graph-up"></i>
          </div>
          <h4 class="gradient-text mb-3">Real-Time Analytics</h4>
          <p>Track your bookings, revenue, and performance with comprehensive analytics and insights.</p>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="feature-glass-card text-center">
          <div class="feature-icon mb-3">
            <i class="bi bi-people"></i>
          </div>
          <h4 class="gradient-text mb-3">Community Driven</h4>
          <p>Connect with verified service providers and clients in our trusted logistics community.</p>
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
    function togglePasswordVisibility(inputId, buttonId) {
      document.getElementById(buttonId).addEventListener('click', function() {
        const input = document.getElementById(inputId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('bi-eye');
          icon.classList.add('bi-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('bi-eye-slash');
          icon.classList.add('bi-eye');
        }
      });
    }

    togglePasswordVisibility('password', 'togglePassword');
    togglePasswordVisibility('confirm_password', 'toggleConfirmPassword');

    // Role selection handling
    document.getElementById('role').addEventListener('change', function() {
      const companyField = document.getElementById('companyNameField');
      const companyInput = document.getElementById('company_name');
      
      if (this.value === 'provider') {
        companyField.style.display = 'block';
        companyInput.required = true;
      } else {
        companyField.style.display = 'none';
        companyInput.required = false;
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

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
      const password = this.value;
      const strength = 0;
      
      if (password.length >= 6) strength++;
      if (password.match(/[a-z]/)) strength++;
      if (password.match(/[A-Z]/)) strength++;
      if (password.match(/[0-9]/)) strength++;
      if (password.match(/[^a-zA-Z0-9]/)) strength++;
      
      // You can add visual feedback here if needed
    });
  </script>
</body>
</html> 
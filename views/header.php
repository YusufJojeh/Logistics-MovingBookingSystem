<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics & Moving Booking System</title>
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    :root {
      --primary: #667eea;
      --secondary: #764ba2;
      --accent: #f093fb;
      --warning: #f5576c;
      --info: #4facfe;
      --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --gradient-accent: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --background: #fff;
      --text: #111;
      --gray: #e5e7eb;
    }
    
    body {
      background: var(--background);
      color: var(--text);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    /* Stunning Navbar */
    .navbar {
      background: rgba(255, 255, 255, 0.1) !important;
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 0 0 2rem 2rem;
      box-shadow: 
        0 8px 32px rgba(102, 126, 234, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
      padding: 1rem 0;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    
    .navbar::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, 
        rgba(102, 126, 234, 0.1) 0%, 
        rgba(118, 75, 162, 0.1) 25%, 
        rgba(240, 147, 251, 0.1) 50%, 
        rgba(245, 87, 108, 0.1) 75%, 
        rgba(79, 172, 254, 0.1) 100%);
      border-radius: 0 0 2rem 2rem;
      z-index: -1;
    }
    
    .navbar-brand {
      font-size: 1.8rem;
      font-weight: 800;
      color: #fff !important;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      position: relative;
    }
    
    .navbar-brand::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #facc15, #fbbf24);
      transition: width 0.3s ease;
    }
    
    .navbar-brand:hover::after {
      width: 100%;
    }
    
    .navbar-brand:hover {
      transform: scale(1.05);
      text-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .navbar-brand i {
      color: #facc15;
      margin-right: 0.5rem;
      animation: bounce 2s infinite;
      filter: drop-shadow(0 2px 4px rgba(250, 204, 21, 0.3));
    }
    
    .navbar-nav .nav-link {
      color: #fff !important;
      font-weight: 600;
      padding: 0.75rem 1.5rem !important;
      margin: 0 0.25rem;
      border-radius: 2rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
    }
    
    .navbar-nav .nav-link::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .navbar-nav .nav-link:hover::before {
      left: 100%;
    }
    
    .navbar-nav .nav-link::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #facc15, #fbbf24);
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }
    
    .navbar-nav .nav-link:hover::after {
      width: 80%;
    }
    
    .navbar-nav .nav-link:hover {
      background: rgba(255,255,255,0.15) !important;
      color: #fff !important;
      transform: translateY(-2px);
      box-shadow: 
        0 8px 25px rgba(0,0,0,0.15),
        inset 0 1px 0 rgba(255,255,255,0.2);
      border-color: rgba(255, 255, 255, 0.3);
    }
    
    .navbar-nav .nav-link.active {
      background: rgba(250, 204, 21, 0.2) !important;
      color: #facc15 !important;
      box-shadow: 
        0 8px 25px rgba(250,204,21,0.3),
        inset 0 1px 0 rgba(250, 204, 21, 0.3);
      border-color: rgba(250, 204, 21, 0.4);
    }
    
    .navbar-nav .btn-warning {
      background: rgba(250, 204, 21, 0.9) !important;
      color: #111 !important;
      font-weight: 700;
      border-radius: 2rem;
      padding: 0.75rem 2rem !important;
      box-shadow: 
        0 8px 25px rgba(250,204,21,0.4),
        inset 0 1px 0 rgba(255,255,255,0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid rgba(250, 204, 21, 0.3);
      backdrop-filter: blur(10px);
      position: relative;
      overflow: hidden;
    }
    
    .navbar-nav .btn-warning::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .navbar-nav .btn-warning:hover::before {
      left: 100%;
    }
    
    .navbar-nav .btn-warning:hover {
      background: rgba(37, 99, 235, 0.9) !important;
      color: #fff !important;
      transform: translateY(-3px);
      box-shadow: 
        0 12px 35px rgba(37,99,235,0.5),
        inset 0 1px 0 rgba(255,255,255,0.3);
      border-color: rgba(37, 99, 235, 0.4);
    }
    
    .navbar-toggler {
      border: none;
      padding: 0.5rem;
      border-radius: 0.5rem;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }
    
    .navbar-toggler:focus {
      box-shadow: none;
      background: rgba(255,255,255,0.2);
      border-color: rgba(255, 255, 255, 0.4);
    }
    
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    /* Mobile Responsive */
    @media (max-width: 991.98px) {
      .navbar-collapse {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        margin-top: 1rem;
        padding: 1rem;
        box-shadow: 
          0 8px 32px rgba(102, 126, 234, 0.15),
          inset 0 1px 0 rgba(255, 255, 255, 0.2);
      }
      
      .navbar-nav .nav-link {
        margin: 0.5rem 0;
        text-align: center;
        font-size: 1.1rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
      }
      
      .navbar-brand {
        font-size: 1.5rem;
      }
    }
    
    /* Animations */
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-10px);
      }
      60% {
        transform: translateY(-5px);
      }
    }
    
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* General Styles */
    .card {
      background: #fff;
      border: 1px solid var(--gray);
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      border-radius: 1rem;
      transition: all 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .btn-primary {
      background: var(--gradient-primary);
      border: none;
      color: #fff;
      font-weight: 600;
      border-radius: 2rem;
      padding: 0.75rem 2rem;
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .btn-warning {
      background: #facc15;
      border: none;
      color: #111;
      font-weight: 700;
      border-radius: 2rem;
      padding: 0.75rem 2rem;
      transition: all 0.3s ease;
    }
    
    .btn-warning:hover {
      background: #2563eb;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(37,99,235,0.3);
    }
    
    .form-control, .form-select {
      border-radius: 1rem;
      border: 2px solid var(--gray);
      padding: 0.75rem 1rem;
      transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .alert {
      border-radius: 1rem;
      border: none;
      font-weight: 500;
    }
    
    .alert-success {
      background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
      color: #fff;
    }
    
    .alert-danger {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: #fff;
    }
    
    .alert-info {
      background: var(--gradient-primary);
      color: #fff;
    }
  </style>
</head>
<body>
  <!-- Stunning Navbar -->
  <nav class="navbar navbar-expand-lg animate__animated animate__fadeInDown">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <i class="fas fa-shield-alt"></i>
        <span style="color:#facc15;">Logistics</span> <span class="d-none d-md-inline">& Moving</span>
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($user) && $user->type === 'provider'): ?>
              <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                  <i class="fas fa-tachometer-alt fa-fw me-1"></i>Dashboard
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="profile.php">
                  <i class="fas fa-user fa-fw me-1"></i>Profile
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="services.php">
                  <i class="fas fa-box fa-fw me-1"></i>My Services
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="add_service.php">
                  <i class="fas fa-plus fa-fw me-1"></i>Add Service
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="manage_bookings.php">
                  <i class="fas fa-calendar-check fa-fw me-1"></i>Manage Bookings
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="logout.php">
                  <i class="fas fa-sign-out-alt fa-fw me-1"></i>Logout
                </a>
              </li>
            <?php elseif (isset($user) && $user->type === 'customer'): ?>
              <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                  <i class="fas fa-tachometer-alt fa-fw me-1"></i>Dashboard
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="profile.php">
                  <i class="fas fa-user fa-fw me-1"></i>Profile
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="services.php">
                  <i class="fas fa-truck fa-fw me-1"></i>Services
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="book_service.php">
                  <i class="fas fa-calendar-plus fa-fw me-1"></i>Book
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="logout.php">
                  <i class="fas fa-sign-out-alt fa-fw me-1"></i>Logout
                </a>
              </li>
            <?php elseif (isset($user) && $user->type === 'admin'): ?>
              <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                  <i class="fas fa-tachometer-alt fa-fw me-1"></i>Dashboard
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="admin_panel.php">
                  <i class="fas fa-user-shield fa-fw me-1"></i>Admin Panel
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="profile.php">
                  <i class="fas fa-user fa-fw me-1"></i>Profile
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="logout.php">
                  <i class="fas fa-sign-out-alt fa-fw me-1"></i>Logout
                </a>
              </li>
            <?php endif; ?>
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="services.php">Services</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link btn btn-warning" href="register.php">Sign Up</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
      <script>
      // Navbar scroll effect for liquid glass
      window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        const scrolled = window.pageYOffset;
        
        if (scrolled > 50) {
          navbar.style.background = 'rgba(255, 255, 255, 0.15) !important';
          navbar.style.backdropFilter = 'blur(25px) saturate(200%)';
          navbar.style.borderColor = 'rgba(255, 255, 255, 0.3)';
          navbar.style.boxShadow = `
            0 12px 40px rgba(102, 126, 234, 0.2),
            inset 0 1px 0 rgba(255, 255, 255, 0.3)
          `;
        } else {
          navbar.style.background = 'rgba(255, 255, 255, 0.1) !important';
          navbar.style.backdropFilter = 'blur(20px) saturate(180%)';
          navbar.style.borderColor = 'rgba(255, 255, 255, 0.2)';
          navbar.style.boxShadow = `
            0 8px 32px rgba(102, 126, 234, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.2)
          `;
        }
      });
    
    // Active link highlighting
    document.addEventListener('DOMContentLoaded', function() {
      const currentLocation = window.location.pathname;
      const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
      
      navLinks.forEach(link => {
        if (link.getAttribute('href') === currentLocation.split('/').pop()) {
          link.classList.add('active');
        }
      });
    });
  </script>
</body>
</html> 
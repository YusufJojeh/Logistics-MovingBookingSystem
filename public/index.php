<?php
require_once '../config/db.php';
require_once '../includes/auth.php';

// Marketing stats (best-effort)
[$total_services]   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM services WHERE status='active'"));
[$total_providers]  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='provider' AND status IN ('active','inactive')"));
[$total_bookings]   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings"));
[$avg_rating_raw]   = mysqli_fetch_row(mysqli_query($conn, "SELECT AVG(rating) FROM reviews"));
$avg_rating = $avg_rating_raw ? number_format((float)$avg_rating_raw, 1) : '0.0';

// Teaser services
$services = mysqli_query($conn, "
  SELECT s.*, u.name as provider_name,
         (SELECT AVG(r.rating) FROM reviews r JOIN bookings b ON r.booking_id=b.id WHERE b.service_id=s.id) as avg_rating,
         (SELECT COUNT(*) FROM reviews r JOIN bookings b ON r.booking_id=b.id WHERE b.service_id=s.id) as review_count
  FROM services s
  JOIN users u ON s.provider_id = u.id
  WHERE s.status='active'
  ORDER BY s.created_at DESC
  LIMIT 6
");

// Top testimonials (optional)
$testimonials = mysqli_query($conn, "
  SELECT r.comment, r.rating, c.name AS client_name, p.name AS provider_name
  FROM reviews r
  JOIN users c ON r.reviewer_id=c.id
  JOIN users p ON r.provider_id=p.id
  ORDER BY r.created_at DESC
  LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Logistics & Moving — Book Trusted Providers</title>
  <link rel="icon" href="../assets/img/favicon.ico" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body class="modern-bg">
  <!-- NAV -->
  <nav class="navbar navbar-expand-lg navbar-glass">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="index.php">
        <i class="bi bi-truck me-2"></i>Logistics<span class="text-gradient-secondary">&</span>Moving
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#marketingNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="marketingNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="#testimonials">Reviews</a></li>
          <li class="nav-item ms-3"><a class="btn btn-outline-primary px-4" href="login.php">Login</a></li>
          <li class="nav-item ms-2"><a class="btn btn-primary px-4" href="register.php">Get Started</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero-glass d-flex align-items-center text-center section-gradient" style="min-height: 60vh;">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="glass-card">
            <h1 class="display-4 fw-black mb-3 gradient-text">Book Logistics & Moving Services With Confidence</h1>
            <p class="lead mb-4">Discover verified providers, transparent pricing, and real customer reviews — all in one place.</p>
            <div class="d-flex justify-content-center gap-3">
              <a href="client_services.php" class="btn btn-primary btn-lg"><i class="bi bi-search me-2"></i>Browse Services</a>
              <a href="register.php" class="btn btn-outline-primary btn-lg"><i class="bi bi-person-plus me-2"></i>Create Account</a>
            </div>
            <div class="d-flex justify-content-center gap-4 mt-4 text-white-50">
              <div><i class="bi bi-box me-2"></i><?= (int)$total_services ?> Active Services</div>
              <div><i class="bi bi-building me-2"></i><?= (int)$total_providers ?> Providers</div>
              <div><i class="bi bi-people me-2"></i><?= (int)$total_bookings ?> Bookings</div>
              <div><i class="bi bi-star-fill text-warning me-2"></i><?= $avg_rating ?> Avg Rating</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES PREVIEW -->
  <section id="services" class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10 text-center">
        <h2 class="gradient-text mb-3">Popular Services</h2>
        <p class="text-muted">Handpicked active services from trusted providers</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <?php if ($services && mysqli_num_rows($services) > 0): ?>
            <?php while($s = mysqli_fetch_assoc($services)): ?>
              <div class="col-md-6 col-lg-4">
                <div class="service-3d-card h-100">
                  <div class="service-icon mb-3"><i class="bi bi-box-seam"></i></div>
                  <h5 class="fw-bold mb-2"><?= htmlspecialchars($s['title']) ?></h5>
                  <div class="mb-2 text-warning">
                    <?php $r = (float)$s['avg_rating']; for($i=1;$i<=5;$i++): ?>
                      <i class="bi bi-star<?= $i <= $r ? '-fill' : '' ?>"></i>
                    <?php endfor; ?>
                    <small class="text-muted ms-1">(<?= (int)$s['review_count'] ?>)</small>
                  </div>
                  <div class="mb-2"><i class="bi bi-building me-2"></i><?= htmlspecialchars($s['provider_name']) ?></div>
                  <div class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($s['city_from'] ?: 'N/A') ?> → <?= htmlspecialchars($s['city_to'] ?: 'N/A') ?></div>
                  <div class="fw-bold text-success mb-3">$<?= number_format((float)$s['price'], 2) ?></div>
                  <div class="d-flex gap-2">
                    <a href="login.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye me-1"></i>View</a>
                    <a href="login.php" class="btn btn-primary btn-sm"><i class="bi bi-calendar-check me-1"></i>Book Now</a>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="col-12 text-center text-muted">No services available yet. Check back soon.</div>
          <?php endif; ?>
        </div>
        <div class="text-center mt-4">
          <a href="login.php" class="btn btn-primary"><i class="bi bi-list-ul me-2"></i>View All Services</a>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features" class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="gradient-text mb-3">Why Choose Us</h2>
        <p class="lead">A modern platform to discover, book, and review logistics and moving services.</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <div class="col-md-4"><div class="feature-glass-card text-center"><div class="feature-icon mb-3"><i class="bi bi-shield-check"></i></div><h5 class="mb-2">Verified Providers</h5><p>All providers are verified and rated by real clients.</p></div></div>
          <div class="col-md-4"><div class="feature-glass-card text-center"><div class="feature-icon mb-3"><i class="bi bi-cash-coin"></i></div><h5 class="mb-2">Transparent Pricing</h5><p>Clear pricing with no surprises. Compare and choose confidently.</p></div></div>
          <div class="col-md-4"><div class="feature-glass-card text-center"><div class="feature-icon mb-3"><i class="bi bi-speedometer2"></i></div><h5 class="mb-2">Quick Booking</h5><p>Intuitive workflow to book services in minutes.</p></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section id="testimonials" class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-10 text-center">
        <h2 class="gradient-text mb-3">What Clients Say</h2>
        <p class="text-muted">Recent feedback from real bookings</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="row g-4">
          <?php if ($testimonials && mysqli_num_rows($testimonials) > 0): ?>
            <?php while($t = mysqli_fetch_assoc($testimonials)): ?>
              <div class="col-md-4">
                <div class="testimonial-glass-card h-100">
                  <div class="testimonial-rating mb-2">
                    <?php for($i=1;$i<=5;$i++): ?>
                      <i class="bi bi-star<?= $i <= (int)$t['rating'] ? '-fill' : '' ?>"></i>
                    <?php endfor; ?>
                  </div>
                  <div class="testimonial-text mb-3">“<?= htmlspecialchars($t['comment'] ?: 'Great service!') ?>”</div>
                  <div class="testimonial-author">
                    <strong><?= htmlspecialchars($t['client_name']) ?></strong>
                    <span>about <?= htmlspecialchars($t['provider_name']) ?></span>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="col-12 text-center text-muted">Reviews will appear here as bookings are completed.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="container-fluid py-6">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="cta-glass text-center">
          <h3 class="gradient-text mb-3">Ready to get started?</h3>
          <p class="mb-4">Create a free account to book services, track bookings, and leave reviews.</p>
          <a href="register.php" class="btn btn-primary btn-lg me-2"><i class="bi bi-person-plus me-2"></i>Sign Up Free</a>
          <a href="login.php" class="btn btn-outline-primary btn-lg"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</a>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer-glass text-center py-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <p class="mb-2">&copy; <?= date('Y'); ?> Logistics &amp; Moving Booking System. All rights reserved.</p>
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
</body>
</html>
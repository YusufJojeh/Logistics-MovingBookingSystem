<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics & Moving Booking System</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
  <nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold fs-3 gradient-text" href="#">Logistics<span class="text-primary">&</span>Moving</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
          <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="register.php">Get Started</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative">
    <div class="hero-bg-svg position-absolute w-100 h-100 top-0 start-0 z-n1"></div>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="glass-card p-5 mb-4">
            <h1 class="display-2 fw-black mb-3 gradient-text">Move Anything, Anywhere.<br><span class="text-body">Effortlessly.</span></h1>
            <p class="lead mb-4 fs-4 text-body-secondary">The world’s most advanced logistics & moving platform. Compare, book, and manage all your transport needs in one beautiful, secure place.</p>
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
              <a href="/register.php" class="btn btn-warning btn-lg px-5 fw-bold shadow">Start Free</a>
              <a href="/services.php" class="btn btn-outline-primary btn-lg px-5 fw-bold">Browse Services</a>
            </div>
            <div class="row g-3 mt-4">
              <div class="col-4">
                <div class="stat-glass">
                  <div class="fs-2 fw-bold gradient-text">5,000+</div>
                  <div class="small text-body-secondary">Happy Clients</div>
                </div>
              </div>
              <div class="col-4">
                <div class="stat-glass">
                  <div class="fs-2 fw-bold gradient-text">15,000+</div>
                  <div class="small text-body-secondary">Successful Moves</div>
                </div>
              </div>
              <div class="col-4">
                <div class="stat-glass">
                  <div class="fs-2 fw-bold gradient-text">4.98/5</div>
                  <div class="small text-body-secondary">Avg. Rating</div>
                </div>
              </div>
            </div>
          </div>
          <div class="hero-svg mt-4">
    
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SERVICES -->
  <section id="services" class="py-6">
    <div class="container">
      <h2 class="display-5 fw-bold text-center mb-5 gradient-text">Our Premium Services</h2>
      <div class="row g-4 justify-content-center">
        <div class="col-lg-4 col-md-6">
          <div class="service-3d-card p-4 h-100">
            <div class="icon-glass mb-3"><i class="fas fa-truck-moving fa-2x"></i></div>
            <h4 class="fw-bold mb-2">Professional Moving</h4>
            <p class="text-body-secondary mb-3">Expert moving for homes, offices, and businesses. Safe, efficient, and stress-free.</p>
            <ul class="list-unstyled mb-3">
              <li><i class="fas fa-check text-success me-2"></i>Full-service packing</li>
              <li><i class="fas fa-check text-success me-2"></i>Furniture protection</li>
              <li><i class="fas fa-check text-success me-2"></i>Insurance coverage</li>
            </ul>
            <div class="d-flex justify-content-between align-items-center">
              <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">From $99</span>
              <a href="/services.php" class="btn btn-outline-primary btn-sm">Learn More</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="service-3d-card p-4 h-100">
            <div class="icon-glass mb-3"><i class="fas fa-shipping-fast fa-2x"></i></div>
            <h4 class="fw-bold mb-2">Express Delivery</h4>
            <p class="text-body-secondary mb-3">Lightning-fast delivery for urgent shipments. Same-day and next-day options.</p>
            <ul class="list-unstyled mb-3">
              <li><i class="fas fa-check text-success me-2"></i>Real-time tracking</li>
              <li><i class="fas fa-check text-success me-2"></i>Same-day delivery</li>
              <li><i class="fas fa-check text-success me-2"></i>Signature confirmation</li>
            </ul>
            <div class="d-flex justify-content-between align-items-center">
              <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">From $19</span>
              <a href="/services.php" class="btn btn-outline-primary btn-sm">Learn More</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="service-3d-card p-4 h-100">
            <div class="icon-glass mb-3"><i class="fas fa-warehouse fa-2x"></i></div>
            <h4 class="fw-bold mb-2">Smart Storage</h4>
            <p class="text-body-secondary mb-3">Secure, climate-controlled storage with flexible terms and 24/7 access.</p>
            <ul class="list-unstyled mb-3">
              <li><i class="fas fa-check text-success me-2"></i>Climate control</li>
              <li><i class="fas fa-check text-success me-2"></i>24/7 access</li>
              <li><i class="fas fa-check text-success me-2"></i>Security monitoring</li>
            </ul>
            <div class="d-flex justify-content-between align-items-center">
              <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">From $49/mo</span>
              <a href="/services.php" class="btn btn-outline-primary btn-sm">Learn More</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section id="how" class="py-6 bg-gradient-light">
    <div class="container">
      <h2 class="display-5 fw-bold text-center mb-5 gradient-text">How It Works</h2>
      <div class="timeline-glass mx-auto">
        <div class="timeline-step">
          <div class="timeline-icon"><i class="fas fa-search-location"></i></div>
          <div class="timeline-content">
            <h5 class="fw-bold mb-1">Find Your Service</h5>
            <p class="mb-0 text-body-secondary">Browse our network of verified providers and compare services, prices, and reviews.</p>
          </div>
        </div>
        <div class="timeline-step">
          <div class="timeline-icon"><i class="fas fa-calendar-check"></i></div>
          <div class="timeline-content">
            <h5 class="fw-bold mb-1">Book Instantly</h5>
            <p class="mb-0 text-body-secondary">Choose your date, time, and service. Get instant confirmation and secure payment.</p>
          </div>
        </div>
        <div class="timeline-step">
          <div class="timeline-icon"><i class="fas fa-smile-beam"></i></div>
          <div class="timeline-content">
            <h5 class="fw-bold mb-1">Enjoy & Track</h5>
            <p class="mb-0 text-body-secondary">Monitor your service in real-time, receive updates, and rate your experience.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features" class="py-6">
    <div class="container">
      <h2 class="display-5 fw-bold text-center mb-5 gradient-text">Why Choose Us?</h2>
      <div class="row g-4 justify-content-center">
        <div class="col-md-3 col-6">
          <div class="feature-glass-card text-center p-4">
            <div class="icon-glass mb-2"><i class="fas fa-shield-alt fa-lg"></i></div>
            <h6 class="fw-bold mb-1">100% Secure & Insured</h6>
            <p class="small text-body-secondary mb-0">Comprehensive insurance and secure payment for every order.</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="feature-glass-card text-center p-4">
            <div class="icon-glass mb-2"><i class="fas fa-clock fa-lg"></i></div>
            <h6 class="fw-bold mb-1">24/7 Support</h6>
            <p class="small text-body-secondary mb-0">Round-the-clock customer support for all your needs.</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="feature-glass-card text-center p-4">
            <div class="icon-glass mb-2"><i class="fas fa-map-marker-alt fa-lg"></i></div>
            <h6 class="fw-bold mb-1">Real-time Tracking</h6>
            <p class="small text-body-secondary mb-0">Track your shipments and services in real-time.</p>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="feature-glass-card text-center p-4">
            <div class="icon-glass mb-2"><i class="fas fa-star fa-lg"></i></div>
            <h6 class="fw-bold mb-1">Verified Providers</h6>
            <p class="small text-body-secondary mb-0">All providers are thoroughly vetted and background-checked.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section id="testimonials" class="py-6 bg-gradient-light">
    <div class="container">
      <h2 class="display-5 fw-bold text-center mb-5 gradient-text">What Our Clients Say</h2>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <div class="testimonial-glass-card p-4">
                  <div class="d-flex align-items-center mb-3">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" class="rounded-circle me-3" width="56" height="56" alt="User">
                    <div>
                      <div class="fw-bold">Ahmed, Client</div>
                      <div class="stars text-warning">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                      </div>
                    </div>
                  </div>
                  <blockquote class="blockquote mb-0">“Booking a moving service was never this easy. Highly recommended!”</blockquote>
                </div>
              </div>
              <div class="carousel-item">
                <div class="testimonial-glass-card p-4">
                  <div class="d-flex align-items-center mb-3">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" class="rounded-circle me-3" width="56" height="56" alt="User">
                    <div>
                      <div class="fw-bold">Sara, Provider</div>
                      <div class="stars text-warning">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                      </div>
                    </div>
                  </div>
                  <blockquote class="blockquote mb-0">“I got more clients and managed all orders in one place.”</blockquote>
                </div>
              </div>
              <div class="carousel-item">
                <div class="testimonial-glass-card p-4">
                  <div class="d-flex align-items-center mb-3">
                    <img src="https://randomuser.me/api/portraits/men/65.jpg" class="rounded-circle me-3" width="56" height="56" alt="User">
                    <div>
                      <div class="fw-bold">Mohamed, Client</div>
                      <div class="stars text-warning">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                      </div>
                    </div>
                  </div>
                  <blockquote class="blockquote mb-0">“The platform is fast, secure, and the support is great!”</blockquote>
                </div>
              </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-glass sticky-bottom-cta text-center py-5">
    <div class="container">
      <h2 class="display-5 fw-bold mb-3 gradient-text">Ready to Transform Your Logistics?</h2>
      <p class="lead mb-4">Join thousands of satisfied customers who trust us with their moving and delivery needs. Start your journey today!</p>
      <a href="/register.php" class="btn btn-warning btn-lg px-5 fw-bold shadow">Get Started Free</a>
      <a href="/services.php" class="btn btn-outline-primary btn-lg px-5 fw-bold ms-2">Learn More</a>
    </div>
  </section>

  <footer class="footer-glass text-center py-4 mt-5">
    <small>&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</small>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/main.js"></script>
</body>
</html> 
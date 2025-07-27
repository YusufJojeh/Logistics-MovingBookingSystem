<?php
require_once '../config/config.php';
require_once '../config/multilanguage.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logistics & Moving Booking System - Professional Solutions</title>
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
          <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
          <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
          <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="register.php">Get Started</a></li>
          <li class="nav-item ms-2"><a class="btn btn-outline-primary px-4" href="login.php">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 80vh;">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="glass-card">
            <h1 class="display-3 fw-black mb-4 gradient-text">
              Professional Logistics & Moving Solutions
            </h1>
            <p class="lead mb-4 fs-5">
              Connect with trusted service providers for seamless logistics and moving experiences. 
              Book, track, and manage your shipments with our professional platform.
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
              <a href="register.php" class="btn btn-primary btn-lg px-5 py-3">
                <i class="bi bi-rocket-takeoff me-2"></i>Start Your Journey
              </a>
              <a href="#services" class="btn btn-outline-primary btn-lg px-5 py-3">
                <i class="bi bi-play-circle me-2"></i>Explore Services
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="container-fluid py-6 section-glass">
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="kpi-number">500+</div>
          <div class="kpi-label">Service Providers</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +12% this month
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-calendar-check-fill"></i>
          </div>
          <div class="kpi-number">10K+</div>
          <div class="kpi-label">Successful Bookings</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +8% this month
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-star-fill"></i>
          </div>
          <div class="kpi-number">98%</div>
          <div class="kpi-label">Customer Satisfaction</div>
          <div class="kpi-trend text-success">
            <i class="bi bi-arrow-up"></i> +2% this month
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="kpi-card text-center">
          <div class="kpi-icon mb-3">
            <i class="bi bi-headset"></i>
          </div>
          <div class="kpi-number">24/7</div>
          <div class="kpi-label">Support Available</div>
          <div class="kpi-trend text-info">
            <i class="bi bi-clock"></i> Always online
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="gradient-text mb-4">Our Services</h2>
        <p class="lead">Comprehensive logistics solutions tailored to your needs</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="service-3d-card text-center">
          <div class="service-icon mb-4">
            <i class="bi bi-house-fill"></i>
          </div>
          <h4 class="gradient-text mb-3">Home Moving</h4>
          <p>Professional home relocation services with packing, loading, and delivery. We handle everything from small apartments to large houses.</p>
          <ul class="service-features">
            <li><i class="bi bi-check-circle"></i> Professional packing</li>
            <li><i class="bi bi-check-circle"></i> Insurance coverage</li>
            <li><i class="bi bi-check-circle"></i> Furniture protection</li>
          </ul>
          <a href="register.php" class="btn btn-outline-primary mt-3">Learn More</a>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="service-3d-card text-center">
          <div class="service-icon mb-4">
            <i class="bi bi-building-fill"></i>
          </div>
          <h4 class="gradient-text mb-3">Office Relocation</h4>
          <p>Complete office relocation services including equipment, furniture, and IT infrastructure. Minimal downtime guaranteed.</p>
          <ul class="service-features">
            <li><i class="bi bi-check-circle"></i> Equipment handling</li>
            <li><i class="bi bi-check-circle"></i> IT infrastructure</li>
            <li><i class="bi bi-check-circle"></i> Minimal downtime</li>
          </ul>
          <a href="register.php" class="btn btn-outline-primary mt-3">Learn More</a>
        </div>
      </div>
      <div class="col-lg-4 col-md-6">
        <div class="service-3d-card text-center">
          <div class="service-icon mb-4">
            <i class="bi bi-box-seam-fill"></i>
          </div>
          <h4 class="gradient-text mb-3">Parcel Delivery</h4>
          <p>Fast and reliable parcel delivery services across the country. Track your packages in real-time with our advanced system.</p>
          <ul class="service-features">
            <li><i class="bi bi-check-circle"></i> Real-time tracking</li>
            <li><i class="bi bi-check-circle"></i> Express delivery</li>
            <li><i class="bi bi-check-circle"></i> Secure handling</li>
          </ul>
          <a href="register.php" class="btn btn-outline-primary mt-3">Learn More</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="gradient-text mb-4">Why Choose Us</h2>
        <p class="lead">Advanced features that make logistics simple and efficient</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="feature-glass-card">
          <div class="feature-icon mb-3">
            <i class="bi bi-shield-check"></i>
          </div>
          <h4 class="gradient-text mb-3">Verified Providers</h4>
          <p>All our service providers are thoroughly vetted and verified. We ensure quality and reliability for every booking.</p>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="feature-glass-card">
          <div class="feature-icon mb-3">
            <i class="bi bi-graph-up"></i>
          </div>
          <h4 class="gradient-text mb-3">Real-time Tracking</h4>
          <p>Track your shipments and bookings in real-time with our advanced GPS tracking system and live updates.</p>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="feature-glass-card">
          <div class="feature-icon mb-3">
            <i class="bi bi-credit-card"></i>
          </div>
          <h4 class="gradient-text mb-3">Secure Payments</h4>
          <p>Multiple secure payment options with escrow protection. Your money is safe until the service is completed.</p>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="feature-glass-card">
          <div class="feature-icon mb-3">
            <i class="bi bi-headset"></i>
          </div>
          <h4 class="gradient-text mb-3">24/7 Support</h4>
          <p>Round-the-clock customer support available via phone, email, and live chat. We're here when you need us.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section id="testimonials" class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="gradient-text mb-4">What Our Customers Say</h2>
        <p class="lead">Real feedback from satisfied customers</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="testimonial-glass-card">
          <div class="testimonial-rating mb-3">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
          </div>
          <p class="testimonial-text">"Excellent service! The team was professional, punctual, and handled our move with care. Highly recommended!"</p>
          <div class="testimonial-author">
            <strong>Ahmed Hassan</strong>
            <span>Home Owner</span>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="testimonial-glass-card">
          <div class="testimonial-rating mb-3">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
          </div>
          <p class="testimonial-text">"The office relocation was seamless. They managed everything perfectly and we were back to business in no time."</p>
          <div class="testimonial-author">
            <strong>Sarah Johnson</strong>
            <span>Business Owner</span>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="testimonial-glass-card">
          <div class="testimonial-rating mb-3">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
          </div>
          <p class="testimonial-text">"Fast delivery and excellent tracking. I always know where my packages are. Great service!"</p>
          <div class="testimonial-author">
            <strong>Mohammed Ali</strong>
            <span>Online Seller</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section id="faq" class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="gradient-text mb-4">Frequently Asked Questions</h2>
        <p class="lead">Find answers to common questions about our services</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="faq-accordion">
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                <i class="bi bi-question-circle me-2"></i>
                How do I book a service?
              </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faq">
              <div class="accordion-body">
                Booking a service is simple! Just register an account, browse available providers, select your preferred service, choose a date and time, and complete the payment. You'll receive confirmation and tracking details immediately.
              </div>
            </div>
          </div>
          
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                <i class="bi bi-shield-check me-2"></i>
                Are the service providers verified?
              </button>
            </h2>
            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faq">
              <div class="accordion-body">
                Yes, all our service providers undergo a thorough verification process including background checks, insurance verification, and service quality assessments. We only work with trusted, professional providers.
              </div>
            </div>
          </div>
          
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                <i class="bi bi-credit-card me-2"></i>
                What payment methods do you accept?
              </button>
            </h2>
            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faq">
              <div class="accordion-body">
                We accept all major credit cards, debit cards, and digital wallets. Payments are processed securely through our escrow system, ensuring your money is protected until the service is completed.
              </div>
            </div>
          </div>
          
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                <i class="bi bi-clock me-2"></i>
                How far in advance should I book?
              </button>
            </h2>
            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faq">
              <div class="accordion-body">
                We recommend booking at least 48 hours in advance for standard services. For peak seasons or large moves, booking 1-2 weeks ahead ensures availability of your preferred provider and time slot.
              </div>
            </div>
          </div>
          
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                <i class="bi bi-headset me-2"></i>
                What if I need to cancel or reschedule?
              </button>
            </h2>
            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faq">
              <div class="accordion-body">
                You can cancel or reschedule up to 24 hours before your scheduled service without any fees. Cancellations within 24 hours may incur a small fee. Contact our support team for assistance.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="container-fluid py-6 section-gradient">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center">
        <div class="cta-glass">
          <h2 class="gradient-text mb-4">Ready to Get Started?</h2>
          <p class="lead mb-4">Join thousands of satisfied customers who trust us with their logistics needs</p>
          <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <a href="register.php" class="btn btn-primary btn-lg px-5 py-3">
              <i class="bi bi-person-plus me-2"></i>Create Account
            </a>
            <a href="login.php" class="btn btn-outline-primary btn-lg px-5 py-3">
              <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="container-fluid py-6 section-glass">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-8 text-center">
        <h2 class="gradient-text mb-4">Get in Touch</h2>
        <p class="lead">Have questions? We're here to help</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="glass-card">
          <div class="row g-4">
            <div class="col-md-6">
              <h4 class="gradient-text mb-3">Contact Information</h4>
              <div class="contact-info">
                <div class="contact-item mb-3">
                  <i class="bi bi-geo-alt-fill me-2"></i>
                  <span>123 Logistics Street, Business District</span>
                </div>
                <div class="contact-item mb-3">
                  <i class="bi bi-telephone-fill me-2"></i>
                  <span>+1 (555) 123-4567</span>
                </div>
                <div class="contact-item mb-3">
                  <i class="bi bi-envelope-fill me-2"></i>
                  <span>info@logistics-system.com</span>
                </div>
                <div class="contact-item">
                  <i class="bi bi-clock-fill me-2"></i>
                  <span>24/7 Support Available</span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <h4 class="gradient-text mb-3">Send us a Message</h4>
              <form>
                <div class="mb-3">
                  <input type="text" class="form-control" placeholder="Your Name" required>
                </div>
                <div class="mb-3">
                  <input type="email" class="form-control" placeholder="Your Email" required>
                </div>
                <div class="mb-3">
                  <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                  <i class="bi bi-send me-2"></i>Send Message
                </button>
              </form>
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
</body>
</html> 
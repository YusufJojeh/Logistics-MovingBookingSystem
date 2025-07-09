<?php
require_once '../autoload.php';
include '../views/header.php';
?>

<!-- HERO SECTION -->
<section class="hero-section position-relative overflow-hidden" style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #f5576c 75%, #4facfe 100%);">
  <!-- Animated Background Elements -->
  <div class="floating-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="shape shape-5"></div>
  </div>
  
  <div class="container position-relative z-3">
    <div class="row align-items-center min-vh-100">
      <div class="col-lg-6 text-white">
        <div class="hero-content animate__animated animate__fadeInLeft">
          <h1 class="display-2 fw-bold mb-4 text-gradient">
            <span class="gradient-text">Logistics & Moving</span><br>
            <span class="text-white">Solutions</span>
          </h1>
          <p class="lead mb-4 fs-4 text-white-75">
            Transform your business with our cutting-edge logistics platform. 
            <span class="fw-bold">Book, track, and manage</span> all your moving and delivery needs in one place.
          </p>
          <div class="hero-stats mb-5">
            <div class="row g-3">
              <div class="col-4">
                <div class="stat-item text-center">
                  <div class="stat-number fw-bold fs-3" data-target="5000">0</div>
                  <div class="stat-label text-white-75">Happy Clients</div>
                </div>
              </div>
              <div class="col-4">
                <div class="stat-item text-center">
                  <div class="stat-number fw-bold fs-3" data-target="15000">0</div>
                  <div class="stat-label text-white-75">Successful Moves</div>
                </div>
              </div>
              <div class="col-4">
                <div class="stat-item text-center">
                  <div class="stat-number fw-bold fs-3" data-target="98">0</div>
                  <div class="stat-label text-white-75">% Satisfaction</div>
                </div>
              </div>
            </div>
          </div>
          <div class="hero-buttons">
            <a href="register.php" class="btn btn-warning btn-lg px-5 py-3 fw-bold me-3 mb-3 animate__animated animate__pulse animate__infinite">
              <i class="fas fa-rocket me-2"></i>Start Free Trial
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold mb-3">
              <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-visual animate__animated animate__fadeInRight">
          <div class="moving-truck-animation">
            <div class="truck-container">
              <div class="truck">
                <div class="truck-body"></div>
                <div class="truck-cabin"></div>
                <div class="truck-wheels">
                  <div class="wheel wheel-1"></div>
                  <div class="wheel wheel-2"></div>
                </div>
                <div class="truck-smoke"></div>
              </div>
              <div class="road"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<!-- SERVICES SECTION -->
<section class="services-section py-5 bg-white">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center mb-5">
        <h2 class="display-4 fw-bold mb-3 text-gradient">Our Premium Services</h2>
        <p class="lead text-muted">Comprehensive logistics solutions designed to meet all your moving and delivery needs</p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="service-card animate__animated animate__fadeInUp" data-delay="0.1s">
          <div class="service-icon">
            <i class="fas fa-truck-moving"></i>
          </div>
          <h4 class="fw-bold mb-3">Professional Moving</h4>
          <p class="text-muted mb-4">Expert moving services for homes, offices, and businesses. Safe, efficient, and stress-free relocations.</p>
          <ul class="service-features">
            <li><i class="fas fa-check text-success me-2"></i>Full-service packing</li>
            <li><i class="fas fa-check text-success me-2"></i>Furniture protection</li>
            <li><i class="fas fa-check text-success me-2"></i>Insurance coverage</li>
          </ul>
          <div class="service-price">
            <span class="price">From $99</span>
            <a href="services.php" class="btn btn-primary btn-sm">Learn More</a>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6">
        <div class="service-card animate__animated animate__fadeInUp" data-delay="0.2s">
          <div class="service-icon">
            <i class="fas fa-shipping-fast"></i>
          </div>
          <h4 class="fw-bold mb-3">Express Delivery</h4>
          <p class="text-muted mb-4">Lightning-fast delivery services for urgent shipments. Same-day and next-day delivery options available.</p>
          <ul class="service-features">
            <li><i class="fas fa-check text-success me-2"></i>Real-time tracking</li>
            <li><i class="fas fa-check text-success me-2"></i>Same-day delivery</li>
            <li><i class="fas fa-check text-success me-2"></i>Signature confirmation</li>
          </ul>
          <div class="service-price">
            <span class="price">From $19</span>
            <a href="services.php" class="btn btn-primary btn-sm">Learn More</a>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6">
        <div class="service-card animate__animated animate__fadeInUp" data-delay="0.3s">
          <div class="service-icon">
            <i class="fas fa-warehouse"></i>
          </div>
          <h4 class="fw-bold mb-3">Smart Storage</h4>
          <p class="text-muted mb-4">Secure, climate-controlled storage facilities with flexible terms and 24/7 access options.</p>
          <ul class="service-features">
            <li><i class="fas fa-check text-success me-2"></i>Climate control</li>
            <li><i class="fas fa-check text-success me-2"></i>24/7 access</li>
            <li><i class="fas fa-check text-success me-2"></i>Security monitoring</li>
          </ul>
          <div class="service-price">
            <span class="price">From $49/mo</span>
            <a href="services.php" class="btn btn-primary btn-sm">Learn More</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS SECTION -->
<section class="how-it-works py-5 bg-white">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center mb-5">
        <h2 class="display-4 fw-bold mb-3 text-gradient">How It Works</h2>
        <p class="lead text-muted">Get started in minutes with our simple 3-step process</p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="step-card animate__animated animate__fadeInLeft" data-delay="0.1s">
          <div class="step-number">1</div>
          <div class="step-icon">
            <i class="fas fa-search-location"></i>
          </div>
          <h4 class="fw-bold mb-3">Find Your Service</h4>
          <p class="text-muted">Browse our extensive network of verified providers and compare services, prices, and reviews.</p>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="step-card animate__animated animate__fadeInUp" data-delay="0.2s">
          <div class="step-number">2</div>
          <div class="step-icon">
            <i class="fas fa-calendar-check"></i>
          </div>
          <h4 class="fw-bold mb-3">Book Instantly</h4>
          <p class="text-muted">Choose your preferred date, time, and service. Get instant confirmation and secure payment processing.</p>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="step-card animate__animated animate__fadeInRight" data-delay="0.3s">
          <div class="step-number">3</div>
          <div class="step-icon">
            <i class="fas fa-smile-beam"></i>
          </div>
          <h4 class="fw-bold mb-3">Enjoy & Track</h4>
          <p class="text-muted">Monitor your service in real-time, receive updates, and rate your experience to help others.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES SECTION -->
<section class="features-section py-5 bg-white">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center mb-5">
        <h2 class="display-4 fw-bold mb-3 text-gradient">Why Choose Us?</h2>
        <p class="lead text-muted">Discover what makes us the preferred choice for logistics and moving services</p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="feature-item animate__animated animate__fadeInLeft" data-delay="0.1s">
          <div class="feature-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <div class="feature-content">
            <h5 class="fw-bold mb-2">100% Secure & Insured</h5>
            <p class="text-muted mb-0">All services include comprehensive insurance coverage and secure payment processing.</p>
          </div>
        </div>
      </div>
      
      <div class="col-lg-6">
        <div class="feature-item animate__animated animate__fadeInRight" data-delay="0.2s">
          <div class="feature-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="feature-content">
            <h5 class="fw-bold mb-2">24/7 Support</h5>
            <p class="text-muted mb-0">Round-the-clock customer support to assist you with any questions or concerns.</p>
          </div>
        </div>
      </div>
      
      <div class="col-lg-6">
        <div class="feature-item animate__animated animate__fadeInLeft" data-delay="0.3s">
          <div class="feature-icon">
            <i class="fas fa-map-marker-alt"></i>
          </div>
          <div class="feature-content">
            <h5 class="fw-bold mb-2">Real-time Tracking</h5>
            <p class="text-muted mb-0">Track your shipments and services in real-time with our advanced GPS tracking system.</p>
          </div>
        </div>
      </div>
      
      <div class="col-lg-6">
        <div class="feature-item animate__animated animate__fadeInRight" data-delay="0.4s">
          <div class="feature-icon">
            <i class="fas fa-star"></i>
          </div>
          <div class="feature-content">
            <h5 class="fw-bold mb-2">Verified Providers</h5>
            <p class="text-muted mb-0">All our service providers are thoroughly vetted and background-checked for your safety.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="testimonials-section py-5 bg-white">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8 text-center mb-5">
        <h2 class="display-4 fw-bold mb-3 text-gradient">What Our Clients Say</h2>
        <p class="lead text-muted">Real stories from satisfied customers who trust our services</p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="testimonial-card animate__animated animate__fadeInUp" data-delay="0.1s">
          <div class="testimonial-content">
            <div class="stars mb-3">
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
            </div>
            <p class="mb-4">"The moving service was absolutely fantastic! Professional team, on-time delivery, and everything arrived in perfect condition. Highly recommend!"</p>
            <div class="testimonial-author">
              <div class="author-avatar">
                <i class="fas fa-user-circle"></i>
              </div>
              <div class="author-info">
                <h6 class="fw-bold mb-0">Sarah Johnson</h6>
                <small class="text-muted">Home Owner</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="testimonial-card animate__animated animate__fadeInUp" data-delay="0.2s">
          <div class="testimonial-content">
            <div class="stars mb-3">
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
            </div>
            <p class="mb-4">"Express delivery exceeded my expectations. Same-day delivery with real-time tracking made everything so convenient and reliable."</p>
            <div class="testimonial-author">
              <div class="author-avatar">
                <i class="fas fa-user-circle"></i>
              </div>
              <div class="author-info">
                <h6 class="fw-bold mb-0">Ahmed Hassan</h6>
                <small class="text-muted">Business Owner</small>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4">
        <div class="testimonial-card animate__animated animate__fadeInUp" data-delay="0.3s">
          <div class="testimonial-content">
            <div class="stars mb-3">
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
              <i class="fas fa-star text-warning"></i>
            </div>
            <p class="mb-4">"The storage facility is top-notch with excellent security and climate control. The 24/7 access feature is incredibly convenient for my business needs."</p>
            <div class="testimonial-author">
              <div class="author-avatar">
                <i class="fas fa-user-circle"></i>
              </div>
              <div class="author-info">
                <h6 class="fw-bold mb-0">Emily Rodriguez</h6>
                <small class="text-muted">E-commerce Manager</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section py-5 bg-gradient-primary text-white text-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h2 class="display-4 fw-bold mb-4">Ready to Transform Your Logistics?</h2>
        <p class="lead mb-5">Join thousands of satisfied customers who trust us with their moving and delivery needs. Start your journey today!</p>
        <div class="cta-buttons">
          <a href="register.php" class="btn btn-warning btn-lg px-5 py-3 fw-bold me-3 mb-3 animate__animated animate__pulse animate__infinite">
            <i class="fas fa-rocket me-2"></i>Get Started Free
          </a>
          <a href="services.php" class="btn btn-outline-light btn-lg px-5 py-3 fw-bold mb-3">
            <i class="fas fa-info-circle me-2"></i>Learn More
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
/* Custom CSS for enhanced design */
:root {
  --primary: #667eea;
  --secondary: #764ba2;
  --accent: #f093fb;
  --warning: #f5576c;
  --info: #4facfe;
  --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  --gradient-accent: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

/* Hero Section */
.hero-section {
  position: relative;
  overflow: hidden;
}

.floating-shapes {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.shape {
  position: absolute;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}

.shape-1 {
  width: 80px;
  height: 80px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 120px;
  height: 120px;
  top: 60%;
  right: 10%;
  animation-delay: 2s;
}

.shape-3 {
  width: 60px;
  height: 60px;
  top: 40%;
  left: 80%;
  animation-delay: 4s;
}

.shape-4 {
  width: 100px;
  height: 100px;
  bottom: 20%;
  left: 20%;
  animation-delay: 1s;
}

.shape-5 {
  width: 90px;
  height: 90px;
  top: 80%;
  right: 30%;
  animation-delay: 3s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

.text-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.gradient-text {
  background: linear-gradient(135deg, #facc15 0%, #fbbf24 50%, #f59e0b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Moving Truck Animation */
.moving-truck-animation {
  position: relative;
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.truck-container {
  position: relative;
  width: 100%;
  height: 200px;
}

.truck {
  position: absolute;
  bottom: 50px;
  left: -100px;
  animation: drive 8s linear infinite;
}

.truck-body {
  width: 120px;
  height: 60px;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  border-radius: 10px;
  position: relative;
}

.truck-cabin {
  width: 60px;
  height: 50px;
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  border-radius: 10px 20px 10px 10px;
  position: absolute;
  top: -40px;
  right: -10px;
}

.truck-wheels {
  position: absolute;
  bottom: -15px;
  width: 100%;
}

.wheel {
  width: 30px;
  height: 30px;
  background: #333;
  border-radius: 50%;
  position: absolute;
  border: 3px solid #666;
}

.wheel-1 {
  left: 15px;
  animation: rotate 1s linear infinite;
}

.wheel-2 {
  right: 15px;
  animation: rotate 1s linear infinite;
}

.truck-smoke {
  position: absolute;
  top: -20px;
  left: 10px;
  width: 8px;
  height: 8px;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 50%;
  animation: smoke 2s ease-out infinite;
}

.road {
  position: absolute;
  bottom: 0;
  width: 100%;
  height: 4px;
  background: #333;
  border-radius: 2px;
}

@keyframes drive {
  0% { left: -100px; }
  100% { left: 100%; }
}

@keyframes rotate {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes smoke {
  0% { 
    opacity: 1;
    transform: translateY(0) scale(1);
  }
  100% { 
    opacity: 0;
    transform: translateY(-50px) scale(2);
  }
}

/* Trust Section */
.trust-section {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.trust-logo {
  padding: 20px;
  transition: transform 0.3s ease;
}

.trust-logo:hover {
  transform: translateY(-5px);
}

.trust-img {
  height: 40px;
  opacity: 0.6;
  transition: opacity 0.3s ease;
}

.trust-logo:hover .trust-img {
  opacity: 1;
}

/* Service Cards */
.service-card {
  background: white;
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  height: 100%;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.service-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.service-icon {
  width: 80px;
  height: 80px;
  background: var(--gradient-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  font-size: 2rem;
  color: white;
}

.service-features {
  list-style: none;
  padding: 0;
  margin-bottom: 20px;
}

.service-features li {
  margin-bottom: 8px;
  font-size: 0.9rem;
}

.service-price {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.price {
  font-size: 1.2rem;
  font-weight: bold;
  color: var(--primary);
}

/* Step Cards */
.step-card {
  text-align: center;
  padding: 30px 20px;
  position: relative;
}

.step-number {
  width: 60px;
  height: 60px;
  background: var(--gradient-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  margin: 0 auto 20px;
  position: relative;
  z-index: 2;
}

.step-icon {
  width: 80px;
  height: 80px;
  background: var(--gradient-secondary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: white;
  margin: 0 auto 20px;
}

/* Feature Items */
.feature-item {
  display: flex;
  align-items: center;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 15px;
  margin-bottom: 20px;
  transition: transform 0.3s ease;
  border: 1px solid #e9ecef;
}

.feature-item:hover {
  transform: translateX(10px);
  background: #fff;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.feature-icon {
  width: 60px;
  height: 60px;
  background: var(--gradient-accent);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  margin-right: 20px;
  flex-shrink: 0;
}

/* Testimonial Cards */
.testimonial-card {
  background: white;
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
  height: 100%;
}

.testimonial-card:hover {
  transform: translateY(-5px);
}

.stars {
  font-size: 1.2rem;
}

.testimonial-author {
  display: flex;
  align-items: center;
}

.author-avatar {
  width: 50px;
  height: 50px;
  background: var(--gradient-primary);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  margin-right: 15px;
}

/* CTA Section */
.cta-section {
  background: var(--gradient-primary);
  position: relative;
  overflow: hidden;
}

.cta-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.3;
}

/* Utility Classes */
.bg-gradient-light {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.bg-gradient-dark {
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
}

.bg-gradient-primary {
  background: var(--gradient-primary);
}

.letter-spacing-2 {
  letter-spacing: 2px;
}

.z-3 {
  z-index: 3;
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-section {
    min-height: 80vh;
  }
  
  .display-2 {
    font-size: 2.5rem;
  }
  
  .hero-buttons .btn {
    display: block;
    width: 100%;
    margin-bottom: 15px;
  }
  
  .moving-truck-animation {
    height: 200px;
  }
  
  .truck {
    transform: scale(0.7);
  }
}

/* Animation Delays */
[data-delay="0.1s"] { animation-delay: 0.1s; }
[data-delay="0.2s"] { animation-delay: 0.2s; }
[data-delay="0.3s"] { animation-delay: 0.3s; }
[data-delay="0.4s"] { animation-delay: 0.4s; }
[data-delay="0.5s"] { animation-delay: 0.5s; }
[data-delay="0.6s"] { animation-delay: 0.6s; }
</style>

<script>
// Animated counters
function animateCounter(element, target, duration) {
  let start = 0;
  const increment = target / (duration / 16);
  
  function updateCounter() {
    start += increment;
    if (start < target) {
      element.textContent = Math.floor(start);
      requestAnimationFrame(updateCounter);
    } else {
      element.textContent = target;
    }
  }
  
  updateCounter();
}

// Intersection Observer for animations
const observerOptions = {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('animate__animated');
      entry.target.classList.add(entry.target.dataset.animation || 'animate__fadeInUp');
    }
  });
}, observerOptions);

// Observe all animated elements
document.addEventListener('DOMContentLoaded', function() {
  // Animate counters when they come into view
  const statNumbers = document.querySelectorAll('.stat-number');
  statNumbers.forEach(stat => {
    const target = parseInt(stat.dataset.target);
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(stat, target, 2000);
          observer.unobserve(entry.target);
        }
      });
    });
    observer.observe(stat);
  });
  
  // Observe all elements with data-delay
  const animatedElements = document.querySelectorAll('[data-delay]');
  animatedElements.forEach(el => {
    observer.observe(el);
  });
  
  // Add smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
});

// Parallax effect for floating shapes
window.addEventListener('scroll', () => {
  const scrolled = window.pageYOffset;
  const shapes = document.querySelectorAll('.shape');
  
  shapes.forEach((shape, index) => {
    const speed = 0.5 + (index * 0.1);
    shape.style.transform = `translateY(${scrolled * speed}px)`;
  });
});
</script>

<?php
include '../views/footer.php';
?> 
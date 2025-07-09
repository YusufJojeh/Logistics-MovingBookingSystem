<div class="container animate__animated animate__fadeInDown py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="card animate__animated animate__fadeIn">
        <div class="card-header bg-primary text-white text-center">
          <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login</h4>
        </div>
        <div class="card-body">
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger animate__animated animate__shakeX"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <form id="loginForm" method="post" action="login.php" novalidate autocomplete="off">
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
              <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
              <div class="invalid-feedback">Please enter a valid email.</div>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="6">
              <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
              <div class="invalid-feedback">Password is required.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mt-2 animate__animated animate__pulse animate__infinite" id="loginBtn">
              <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
          </form>
          <p class="mb-1 mt-3 text-center">
            <a href="register.php">Don't have an account? Register</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function() {
  $('#loginForm').on('submit', function(e) {
    var form = this;
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    $(form).addClass('was-validated');
  });
});
</script>
<div class="text-center mt-2 text-muted small">&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System</div> 
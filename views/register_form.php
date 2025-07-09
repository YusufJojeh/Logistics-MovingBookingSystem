<div class="container animate__animated animate__fadeInDown py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="card animate__animated animate__fadeIn">
        <div class="card-header bg-primary text-white text-center">
          <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Register</h4>
        </div>
        <div class="card-body">
          <form id="registerForm" method="post" action="register.php" novalidate autocomplete="off">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
              <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
              <div class="invalid-feedback">Please enter your name.</div>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
              <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
              <div class="invalid-feedback">Please enter a valid email.</div>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="6">
              <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
              <div class="invalid-feedback">Password must be at least 6 characters.</div>
            </div>
            <div class="form-floating mb-3">
              <select class="form-select" id="type" name="type" required>
                <option value="" selected disabled>Select Role</option>
                <option value="customer">Customer</option>
                <option value="provider">Provider</option>
              </select>
              <label for="type"><i class="fas fa-users me-2"></i>Role</label>
              <div class="invalid-feedback">Please select a role.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mt-2 animate__animated animate__pulse animate__infinite" id="registerBtn">
              <i class="fas fa-user-plus me-2"></i>Register
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function() {
  $('#registerForm').on('submit', function(e) {
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
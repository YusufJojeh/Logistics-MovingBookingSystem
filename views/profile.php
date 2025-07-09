<?php
// ... existing code ...
?>
<div class="container py-5 animate__animated animate__fadeInDown">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="card animate__animated animate__fadeIn">
        <div class="card-header bg-primary text-white text-center">
          <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>Profile</h4>
        </div>
        <div class="card-body">
          <div class="text-center mb-4">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user->name ?? 'User'); ?>&background=4f8cff&color=fff&rounded=true&size=72" class="rounded-circle shadow animate__animated animate__fadeIn" alt="Avatar">
          </div>
          <form id="profileForm" method="post" action="profile.php" novalidate autocomplete="off">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user->name); ?>" placeholder="Full Name" required>
              <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
              <div class="invalid-feedback">Please enter your name.</div>
            </div>
            <div class="form-floating mb-3">
              <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" placeholder="Email" required>
              <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
              <div class="invalid-feedback">Please enter a valid email.</div>
            </div>
            <div class="form-floating mb-3">
              <input type="password" class="form-control" id="password" name="password" placeholder="New Password (leave blank to keep current)">
              <label for="password"><i class="fas fa-lock me-2"></i>New Password</label>
              <div class="invalid-feedback">Password must be at least 6 characters.</div>
            </div>
            <div class="d-flex gap-2 mt-3">
              <button type="submit" class="btn btn-success flex-fill animate__animated animate__pulse animate__infinite"><i class="fas fa-save me-2"></i>Save</button>
              <a href="dashboard.php" class="btn btn-secondary flex-fill"><i class="fas fa-times me-2"></i>Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function() {
  $('#profileForm').on('submit', function(e) {
    var form = this;
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    $(form).addClass('was-validated');
  });
});
</script>
// ... existing code ... 
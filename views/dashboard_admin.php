<?php
$userObj = new User();
$serviceObj = new Service();
$bookingObj = new Booking();
$reviewObj = new Review();
$db = new Database();
$users = $db->pdo->query('SELECT * FROM users')->fetchAll();
$services = $serviceObj->all();
$bookings = $bookingObj->all();
$reviews = $reviewObj->all();
?>
<div class="container-fluid animate__animated animate__fadeInUp px-2 px-md-0 d-flex flex-column align-items-center justify-content-center" style="min-height: 100vh; background: var(--background);">
  <div class="row mb-4 w-100 justify-content-center">
    <div class="col-12 col-lg-10">
      <!-- Welcome toast will be shown on login -->
    </div>
  </div>
  <div class="row g-4 mb-4 w-100 justify-content-center">
    <div class="col-12 col-md-4">
      <div class="card h-100 text-center">
        <div class="card-header d-flex align-items-center gap-2 justify-content-center">
          <i class="fas fa-users me-2"></i>
          <h5 class="card-title mb-0 fw-bold">All Users</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive animate__animated animate__fadeIn">
            <table class="table table-hover table-striped align-middle rounded overflow-hidden">
              <thead class="table-light">
                <tr>
                  <th><i class="fas fa-user"></i> Name</th>
                  <th><i class="fas fa-user-tag"></i> Type</th>
                  <th><i class="fas fa-envelope"></i> Email</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><span class="badge bg-<?php echo $u['type'] === 'admin' ? 'danger' : ($u['type'] === 'provider' ? 'info' : 'success'); ?> animate__animated animate__pulse animate__infinite"><?php echo ucfirst($u['type']); ?></span></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><a href="admin_delete.php?type=user&id=<?php echo $u['id']; ?>" class="btn btn-xs btn-danger shadow-sm" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card h-100 text-center">
        <div class="card-header d-flex align-items-center gap-2 justify-content-center">
          <i class="fas fa-box me-2"></i>
          <h5 class="card-title mb-0 fw-bold">All Services</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive animate__animated animate__fadeIn">
            <table class="table table-hover table-striped align-middle rounded overflow-hidden">
              <thead class="table-light">
                <tr>
                  <th><i class="fas fa-box"></i> Title</th>
                  <th><i class="fas fa-dollar-sign"></i> Price</th>
                  <th><i class="fas fa-calendar-alt"></i> Available</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($services as $s): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($s['title']); ?></td>
                    <td>$<?php echo htmlspecialchars($s['price']); ?></td>
                    <td><?php echo htmlspecialchars($s['available_from']); ?> to <?php echo htmlspecialchars($s['available_to']); ?></td>
                    <td><a href="admin_delete.php?type=service&id=<?php echo $s['id']; ?>" class="btn btn-xs btn-danger shadow-sm" onclick="return confirm('Delete this service?')"><i class="fas fa-trash"></i></a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="card h-100 text-center">
        <div class="card-header d-flex align-items-center gap-2 justify-content-center">
          <i class="fas fa-calendar-check me-2"></i>
          <h5 class="card-title mb-0 fw-bold">All Bookings</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive animate__animated animate__fadeIn">
            <table class="table table-hover table-striped align-middle rounded overflow-hidden">
              <thead class="table-light">
                <tr>
                  <th><i class="fas fa-calendar-day"></i> Date</th>
                  <th><i class="fas fa-map-marker-alt"></i> From</th>
                  <th><i class="fas fa-map-pin"></i> To</th>
                  <th><i class="fas fa-info-circle"></i> Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bookings as $b): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
                    <td><?php echo htmlspecialchars($b['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($b['dropoff_location']); ?></td>
                    <td><span class="badge bg-<?php
                      switch ($b['status']) {
                        case 'confirmed': echo 'success'; break;
                        case 'completed': echo 'primary'; break;
                        case 'cancelled': echo 'danger'; break;
                        default: echo 'secondary';
                      }
                    ?> animate__animated animate__pulse animate__infinite"><?php echo ucfirst($b['status']); ?></span></td>
                    <td><a href="admin_delete.php?type=booking&id=<?php echo $b['id']; ?>" class="btn btn-xs btn-danger shadow-sm" onclick="return confirm('Delete this booking?')"><i class="fas fa-trash"></i></a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Reviews (CRUD) -->
  <div class="row mb-4 g-4 w-100 justify-content-center">
    <div class="col-12 col-lg-10">
      <div class="card card-success card-outline h-100 text-center">
        <div class="card-header d-flex align-items-center gap-2 justify-content-center" style="background: var(--success); color: #fff;">
          <i class="fas fa-star me-2"></i>
          <h5 class="card-title mb-0 fw-bold">All Reviews</h5>
        </div>
        <div class="card-body">
          <?php if (empty($reviews)): ?>
            <div class="alert alert-info animate__animated animate__fadeIn">No reviews yet.</div>
          <?php else: ?>
            <div class="table-responsive animate__animated animate__fadeIn">
              <table class="table table-hover table-striped align-middle rounded overflow-hidden">
                <thead class="table-light">
                  <tr>
                    <th>Service</th>
                    <th>Customer</th>
                    <th>Provider</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($reviews as $r): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($r['service_title'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($r['customer_name'] ?? $r['customer_id']); ?></td>
                      <td><?php echo htmlspecialchars($r['provider_name'] ?? $r['provider_id']); ?></td>
                      <td><span class="text-warning"><?php echo str_repeat('★', (int)$r['rating']); ?></span></td>
                      <td><?php echo htmlspecialchars($r['comment']); ?></td>
                      <td>
                        <button class="btn btn-sm btn-outline-primary me-1 edit-review-btn" data-review-id="<?php echo $r['id']; ?>" data-rating="<?php echo $r['rating']; ?>" data-comment="<?php echo htmlspecialchars($r['comment']); ?>"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-review-btn" data-review-id="<?php echo $r['id']; ?>"><i class="fas fa-trash"></i></button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Review Modal (Edit) -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="reviewForm" method="post" action="edit_review.php" autocomplete="off">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="reviewModalLabel"><i class="fas fa-star me-2"></i>Edit Review</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="review_id" id="modalReviewId">
          <div class="mb-3">
            <label for="rating" class="form-label"><i class="fas fa-star text-warning me-2"></i>Rating</label>
            <select class="form-select" id="modalRating" name="rating" required>
              <option value="">Select rating</option>
              <option value="5">★★★★★</option>
              <option value="4">★★★★</option>
              <option value="3">★★★</option>
              <option value="2">★★</option>
              <option value="1">★</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="modalComment" class="form-label"><i class="fas fa-comment me-2"></i>Comment</label>
            <textarea class="form-control" id="modalComment" name="comment" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Review</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
$(function() {
  // Edit Review
  $('.edit-review-btn').on('click', function() {
    $('#reviewModalLabel').text('Edit Review');
    $('#modalReviewId').val($(this).data('review-id'));
    $('#modalRating').val($(this).data('rating'));
    $('#modalComment').val($(this).data('comment'));
    $('#reviewModal').modal('show');
  });
  // Delete Review
  $('.delete-review-btn').on('click', function() {
    if (confirm('Are you sure you want to delete this review?')) {
      window.location.href = 'admin_delete.php?type=review&id=' + $(this).data('review-id');
    }
  });
  // Show welcome toast on login
  if (sessionStorage.getItem('showWelcomeToast')) {
    var toastHtml = `<div class=\"toast align-items-center text-bg-light border-0 show\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\" style=\"position: fixed; top: 1.5rem; right: 1.5rem; z-index: 5000; min-width: 320px;\">\n      <div class=\"d-flex\">\n        <div class=\"toast-body text-dark fw-bold\">\n          👋 Welcome, <?php echo htmlspecialchars($user->name); ?>!\n        </div>\n        <button type=\"button\" class=\"btn-close me-2 m-auto\" data-bs-dismiss=\"toast\" aria-label=\"Close\"></button>\n      </div>\n    </div>`;
    $('body').append(toastHtml);
    setTimeout(function() { $('.toast').fadeOut(400, function() { $(this).remove(); }); }, 3500);
    sessionStorage.removeItem('showWelcomeToast');
  }
});
</script> 
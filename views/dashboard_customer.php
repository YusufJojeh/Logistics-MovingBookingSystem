<?php
$bookingObj = new Booking();
$reviewObj = new Review();
$bookings = $bookingObj->byCustomer($user->id);
$reviews = $reviewObj->byCustomer($user->id);
// Map reviews by booking id for quick lookup
$reviewedBookingIds = array_column($reviews, 'booking_id');
?>
<div class="container-fluid animate__animated animate__fadeInUp px-2 px-md-0 d-flex flex-column align-items-center justify-content-center" style="min-height: 100vh; background: var(--background);">
  <div class="row mb-4 w-100 justify-content-center">
    <div class="col-12 col-lg-10">
      <!-- Welcome toast will be shown on login -->
    </div>
  </div>
  <div class="row g-4 mb-4 w-100 justify-content-center">
    <div class="col-12 col-lg-5">
      <section class="h-100 text-center">
        <div class="d-flex align-items-center gap-2 justify-content-center mb-2" style="background: var(--primary); color: #fff; padding: 0.75rem 1rem;">
          <i class="fas fa-calendar-check me-2"></i>
          <h5 class="mb-0 fw-bold">Your Bookings</h5>
        </div>
        <div>
          <?php if (empty($bookings)): ?>
            <div class="alert alert-info animate__animated animate__fadeIn">No bookings yet.</div>
          <?php else: ?>
            <div class="table-responsive animate__animated animate__fadeIn">
              <table class="table table-hover table-striped align-middle rounded overflow-hidden text-center">
                <thead class="table-light">
                  <tr>
                    <th><i class="fas fa-calendar-day"></i> Date</th>
                    <th><i class="fas fa-map-marker-alt"></i> From</th>
                    <th><i class="fas fa-map-pin"></i> To</th>
                    <th><i class="fas fa-info-circle"></i> Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($bookings as $b): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
                      <td><?php echo htmlspecialchars($b['pickup_location']); ?></td>
                      <td><?php echo htmlspecialchars($b['dropoff_location']); ?></td>
                      <td>
                        <span class="badge bg-<?php
                          switch ($b['status']) {
                            case 'confirmed': echo 'success'; break;
                            case 'completed': echo 'primary'; break;
                            case 'cancelled': echo 'danger'; break;
                            default: echo 'secondary';
                          }
                        ?> animate__animated animate__pulse animate__infinite">
                          <?php echo ucfirst($b['status']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
          <a href="services.php" class="btn btn-outline-primary btn-lg w-100 mt-3 shadow-sm animate__animated animate__pulse animate__infinite" style="background: #e3f2fd; color: #1976d2; border-color: #1976d2;"><i class="fas fa-plus"></i> Book a New Service</a>
        </div>
      </section>
    </div>
    <div class="col-12 col-lg-5">
      <section class="h-100 text-center">
        <div class="d-flex align-items-center gap-2 justify-content-center mb-2" style="background: var(--success); color: #fff; padding: 0.75rem 1rem;">
          <i class="fas fa-star me-2"></i>
          <h5 class="mb-0 fw-bold">Your Reviews</h5>
        </div>
        <div>
          <?php if (empty($reviews)): ?>
            <div class="alert alert-info animate__animated animate__fadeIn">No reviews yet.</div>
          <?php else: ?>
            <ul class="list-group animate__animated animate__fadeIn">
              <?php foreach ($reviews as $r): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center gap-2 mb-2 text-center" style="background: transparent; border-radius: 0;">
                  <span>
                    <strong>Rating:</strong> <span class="text-warning"><?php echo str_repeat('★', (int)$r['rating']); ?></span>
                    <br><strong>Comment:</strong> <?php echo htmlspecialchars($r['comment']); ?>
                  </span>
                  <span>
                    <a class="btn btn-sm btn-outline-primary me-1" href="edit_review.php?review_id=<?php echo $r['id']; ?>&booking_id=<?php echo $r['booking_id']; ?>"><i class="fas fa-edit"></i></a>
                    <button class="btn btn-sm btn-outline-danger delete-review-btn" data-review-id="<?php echo $r['id']; ?>"><i class="fas fa-trash"></i></button>
                  </span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
  <!-- My Booked Services (CRUD for reviews) -->
  <div class="row mb-4 g-4 w-100 justify-content-center">
    <div class="col-12 col-lg-10">
      <section class="h-100 text-center">
        <div class="d-flex align-items-center gap-2 justify-content-center mb-2" style="background: var(--primary); color: #fff; padding: 0.75rem 1rem;">
          <i class="fas fa-clipboard-list me-2"></i>
          <h5 class="mb-0 fw-bold">My Booked Services (Review Management)</h5>
        </div>
        <div>
          <div class="table-responsive animate__animated animate__fadeIn">
            <table class="table table-hover table-striped align-middle rounded overflow-hidden text-center">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Status</th>
                  <th>Review</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bookings as $b): ?>
                  <?php if ($b['status'] === 'completed'): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($b['booking_date']); ?></td>
                    <td><?php echo htmlspecialchars($b['pickup_location']); ?></td>
                    <td><?php echo htmlspecialchars($b['dropoff_location']); ?></td>
                    <td><span class="badge bg-primary">Completed</span></td>
                    <td>
                      <?php if (in_array($b['id'], $reviewedBookingIds)): ?>
                        <span class="badge bg-success"><i class="fas fa-check"></i> Reviewed</span>
                      <?php else: ?>
                        <button class="btn btn-sm btn-outline-success add-review-btn" data-booking-id="<?php echo $b['id']; ?>" data-bs-toggle="modal" data-bs-target="#reviewModal"><i class="fas fa-plus"></i> Add Review</button>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<!-- Review Modal (Add/Edit) -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="reviewForm" method="post" action="add_review.php" autocomplete="off">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="reviewModalLabel"><i class="fas fa-star me-2"></i>Add/Edit Review</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="booking_id" id="modalBookingId">
          <input type="hidden" name="review_id" id="modalReviewId">
          <input type="hidden" name="provider_id" id="modalProviderId">
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
  // Add Review
  $('.add-review-btn').on('click', function() {
    $('#reviewModalLabel').text('Add Review');
    $('#reviewForm').attr('action', 'add_review.php');
    $('#modalBookingId').val($(this).data('booking-id'));
    $('#modalReviewId').val('');
    $('#modalRating').val('');
    $('#modalComment').val('');
    $('#modalProviderId').val($(this).data('provider-id') || '');
  });
  // Edit Review
  $('.edit-review-btn').on('click', function() {
    $('#reviewModalLabel').text('Edit Review');
    $('#reviewForm').attr('action', 'edit_review.php');
    $('#modalBookingId').val($(this).data('booking-id'));
    $('#modalReviewId').val($(this).data('review-id'));
    $('#modalRating').val($(this).data('rating'));
    $('#modalComment').val($(this).data('comment'));
    $('#modalProviderId').val($(this).data('provider-id') || '');
    $('#reviewModal').modal('show');
  });
  // Delete Review
  $('.delete-review-btn').on('click', function() {
    if (confirm('Are you sure you want to delete this review?')) {
      window.location.href = 'delete_review.php?id=' + $(this).data('review-id');
    }
  });

  // AJAX form submit for add/edit review
  $('#reviewForm').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var action = $form.attr('action');
    var formData = $form.serialize();
    $.post(action, formData)
      .done(function(response) {
        $('#reviewModal').modal('hide');
        showToast('Review saved successfully!', 'success');
        setTimeout(function() { location.reload(); }, 800);
      })
      .fail(function(xhr) {
        showToast('Failed to save review: ' + (xhr.responseText || 'Unknown error'), 'danger');
      });
  });

  // Bootstrap toast for feedback
  function showToast(message, type) {
    var toastHtml = `<div class="toast align-items-center text-bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 1rem; right: 1rem; z-index: 9999;">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>`;
    var $toast = $(toastHtml);
    $('body').append($toast);
    setTimeout(function() { $toast.fadeOut(400, function() { $(this).remove(); }); }, 2500);
  }

  // Show welcome toast on login
  if (sessionStorage.getItem('showWelcomeToast')) {
    var toastHtml = `<div class="toast align-items-center text-bg-light border-0 show" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 5000; min-width: 320px;">
      <div class="d-flex">
        <div class="toast-body text-dark fw-bold">
          👋 Welcome, <?php echo htmlspecialchars($user->name); ?>!
        </div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>`;
    $('body').append(toastHtml);
    setTimeout(function() { $('.toast').fadeOut(400, function() { $(this).remove(); }); }, 3500);
    sessionStorage.removeItem('showWelcomeToast');
  }
});
</script> 
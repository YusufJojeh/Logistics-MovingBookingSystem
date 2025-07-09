<?php
require_once '../autoload.php';

// Check if user is logged in and is a provider
if (!isset($_SESSION['user_id']) ) {
    header('Location: login.php');
    exit();
}

$serviceObj = new Service();
$bookingObj = new Booking();
$userObj = new User();

// Get all services by this provider
$services = $serviceObj->byProvider($user->id);

// Get all bookings for provider's services with customer details
$bookings = [];
foreach ($services as $service) {
    $serviceBookings = $bookingObj->byService($service['id']);
    foreach ($serviceBookings as $booking) {
        // Get customer details
        $customer = $userObj->findById($booking['customer_id']);
        $booking['customer_name'] = $customer ? $customer->name : 'Unknown Customer';
        $booking['customer_email'] = $customer ? $customer->email : '';
        $booking['customer_phone'] = $customer ? $customer->phone : '';
        $booking['service_title'] = $service['title'];
        $booking['service_price'] = $service['price'];
        $bookings[] = $booking;
    }
}

// Sort bookings by date (newest first)
usort($bookings, function($a, $b) {
    return strtotime($b['booking_date']) - strtotime($a['booking_date']);
});

include '../views/header.php';
?>

<div class="container-fluid animate__animated animate__fadeInUp px-2 px-md-0" style="min-height: 100vh; background: var(--background);">
  <div class="row mb-4 w-100 justify-content-center">
    <div class="col-12 col-lg-11">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-gradient">
          <i class="fas fa-calendar-check me-2"></i>Manage Booked Services
        </h2>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-primary" onclick="refreshBookings()">
            <i class="fas fa-sync-alt me-1"></i>Refresh
          </button>
          <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
          </a>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card bg-primary text-white">
            <div class="card-body text-center">
              <i class="fas fa-calendar-day fa-2x mb-2"></i>
              <h4 class="mb-0"><?php echo count($bookings); ?></h4>
              <small>Total Bookings</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-warning text-dark">
            <div class="card-body text-center">
              <i class="fas fa-clock fa-2x mb-2"></i>
              <h4 class="mb-0"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'pending')); ?></h4>
              <small>Pending</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-success text-white">
            <div class="card-body text-center">
              <i class="fas fa-check-circle fa-2x mb-2"></i>
              <h4 class="mb-0"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')); ?></h4>
              <small>Confirmed</small>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-info text-white">
            <div class="card-body text-center">
              <i class="fas fa-flag-checkered fa-2x mb-2"></i>
              <h4 class="mb-0"><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'completed')); ?></h4>
              <small>Completed</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Bookings Table -->
      <div class="card">
        <div class="card-header bg-gradient-primary text-white">
          <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>All Bookings
          </h5>
        </div>
        <div class="card-body">
          <?php if (empty($bookings)): ?>
            <div class="text-center py-5">
              <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
              <h5 class="text-muted">No bookings found</h5>
              <p class="text-muted">When customers book your services, they will appear here.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover table-striped align-middle">
                <thead class="table-light">
                  <tr>
                    <th><i class="fas fa-calendar-day me-1"></i>Booking Date</th>
                    <th><i class="fas fa-user me-1"></i>Customer</th>
                    <th><i class="fas fa-box me-1"></i>Service</th>
                    <th><i class="fas fa-map-marker-alt me-1"></i>Pickup</th>
                    <th><i class="fas fa-map-marker me-1"></i>Dropoff</th>
                    <th><i class="fas fa-dollar-sign me-1"></i>Price</th>
                    <th><i class="fas fa-info-circle me-1"></i>Status</th>
                    <th><i class="fas fa-cogs me-1"></i>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($bookings as $booking): ?>
                    <tr class="booking-row" data-booking-id="<?php echo $booking['id']; ?>">
                      <td>
                        <strong><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></strong><br>
                        <small class="text-muted"><?php echo date('h:i A', strtotime($booking['booking_date'])); ?></small>
                      </td>
                      <td>
                        <div class="d-flex flex-column">
                          <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                          <small class="text-muted">
                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($booking['customer_email']); ?>
                          </small>
                          <?php if ($booking['customer_phone']): ?>
                            <small class="text-muted">
                              <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($booking['customer_phone']); ?>
                            </small>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td>
                        <strong><?php echo htmlspecialchars($booking['service_title']); ?></strong>
                      </td>
                      <td>
                        <small><?php echo htmlspecialchars($booking['pickup_location']); ?></small>
                      </td>
                      <td>
                        <small><?php echo htmlspecialchars($booking['dropoff_location']); ?></small>
                      </td>
                      <td>
                        <span class="badge bg-success fs-6">$<?php echo htmlspecialchars($booking['service_price']); ?></span>
                      </td>
                      <td>
                        <span class="badge bg-<?php
                          switch ($booking['status']) {
                            case 'pending': echo 'warning'; break;
                            case 'confirmed': echo 'success'; break;
                            case 'completed': echo 'info'; break;
                            case 'cancelled': echo 'danger'; break;
                            default: echo 'secondary';
                          }
                        ?> fs-6">
                          <i class="fas fa-<?php
                            switch ($booking['status']) {
                              case 'pending': echo 'clock'; break;
                              case 'confirmed': echo 'check'; break;
                              case 'completed': echo 'flag-checkered'; break;
                              case 'cancelled': echo 'times'; break;
                              default: echo 'question';
                            }
                          ?> me-1"></i>
                          <?php echo ucfirst($booking['status']); ?>
                        </span>
                      </td>
                      <td>
                        <div class="btn-group" role="group">
                          <?php if ($booking['status'] === 'pending'): ?>
                            <button class="btn btn-sm btn-success confirm-booking" data-booking-id="<?php echo $booking['id']; ?>" title="Confirm Booking">
                              <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger cancel-booking" data-booking-id="<?php echo $booking['id']; ?>" title="Cancel Booking">
                              <i class="fas fa-times"></i>
                            </button>
                          <?php elseif ($booking['status'] === 'confirmed'): ?>
                            <button class="btn btn-sm btn-info complete-booking" data-booking-id="<?php echo $booking['id']; ?>" title="Mark as Completed">
                              <i class="fas fa-flag-checkered"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="rescheduleBooking(<?php echo $booking['id']; ?>)" title="Reschedule">
                              <i class="fas fa-calendar-alt"></i>
                            </button>
                          <?php endif; ?>
                          <button class="btn btn-sm btn-outline-primary view-details" data-booking-id="<?php echo $booking['id']; ?>" title="View Details">
                            <i class="fas fa-eye"></i>
                          </button>
                        </div>
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

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="bookingDetailsModalLabel">
          <i class="fas fa-info-circle me-2"></i>Booking Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="bookingDetailsContent">
        <!-- Content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="rescheduleForm">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="rescheduleModalLabel">
            <i class="fas fa-calendar-alt me-2"></i>Reschedule Booking
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="rescheduleBookingId" name="booking_id">
          <div class="mb-3">
            <label for="newBookingDate" class="form-label">
              <i class="fas fa-calendar me-1"></i>New Booking Date & Time
            </label>
            <input type="datetime-local" class="form-control" id="newBookingDate" name="new_booking_date" required>
          </div>
          <div class="mb-3">
            <label for="rescheduleReason" class="form-label">
              <i class="fas fa-comment me-1"></i>Reason for Reschedule (Optional)
            </label>
            <textarea class="form-control" id="rescheduleReason" name="reason" rows="3" placeholder="Please provide a reason for rescheduling..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-warning">
            <i class="fas fa-save me-1"></i>Reschedule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Confirm Booking
    $('.confirm-booking').on('click', function() {
        const bookingId = $(this).data('booking-id');
        if (confirm('Are you sure you want to confirm this booking?')) {
            updateBookingStatus(bookingId, 'confirmed');
        }
    });

    // Complete Booking
    $('.complete-booking').on('click', function() {
        const bookingId = $(this).data('booking-id');
        if (confirm('Are you sure you want to mark this booking as completed?')) {
            updateBookingStatus(bookingId, 'completed');
        }
    });

    // Cancel Booking
    $('.cancel-booking').on('click', function() {
        const bookingId = $(this).data('booking-id');
        if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
            updateBookingStatus(bookingId, 'cancelled');
        }
    });

    // View Booking Details
    $('.view-details').on('click', function() {
        const bookingId = $(this).data('booking-id');
        loadBookingDetails(bookingId);
    });

    // Reschedule Form Submit
    $('#rescheduleForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        rescheduleBooking(formData);
    });
});

function updateBookingStatus(bookingId, status) {
    $.ajax({
        url: 'update_booking_status.php',
        type: 'POST',
        data: {
            booking_id: bookingId,
            status: status
        },
        success: function(response) {
            if (response.success) {
                showToast('Booking status updated successfully!', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Error updating booking status: ' + response.message, 'error');
            }
        },
        error: function() {
            showToast('Error updating booking status. Please try again.', 'error');
        }
    });
}

function loadBookingDetails(bookingId) {
    $.ajax({
        url: 'get_booking_details.php',
        type: 'GET',
        data: { booking_id: bookingId },
        success: function(response) {
            if (response.success) {
                $('#bookingDetailsContent').html(response.html);
                $('#bookingDetailsModal').modal('show');
            } else {
                showToast('Error loading booking details: ' + response.message, 'error');
            }
        },
        error: function() {
            showToast('Error loading booking details. Please try again.', 'error');
        }
    });
}

function rescheduleBooking(bookingId) {
    $('#rescheduleBookingId').val(bookingId);
    $('#rescheduleModal').modal('show');
}

function rescheduleBooking(formData) {
    $.ajax({
        url: 'reschedule_booking.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('Booking rescheduled successfully!', 'success');
                $('#rescheduleModal').modal('hide');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('Error rescheduling booking: ' + response.message, 'error');
            }
        },
        error: function() {
            showToast('Error rescheduling booking. Please try again.', 'error');
        }
    });
}

function refreshBookings() {
    location.reload();
}

function contactCustomer(email, phone) {
    const contactInfo = `Email: ${email}\nPhone: ${phone}`;
    const message = `Customer Contact Information:\n\n${contactInfo}\n\nYou can contact the customer using the information above.`;
    
    if (confirm(message + '\n\nWould you like to copy the email address to clipboard?')) {
        navigator.clipboard.writeText(email).then(() => {
            showToast('Email address copied to clipboard!', 'success');
        }).catch(() => {
            showToast('Could not copy to clipboard. Email: ' + email, 'info');
        });
    }
}

function showToast(message, type) {
    const toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const toastHtml = `
        <div class="toast align-items-center text-white ${toastClass} border-0 show" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 5000; min-width: 320px;">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    $('body').append(toastHtml);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        $('.toast').remove();
    }, 3000);
}
</script>

<?php include '../views/footer.php'; ?> 
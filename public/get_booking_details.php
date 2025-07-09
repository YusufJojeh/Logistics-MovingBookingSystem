<?php
require_once '../autoload.php';

// Check if user is logged in and is a provider
if (!isset($_SESSION['user_id']) || $user->type !== 'provider') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get booking ID
$booking_id = $_GET['booking_id'] ?? null;

// Validate input
if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Missing booking ID']);
    exit();
}

try {
    $bookingObj = new Booking();
    $serviceObj = new Service();
    $userObj = new User();
    
    // Get the booking
    $booking = $bookingObj->findById($booking_id);
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit();
    }
    
    // Check if the booking belongs to a service owned by this provider
    $service = $serviceObj->findById($booking->service_id);
    if (!$service || $service['provider_id'] != $user->id) {
        echo json_encode(['success' => false, 'message' => 'You can only view bookings for your own services']);
        exit();
    }
    
    // Get customer details
    $customer = $userObj->findById($booking->customer_id);
    
    // Generate HTML for the modal
    $html = '
    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">
                <i class="fas fa-user me-2"></i>Customer Information
            </h6>
            <div class="card bg-light">
                <div class="card-body">
                    <p><strong>Name:</strong> ' . htmlspecialchars($customer->name) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($customer->email) . '</p>
                    <p><strong>Phone:</strong> ' . htmlspecialchars($customer->phone) . '</p>
                    <p><strong>Member Since:</strong> ' . date('M d, Y', strtotime($customer->created_at)) . '</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <h6 class="fw-bold text-primary mb-3">
                <i class="fas fa-box me-2"></i>Service Information
            </h6>
            <div class="card bg-light">
                <div class="card-body">
                    <p><strong>Service:</strong> ' . htmlspecialchars($service['title']) . '</p>
                    <p><strong>Price:</strong> $' . htmlspecialchars($service['price']) . '</p>
                    <p><strong>Description:</strong> ' . htmlspecialchars($service['description']) . '</p>
                    <p><strong>Available:</strong> ' . date('M d, Y', strtotime($service['available_from'])) . ' to ' . date('M d, Y', strtotime($service['available_to'])) . '</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="fw-bold text-primary mb-3">
                <i class="fas fa-calendar-check me-2"></i>Booking Details
            </h6>
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Booking Date:</strong> ' . date('M d, Y h:i A', strtotime($booking->booking_date)) . '</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-' . getStatusColor($booking->status) . '">' . ucfirst($booking->status) . '</span>
                            </p>
                            <p><strong>Created:</strong> ' . date('M d, Y h:i A', strtotime($booking->created_at)) . '</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Pickup Location:</strong> ' . htmlspecialchars($booking->pickup_location) . '</p>
                            <p><strong>Dropoff Location:</strong> ' . htmlspecialchars($booking->dropoff_location) . '</p>
                        </div>
                    </div>
                    ' . ($booking->notes ? '<p><strong>Notes:</strong> ' . htmlspecialchars($booking->notes) . '</p>' : '') . '
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="fw-bold text-primary mb-3">
                <i class="fas fa-history me-2"></i>Quick Actions
            </h6>
            <div class="d-flex gap-2 flex-wrap">';
    
    // Add action buttons based on current status
    if ($booking->status === 'pending') {
        $html .= '
                <button class="btn btn-success confirm-booking" data-booking-id="' . $booking->id . '">
                    <i class="fas fa-check me-1"></i>Confirm Booking
                </button>
                <button class="btn btn-danger cancel-booking" data-booking-id="' . $booking->id . '">
                    <i class="fas fa-times me-1"></i>Cancel Booking
                </button>';
    } elseif ($booking->status === 'confirmed') {
        $html .= '
                <button class="btn btn-info complete-booking" data-booking-id="' . $booking->id . '">
                    <i class="fas fa-flag-checkered me-1"></i>Mark as Completed
                </button>
                <button class="btn btn-warning" onclick="rescheduleBooking(' . $booking->id . ')">
                    <i class="fas fa-calendar-alt me-1"></i>Reschedule
                </button>';
    }
    
    $html .= '
                <button class="btn btn-outline-primary" onclick="contactCustomer(\'' . htmlspecialchars($customer->email) . '\', \'' . htmlspecialchars($customer->phone) . '\')">
                    <i class="fas fa-envelope me-1"></i>Contact Customer
                </button>
            </div>
        </div>
    </div>';
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'booking' => [
            'id' => $booking->id,
            'status' => $booking->status,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Error getting booking details: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while loading booking details']);
}

function getStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'confirmed': return 'success';
        case 'completed': return 'info';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
?> 
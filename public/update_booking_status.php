<?php
require_once '../autoload.php';

// Check if user is logged in and is a provider
if (!isset($_SESSION['user_id']) || $user->type !== 'provider') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$booking_id = $_POST['booking_id'] ?? null;
$status = $_POST['status'] ?? null;

// Validate input
if (!$booking_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Validate status
$allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

try {
    $bookingObj = new Booking();
    $serviceObj = new Service();
    
    // Get the booking
    $booking = $bookingObj->findById($booking_id);
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit();
    }
    
    // Check if the booking belongs to a service owned by this provider
    $service = $serviceObj->findById($booking->service_id);
    if (!$service || $service['provider_id'] != $user->id) {
        echo json_encode(['success' => false, 'message' => 'You can only manage bookings for your own services']);
        exit();
    }
    
    // Update the booking status
    if ($bookingObj->updateStatus($booking_id, $status)) {
        // Log the action
        $action_log = [
            'booking_id' => $booking_id,
            'provider_id' => $user->id,
            'action' => 'status_update',
            'old_status' => $booking->status,
            'new_status' => $status,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // You could save this to a log table if needed
        // logAction($action_log);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Booking status updated successfully',
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update booking status']);
    }
    
} catch (Exception $e) {
    error_log('Error updating booking status: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating the booking status']);
}
?> 
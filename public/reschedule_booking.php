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
$new_booking_date = $_POST['new_booking_date'] ?? null;
$reason = $_POST['reason'] ?? '';

// Validate input
if (!$booking_id || !$new_booking_date) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Validate date format
$date_time = DateTime::createFromFormat('Y-m-d\TH:i', $new_booking_date);
if (!$date_time) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit();
}

// Check if new date is in the future
$now = new DateTime();
if ($date_time <= $now) {
    echo json_encode(['success' => false, 'message' => 'New booking date must be in the future']);
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
        echo json_encode(['success' => false, 'message' => 'You can only reschedule bookings for your own services']);
        exit();
    }
    
    // Check if booking status allows rescheduling
    if ($booking->status !== 'confirmed' && $booking->status !== 'pending') {
        echo json_encode(['success' => false, 'message' => 'Only confirmed or pending bookings can be rescheduled']);
        exit();
    }
    
    // Check if new date is within service availability
    $service_start = new DateTime($service['available_from']);
    $service_end = new DateTime($service['available_to']);
    if ($date_time < $service_start || $date_time > $service_end) {
        echo json_encode(['success' => false, 'message' => 'New date must be within service availability period']);
        exit();
    }
    
    // Update the booking date
    $formatted_date = $date_time->format('Y-m-d H:i:s');
    
    // You might want to add a method to update booking date in the Booking class
    // For now, we'll use a direct SQL update
    $db = new Database();
    $stmt = $db->pdo->prepare('UPDATE bookings SET booking_date = ?, notes = CONCAT(notes, ?) WHERE id = ?');
    
    $reschedule_note = "\n\n--- RESCHEDULED ---\nNew Date: " . $formatted_date;
    if ($reason) {
        $reschedule_note .= "\nReason: " . $reason;
    }
    $reschedule_note .= "\nRescheduled by: " . $user->name . " on " . date('Y-m-d H:i:s');
    
    $updated_notes = ($booking->notes ? $booking->notes : '') . $reschedule_note;
    
    if ($stmt->execute([$formatted_date, $reschedule_note, $booking_id])) {
        // Log the action
        $action_log = [
            'booking_id' => $booking_id,
            'provider_id' => $user->id,
            'action' => 'reschedule',
            'old_date' => $booking->booking_date,
            'new_date' => $formatted_date,
            'reason' => $reason,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // You could save this to a log table if needed
        // logAction($action_log);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Booking rescheduled successfully',
            'new_date' => $formatted_date
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reschedule booking']);
    }
    
} catch (Exception $e) {
    error_log('Error rescheduling booking: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while rescheduling the booking']);
}
?> 
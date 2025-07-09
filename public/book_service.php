<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'customer') {
    header('Location: login.php');
    exit;
}
$serviceObj = new Service();
$bookingObj = new Booking();
$error = '';
$success = '';
$service_id = $_GET['service_id'] ?? $_POST['service_id'] ?? null;
$service = $serviceObj->findById($service_id);
if (!$service) {
    header('Location: services.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'] ?? '';
    $pickup_location = $_POST['pickup_location'] ?? '';
    $dropoff_location = $_POST['dropoff_location'] ?? '';
    $notes = $_POST['notes'] ?? '';
    if ($bookingObj->create(Auth::user()->id, $service_id, $booking_date, $pickup_location, $dropoff_location, $notes)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Booking failed.';
    }
}
include '../views/header.php';
?>
<div class="container mt-4" style="max-width: 500px;">
  <h2>Book Service: <?php echo htmlspecialchars($service->title); ?></h2>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
    <div class="mb-3">
      <label for="booking_date" class="form-label">Booking Date</label>
      <input type="date" class="form-control" id="booking_date" name="booking_date" required>
    </div>
    <div class="mb-3">
      <label for="pickup_location" class="form-label">Pickup Location</label>
      <input type="text" class="form-control" id="pickup_location" name="pickup_location" required>
    </div>
    <div class="mb-3">
      <label for="dropoff_location" class="form-label">Dropoff Location</label>
      <input type="text" class="form-control" id="dropoff_location" name="dropoff_location" required>
    </div>
    <div class="mb-3">
      <label for="notes" class="form-label">Notes</label>
      <textarea class="form-control" id="notes" name="notes"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Book</button>
    <a href="services.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<?php
include '../views/footer.php'; 
<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'customer') {
    header('Location: login.php');
    exit;
}
$reviewObj = new Review();
$error = '';
$booking_id = $_GET['booking_id'] ?? $_POST['booking_id'] ?? null;
$provider_id = $_GET['provider_id'] ?? $_POST['provider_id'] ?? null;
if (!$booking_id || !$provider_id) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';
    if ($reviewObj->create($booking_id, Auth::user()->id, $provider_id, $rating, $comment)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Failed to add review.';
    }
}
include '../views/header.php';
?>
<div class="container mt-4" style="max-width: 500px;">
  <h2>Add Review</h2>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>">
    <input type="hidden" name="provider_id" value="<?php echo htmlspecialchars($provider_id); ?>">
    <div class="mb-3">
      <label for="rating" class="form-label">Rating (1-5)</label>
      <select class="form-select" id="rating" name="rating" required>
        <option value="">Select</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="comment" class="form-label">Comment</label>
      <textarea class="form-control" id="comment" name="comment"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit Review</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<?php
include '../views/footer.php'; 
<?php
session_start();
require_once '../autoload.php';
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}
$reviewObj = new Review();
$error = '';
$review_id = $_GET['review_id'] ?? $_POST['review_id'] ?? null;
$review = $reviewObj->findById($review_id);
if (!$review) {
    header('Location: dashboard.php');
    exit;
}
$user = Auth::user();
$role = $user->type;
$canEdit = false;
if ($role === 'customer' && $review->customer_id == $user->id) {
    $canEdit = true;
} elseif ($role === 'provider' && $review->provider_id == $user->id) {
    $canEdit = true;
} elseif ($role === 'admin') {
    $canEdit = true;
}
if (!$canEdit) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';
    if ($reviewObj->update($review_id, $rating, $comment)) {
        // Redirect to the correct dashboard
        if ($role === 'customer') {
            header('Location: dashboard.php');
        } elseif ($role === 'provider') {
            header('Location: dashboard.php');
        } elseif ($role === 'admin') {
            header('Location: dashboard.php');
        }
        exit;
    } else {
        $error = 'Failed to update review.';
    }
}
include '../views/header.php';
?>
<div class="container mt-4" style="max-width: 500px;">
  <h2>Edit Review</h2>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="review_id" value="<?php echo $review_id; ?>">
    <div class="mb-3">
      <label for="rating" class="form-label">Rating (1-5)</label>
      <select class="form-select" id="rating" name="rating" required>
        <option value="">Select</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <option value="<?php echo $i; ?>" <?php if ($review->rating == $i) echo 'selected'; ?>><?php echo $i; ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="comment" class="form-label">Comment</label>
      <textarea class="form-control" id="comment" name="comment"><?php echo htmlspecialchars($review->comment); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Update Review</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<?php
include '../views/footer.php'; 
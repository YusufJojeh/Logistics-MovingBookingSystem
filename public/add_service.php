<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'provider') {
    header('Location: login.php');
    exit;
}
$serviceObj = new Service();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $available_from = $_POST['available_from'] ?? '';
    $available_to = $_POST['available_to'] ?? '';
    if ($serviceObj->create(Auth::user()->id, $title, $description, $price, $available_from, $available_to)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Failed to add service.';
    }
}
include '../views/header.php';
?>
<div class="container mt-4" style="max-width: 500px;">
  <h2>Add New Service</h2>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control" id="description" name="description"></textarea>
    </div>
    <div class="mb-3">
      <label for="price" class="form-label">Price</label>
      <input type="number" class="form-control" id="price" name="price" required>
    </div>
    <div class="mb-3">
      <label for="available_from" class="form-label">Available From</label>
      <input type="date" class="form-control" id="available_from" name="available_from">
    </div>
    <div class="mb-3">
      <label for="available_to" class="form-label">Available To</label>
      <input type="date" class="form-control" id="available_to" name="available_to">
    </div>
    <button type="submit" class="btn btn-primary">Add Service</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<?php
include '../views/footer.php'; 
<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'provider') {
    header('Location: login.php');
    exit;
}
$serviceObj = new Service();
$error = '';
$service_id = $_GET['service_id'] ?? $_POST['service_id'] ?? null;
$service = $serviceObj->findById($service_id);
if (!$service || $service->provider_id != Auth::user()->id) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $available_from = $_POST['available_from'] ?? '';
    $available_to = $_POST['available_to'] ?? '';
    if ($serviceObj->update($service_id, $title, $description, $price, $available_from, $available_to)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Failed to update service.';
    }
}
include '../views/header.php';
?>
<div class="container mt-4" style="max-width: 500px;">
  <h2>Edit Service</h2>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($service->title); ?>" required>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Description</label>
      <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($service->description); ?></textarea>
    </div>
    <div class="mb-3">
      <label for="price" class="form-label">Price</label>
      <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($service->price); ?>" required>
    </div>
    <div class="mb-3">
      <label for="available_from" class="form-label">Available From</label>
      <input type="date" class="form-control" id="available_from" name="available_from" value="<?php echo htmlspecialchars($service->available_from); ?>">
    </div>
    <div class="mb-3">
      <label for="available_to" class="form-label">Available To</label>
      <input type="date" class="form-control" id="available_to" name="available_to" value="<?php echo htmlspecialchars($service->available_to); ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update Service</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<?php
include '../views/footer.php'; 
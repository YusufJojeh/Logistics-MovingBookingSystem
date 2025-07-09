<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'customer') {
    header('Location: login.php');
    exit;
}
$serviceObj = new Service();
$services = $serviceObj->all();
include '../views/header.php';
?>
<div class="container mt-4">
  <h2>Available Services</h2>
  <?php if (empty($services)): ?>
    <div class="alert alert-info">No services available.</div>
  <?php else: ?>
    <ul class="list-group mb-4">
      <?php foreach ($services as $s): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <strong><?php echo htmlspecialchars($s['title']); ?></strong> - $<?php echo htmlspecialchars($s['price']); ?> <br>
            <?php echo htmlspecialchars($s['description']); ?>
          </div>
          <a href="book_service.php?service_id=<?php echo $s['id']; ?>" class="btn btn-primary">Book</a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
<?php
include '../views/footer.php'; 
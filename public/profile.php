<?php
session_start();
require_once '../autoload.php';
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}
$user = Auth::user();
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $db = new Database();
    $params = [$name, $phone, $user->id];
    $sql = 'UPDATE users SET name=?, phone=?';
    if ($password) {
        $sql .= ', password=?';
        $params = [$name, $phone, password_hash($password, PASSWORD_DEFAULT), $user->id];
    }
    $sql .= ' WHERE id=?';
    $stmt = $db->pdo->prepare($sql);
    if ($stmt->execute($params)) {
        $success = 'Profile updated.';
    } else {
        $error = 'Failed to update profile.';
    }
    $user = (new User())->findById($user->id); // Refresh user info
}
include '../views/header.php';
?>
<div class="container mt-4" style="max-width: 500px;">
  <h2>My Profile</h2>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <form method="post">
    <div class="mb-3">
      <label for="name" class="form-label">Name</label>
      <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user->name); ?>" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user->email); ?>" disabled>
    </div>
    <div class="mb-3">
      <label for="phone" class="form-label">Phone</label>
      <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user->phone); ?>">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">New Password (leave blank to keep current)</label>
      <input type="password" class="form-control" id="password" name="password">
    </div>
    <button type="submit" class="btn btn-primary">Update Profile</button>
    <a href="dashboard.php" class="btn btn-secondary">Back</a>
  </form>
</div>
<?php
include '../views/footer.php'; 
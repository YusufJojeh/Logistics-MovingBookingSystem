<?php
// tracking.php - Track a booking/order by number
require_once __DIR__ . '/../config/db.php';
$tracking = trim($_GET['tracking'] ?? '');
$status = '';
$details = null;
$error = '';
if ($tracking) {
    // Try to fetch real booking info
    $stmt = mysqli_prepare($conn, "SELECT b.status, b.booking_date, s.title AS service, s.city_from, s.city_to, u.name AS provider FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.provider_id = u.id WHERE b.id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $tracking);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $status = ucfirst($row['status']);
        $details = [
            'from' => $row['city_from'],
            'to' => $row['city_to'],
            'date' => $row['booking_date'],
            'service' => $row['service'],
            'provider' => $row['provider'],
        ];
    } else {
        $error = 'Booking not found.';
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Your Booking - Logistics & Moving Booking System</title>
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="modern-bg">
<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold fs-3 gradient-text" href="/index.php">Logistics<span class="text-primary">&</span>Moving</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item"><a class="nav-link" href="/index.php#services">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="/index.php#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="/index.php#testimonials">Testimonials</a></li>
        <li class="nav-item"><a class="nav-link" href="/index.php#contact">Contact</a></li>
        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="/register.php">Get Started</a></li>
      </ul>
    </div>
  </div>
</nav>
<section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 30vh;">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="glass-card p-5 mb-4">
          <h1 class="display-4 fw-black mb-3 gradient-text">Track Your Booking</h1>
          <p class="lead mb-4 text-body-secondary">Enter your booking or tracking number to view the latest status and route.</p>
          <form class="row g-3 justify-content-center" method="get" action="">
            <div class="col-md-8">
              <input type="text" class="form-control form-control-lg" name="tracking" placeholder="Enter tracking or booking number" value="<?= htmlspecialchars($tracking) ?>" required>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary btn-lg w-100">Track</button>
            </div>
          </form>
          <?php if ($error): ?>
            <div class="alert alert-danger mt-4"> <?= htmlspecialchars($error) ?> </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php if ($tracking && $details): ?>
<section class="container-fluid py-4 section-glass">
  <div class="glass-card p-4 mb-4 text-center">
    <h2 class="gradient-text mb-3">Status: <?= htmlspecialchars($status) ?></h2>
    <div class="row mb-3">
      <div class="col-md-6 text-start"><b>From:</b> <?= htmlspecialchars($details['from']) ?></div>
      <div class="col-md-6 text-end"><b>To:</b> <?= htmlspecialchars($details['to']) ?></div>
    </div>
    <div class="row mb-3">
      <div class="col-md-6 text-start"><b>Date:</b> <?= htmlspecialchars($details['date']) ?></div>
      <div class="col-md-6 text-end"><b>Service:</b> <?= htmlspecialchars($details['service']) ?></div>
    </div>
    <div class="mb-3"><b>Provider:</b> <?= htmlspecialchars($details['provider']) ?></div>
    <div class="my-4">
      <div id="gmap" style="width:100%;max-width:400px;height:240px;margin:0 auto;border-radius:1rem;"></div>
      <div class="small text-muted mt-2">Route from <b><?= htmlspecialchars($details['from']) ?></b> to <b><?= htmlspecialchars($details['to']) ?></b> (Google Map demo)</div>
    </div>
  </div>
</section>
<script>
  function initTrackingMap() {
    // Demo coordinates for cities
    var cityCoords = {
      'Cairo': {lat:30.0444, lng:31.2357},
      'Alexandria': {lat:31.2001, lng:29.9187},
      'Giza': {lat:30.0131, lng:31.2089},
      'Mansoura': {lat:31.0364, lng:31.3807},
      'Default': {lat:30.0444, lng:31.2357}
    };
    var from = cityCoords['<?= addslashes($details['from']) ?>'] || cityCoords['Default'];
    var to = cityCoords['<?= addslashes($details['to']) ?>'] || cityCoords['Default'];
    var map = new google.maps.Map(document.getElementById('gmap'), {
      zoom: 7,
      center: from,
      mapTypeId: 'roadmap'
    });
    var path = [from, to];
    var polyline = new google.maps.Polyline({
      path: path,
      geodesic: true,
      strokeColor: '#16647e',
      strokeOpacity: 0.8,
      strokeWeight: 5
    });
    polyline.setMap(map);
    new google.maps.Marker({position: from, map: map, label: 'A', icon: {path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#16647e', fillOpacity: 1, strokeWeight: 2, strokeColor: '#fac059'}});
    new google.maps.Marker({position: to, map: map, label: 'B', icon: {path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: '#fac059', fillOpacity: 1, strokeWeight: 2, strokeColor: '#16647e'}});
  }
</script>
<!-- Google Maps JS API (fake developer key for demo only; replace with your real key) -->
<script src="https://maps.googleapis.com/maps/api/js?key=FAKE_DEVELOPER_KEY&callback=initTrackingMap" async defer></script>
<div class="small text-danger mt-2">* This is a demo map with a fake API key. Replace with your real Google Maps API key for production.</div>
<?php endif; ?>
<footer class="footer-glass text-center py-4 mt-5">
  <small>&copy; <?php echo date('Y'); ?> Logistics & Moving Booking System. All rights reserved.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
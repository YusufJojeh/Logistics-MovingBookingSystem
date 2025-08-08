<?php
require_once __DIR__ . '/../config/db.php';
$tracking = trim($_GET['tracking'] ?? '');
$status = '';
$details = null;
$error = '';
if ($tracking) {
    $stmt = mysqli_prepare($conn, "SELECT b.status, b.booking_date, s.title AS service, s.city_from, s.city_to, u.name AS provider 
        FROM bookings b 
        JOIN services s ON b.service_id = s.id 
        JOIN users u ON b.provider_id = u.id 
        WHERE b.id = ? LIMIT 1");
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
  <title>Track Your Booking - Logistics & Moving Booking System</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" href="assets/img/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body {
      background: linear-gradient(120deg, #e5f6fd 0%, #f9fafc 100%) no-repeat;
      min-height:100vh;
    }
    .glass-card {
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(10px);
      border-radius: 1.5rem;
      box-shadow: 0 8px 40px 0 #16647e12;
      border: 1.2px solid #e2e8f0;
    }
    .status-badge {
      font-size: 1.09em;
      font-weight: 600;
      padding: .38em 1.5em;
      border-radius: 1.2em;
      letter-spacing: .02em;
      background: linear-gradient(90deg, #fac059 60%, #16647e 100%);
      color: #fff;
      box-shadow: 0 2px 12px #fac0592a;
      border: 0;
    }
    #trackmap {
      width:100%; height:260px; border-radius:1.1em; border:1.2px solid #e1eaf4; box-shadow:0 4px 24px #16647e0b;
      margin:0 auto 0 auto;
    }
    .booking-info .bi { color:#16647e; margin-right:4px;}
    @media (max-width:700px){
      .glass-card {padding:1.1rem !important;}
    }
  </style>
</head>
<body class="modern-bg">
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-glass shadow-sm sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold fs-3" href="/index.php">
      <img src="../assets/img/logo.svg" alt="MovePro Logo" class="logo-svg">
      <span class="logo-text-white">Move</span><span class="logo-text-blue">Pro</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item"><a class="nav-link" href="/index.php#services">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="/index.php#contact">Contact</a></li>
        <li class="nav-item ms-3"><a class="btn btn-primary px-4" href="/register.php">Get Started</a></li>
      </ul>
    </div>
  </div>
</nav>
<!-- Hero -->
<section class="hero-glass d-flex align-items-center justify-content-center text-center position-relative section-gradient" style="min-height: 28vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="glass-card p-4 p-md-5 mb-3">
          <h1 class="display-5 fw-black mb-2 gradient-text">Track Your Booking</h1>
          <p class="lead mb-4 text-body-secondary">Enter your booking/tracking number to view status and route.</p>
          <form class="row g-2 justify-content-center" method="get" action="">
            <div class="col-12 col-md-8">
              <input type="text" class="form-control form-control-lg" name="tracking" placeholder="Tracking or booking number" value="<?= htmlspecialchars($tracking) ?>" required>
            </div>
            <div class="col-12 col-md-4">
              <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-search me-2"></i>Track</button>
            </div>
          </form>
          <?php if ($error): ?>
            <div class="alert alert-danger mt-3"> <?= htmlspecialchars($error) ?> </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php if ($tracking && $details): ?>
<section class="container py-3">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="glass-card p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h2 class="mb-0 gradient-text fs-4">Booking Details</h2>
          <span class="status-badge"><?= htmlspecialchars($status) ?></span>
        </div>
        <div class="row booking-info mb-3">
          <div class="col-sm-6 mb-2">
            <div><i class="bi bi-geo-alt"></i><b>From:</b> <?= htmlspecialchars($details['from']) ?></div>
            <div><i class="bi bi-calendar-event"></i><b>Date:</b> <?= htmlspecialchars($details['date']) ?></div>
            <div><i class="bi bi-truck"></i><b>Provider:</b> <?= htmlspecialchars($details['provider']) ?></div>
          </div>
          <div class="col-sm-6 text-sm-end mb-2">
            <div><i class="bi bi-geo"></i><b>To:</b> <?= htmlspecialchars($details['to']) ?></div>
            <div><i class="bi bi-box"></i><b>Service:</b> <?= htmlspecialchars($details['service']) ?></div>
          </div>
        </div>
        <div>
          <div id="trackmap"></div>
          <div class="small text-muted mt-2 text-center">
            Route from <b><?= htmlspecialchars($details['from']) ?></b> to <b><?= htmlspecialchars($details['to']) ?></b> (real-time map)
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  let fromCity = "<?= addslashes($details['from']) ?>";
  let toCity   = "<?= addslashes($details['to']) ?>";
  let country  = "Egypt"; // Change this to your country if needed!

  // Always search with 'City, Country' for best results!
  function cityQuery(city) {
    return city + ", " + country;
  }

  let map = L.map('trackmap').setView([26.8,30.8], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 17, attribution: '© OpenStreetMap'
  }).addTo(map);

  function geocode(city, cb) {
    fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(cityQuery(city)))
      .then(r => r.json())
      .then(d => (d && d[0]) ? cb([parseFloat(d[0].lat), parseFloat(d[0].lon)]) : cb(null))
      .catch(()=>cb(null));
  }

  geocode(fromCity, function(fromLatLng) {
    geocode(toCity, function(toLatLng) {
      if (fromLatLng && toLatLng) {
        map.setView([(fromLatLng[0]+toLatLng[0])/2, (fromLatLng[1]+toLatLng[1])/2], 7);
        L.marker(fromLatLng, {title:'From'}).addTo(map).bindPopup('From: ' + fromCity).openPopup();
        L.marker(toLatLng, {title:'To'}).addTo(map).bindPopup('To: ' + toCity);
        L.polyline([fromLatLng, toLatLng], {color:'#fac059',weight:6,opacity:.89}).addTo(map);
      } else {
        document.getElementById('trackmap').innerHTML =
          "<div class='alert alert-warning'>Could not find one or both cities on the map (must be within the same country).</div>";
      }
    });
  });
});

</script>
<?php endif; ?>
<footer class="footer-glass text-center py-4 mt-5">
  <small>&copy; <?= date('Y') ?> Logistics &amp; Moving Booking System. All rights reserved.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

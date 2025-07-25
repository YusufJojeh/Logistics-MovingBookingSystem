<?php
require_once __DIR__ . '/../config/multilanguage.php';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php">
      <svg class="animated-icon me-2" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="8" y="32" width="48" height="16" rx="4" fill="#0d6efd">
          <animate attributeName="x" values="8;16;8" dur="2s" repeatCount="indefinite" />
        </rect>
        <rect x="16" y="24" width="32" height="16" rx="4" fill="#6c757d">
          <animate attributeName="y" values="24;20;24" dur="2s" repeatCount="indefinite" />
        </rect>
      </svg>
      Logistics Booking
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/services.php"><?php echo __('services'); ?></a></li>
        <li class="nav-item"><a class="nav-link" href="/contact.php"><?php echo __('contact'); ?></a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="/profile.php"><?php echo __('profile') ?? 'Profile'; ?></a></li>
          <li class="nav-item"><a class="nav-link" href="/logout.php"><?php echo __('logout'); ?></a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/login.php"><?php echo __('login'); ?></a></li>
          <li class="nav-item"><a class="nav-link btn btn-primary text-white ms-2" href="/register.php"><?php echo __('register'); ?></a></li>
        <?php endif; ?>
        <li class="nav-item dropdown ms-3">
          <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown">
            <?php echo strtoupper($_SESSION['lang'] ?? 'EN'); ?>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/multilanguage.php?lang=en">English</a></li>
            <li><a class="dropdown-item" href="/multilanguage.php?lang=ar">العربية</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav> 
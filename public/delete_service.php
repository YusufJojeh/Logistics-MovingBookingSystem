<?php
session_start();
require_once '../autoload.php';
if (!Auth::check() || Auth::type() !== 'provider') {
    header('Location: login.php');
    exit;
}
$serviceObj = new Service();
$service_id = $_GET['service_id'] ?? null;
$service = $serviceObj->findById($service_id);
if ($service && $service->provider_id == Auth::user()->id) {
    $serviceObj->delete($service_id);
}
header('Location: dashboard.php');
exit; 
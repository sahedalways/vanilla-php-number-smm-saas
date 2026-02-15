<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../include/config.php';
require_once __DIR__ . '/../../../../helpers/session.php';

authOnly();

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// sanitize inputs
$service_id = intval($_POST['service_id'] ?? 0);
$custom_price = floatval($_POST['custom_price'] ?? 0);

$reseller_id = $_SESSION['user_id'] ?? null;

if (!$service_id || $custom_price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// fetch base price
$stmt = $conn->prepare("SELECT base_price FROM services WHERE api_service_id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$stmt->bind_result($base_price);
if (!$stmt->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'Service not found']);
    exit;
}
$stmt->close();

// validate custom price >= base price
if ($custom_price < $base_price) {
    echo json_encode(['status' => 'error', 'message' => "Custom price cannot be less than base price (₦ $base_price)"]);
    exit;
}


$stmt = $conn->prepare("SELECT id FROM reseller_prices WHERE reseller_id = ? AND service_id = ?");
$stmt->bind_param("ii", $reseller_id, $service_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // update existing price
    $stmt->close();
    $stmt = $conn->prepare("UPDATE reseller_prices SET price = ?, updated_at = NOW() WHERE reseller_id = ? AND service_id = ?");
    $stmt->bind_param("dii", $custom_price, $reseller_id, $service_id);
    $stmt->execute();
    $stmt->close();
} else {
    // insert new price
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO reseller_prices (reseller_id, service_id, price, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("iid", $reseller_id, $service_id, $custom_price);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    'status' => 'success',
    'message' => "Custom price set successfully: ₦ " . number_format($custom_price, 2)
]);

<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../include/config.php';
require_once __DIR__ . '/../../../../helpers/session.php';

authOnly();

// CSRF check
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$csrfToken || $csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Sanitize inputs
$service_id = intval($_POST['service_id'] ?? 0);
$custom_price = round(floatval($_POST['custom_price'] ?? 0), 2);
$reseller_id = $_SESSION['user_id'] ?? null;

if (!$service_id || $custom_price <= 0 || !$reseller_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// Fetch base price safely
$stmt = $conn->prepare("SELECT base_price FROM sms_provider_services WHERE id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    echo json_encode(['status' => 'error', 'message' => 'Service not found']);
    exit;
}

$stmt->bind_result($base_price);
$stmt->fetch();
$stmt->close();

// Validate custom price >= base price
if ($custom_price < $base_price) {
    echo json_encode([
        'status' => 'error',
        'message' => "Custom price cannot be less than base price (₦ " . number_format($base_price, 2) . ")"
    ]);
    exit;
}

// Check if price already exists for this reseller
$stmt = $conn->prepare("SELECT id FROM reseller_sms_services_prices WHERE reseller_id = ? AND service_id = ?");
$stmt->bind_param("ii", $reseller_id, $service_id);
$stmt->execute();
$stmt->store_result();

$stmt = $conn->prepare("UPDATE reseller_sms_services_prices SET reseller_price = ? WHERE reseller_id = ? AND service_id = ?");
$stmt->bind_param("dii", $custom_price, $reseller_id, $service_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    'status' => 'success',
    'message' => "Custom price set successfully: ₦ " . number_format($custom_price, 2)
]);
exit;

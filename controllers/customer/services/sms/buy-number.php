


<?php

require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../helpers/currency_helper.php';
require_once __DIR__ . '/../../../../include/config.php';
require_once __DIR__ . '/../../../../class/FiveSimApi.php';

$api = new FiveSimApi();
header('Content-Type: application/json');
authOnly();

$userId = $_SESSION['user_id'] ?? 0;


$csrf_token = $_POST['csrf_token'] ?? '';
if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}


$serviceId = intval($_POST['service_id'] ?? 0);
$country   = trim($_POST['country'] ?? '');
$operator  = trim($_POST['operator'] ?? 'any');
$product   = trim($_POST['product'] ?? '');
$price     = floatval($_POST['price'] ?? 0);

if (!$serviceId || !$country || !$product || $price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid service data.']);
    exit;
}


$stmt = $conn->prepare("
    SELECT id, service_code, country, operator, provider_cost, admin_profit, base_price, count, rate, created_at
    FROM sms_provider_services
    WHERE id = ?
");
$stmt->bind_param("i", $serviceId);
$stmt->execute();
$res = $stmt->get_result();
$service = $res->fetch_assoc();
$stmt->close();

if (!$service) {
    echo json_encode(['status' => 'error', 'message' => 'Service not found.']);
    exit;
}



$basePrice = floatval($service['base_price']);
$apiCost = floatval($service['provider_cost']);


$totalPriceUSD = nairaToUsd($price);
$apiBalanceObj = $api->getBalance();
$apiBalance = floatval($apiBalanceObj['balance'] ?? 0);

if ($totalPriceUSD > $apiBalance) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred, please try again later.'
    ]);
    exit;
}


$stmt = $conn->prepare("SELECT balance FROM user_data WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

$userBalance = floatval($user['balance'] ?? 0);
if ($price > $userBalance) {
    echo json_encode(['status' => 'error', 'message' => 'Insufficient balance.']);
    exit;
}


$order = $api->buyNumber($country, $operator, $product);

// Check if order returned correctly
if (!$order || !isset($order['id'], $order['phone'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to buy number. Please try again.'
    ]);
    exit;
}


$resellerId = null;
$stmt = $conn->prepare("SELECT reseller_id FROM reseller_customers WHERE customer_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $resellerId = $row['reseller_id'];
}
$stmt->close();




// Calculate profit
$adminProfit = 0;
$resellerProfit = 0;

if ($resellerId) {
    // Reseller customer: calculate profits
    $stmt = $conn->prepare("SELECT reseller_price FROM reseller_sms_services_prices WHERE reseller_id = ? AND service_id = ?");
    $stmt->bind_param("ii", $resellerId, $serviceId);
    $stmt->execute();
    $res = $stmt->get_result();
    $resellerPriceRow = $res->fetch_assoc();
    $stmt->close();

    $resellerPrice = floatval($resellerPriceRow['reseller_price'] ?? $basePrice);


    $adminProfit = $basePrice - $apiCost;
    $resellerProfit = max(0, $resellerPrice - $basePrice);
} else {
    $adminProfit = ($basePrice - $apiCost) * $quantity;
    $resellerProfit = 0;
}



// Prepare order data for insertion
$phoneNo   = $order['phone'] ?? null;
$serviceName = $product;
$expiryTime = date('Y-m-d H:i:s', strtotime('+5 minutes'));
$status      = $order['status'] ?? 'PENDING';
$cost        = $price;
$stmt = $conn->prepare("
    INSERT INTO sms_orders (
        service_id, user_id, reseller_id,
        cost, admin_profit, reseller_profit,
        country, operator, phone_no, service,
        expiry_time, status, order_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iiidddsssssss",
    $serviceId,
    $userId,
    $resellerId,
    $cost,
    $adminProfit,
    $resellerProfit,
    $country,
    $operator,
    $phoneNo,
    $serviceName,
    $expiryTime,
    $status,
    $order['id']
);

if ($stmt->execute()) {
    $orderId = $stmt->insert_id;

    // Deduct user balance
    $stmt = $conn->prepare("UPDATE user_data SET balance = balance - ? WHERE id = ?");
    $stmt->bind_param("di", $cost, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Order placed successfully.',
        'order_id' => $orderId,
        'phone' => $phoneNo
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save order: ' . $stmt->error
    ]);
}

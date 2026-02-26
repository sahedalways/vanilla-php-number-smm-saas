


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


$country  = strtolower(trim($_POST['country'] ?? ''));
$operator = strtolower(trim($_POST['operator'] ?? 'any'));
$product  = strtolower(trim($_POST['product'] ?? ''));
$price    = floatval($_POST['price'] ?? 0);


if (empty($country)) {
    echo json_encode(['status' => 'error', 'message' => 'Country is required.']);
    exit;
}

if (empty($product)) {
    echo json_encode(['status' => 'error', 'message' => 'Product is required.']);
    exit;
}

if ($price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid price.']);
    exit;
}

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized user.']);
    exit;
}

$userRow = $conn->query("
    SELECT balance
    FROM user_data
    WHERE id = {$userId}
    LIMIT 1
")->fetch_assoc();

if (!$userRow) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}

$userBalance = floatval($userRow['balance'] ?? 0);


if ($userBalance < $price) {
    echo json_encode(['status' => 'error', 'message' => 'Insufficient balance.']);
    exit;
}




$profitRow = $conn->query("SELECT profit_percentage FROM profit_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
$adminProfitPercent = floatval($profitRow['profit_percentage'] ?? 0);


$resellerRow = $conn->query("
    SELECT r.profit_percentage AS reseller_profit_percentage, rc.reseller_id
    FROM reseller_customers rc
    JOIN reseller_sms_profit_settings r
        ON rc.reseller_id = r.user_id
    WHERE rc.customer_id = {$userId}
    ORDER BY r.id DESC
    LIMIT 1
")->fetch_assoc();

$resellerProfitPercent = floatval($resellerRow['profit_percentage'] ?? 0);


$adminMultiplier = 1 + ($adminProfitPercent / 100);
$resellerMultiplier = 1 + ($resellerProfitPercent / 100);

$totalMultiplier = $adminMultiplier * $resellerMultiplier;

$basePrice = $price;

if ($totalMultiplier > 0) {
    $basePrice = $price / $totalMultiplier;
}


$adminProfit = round($basePrice * ($adminProfitPercent / 100), 2);
$resellerProfit = round(($basePrice + $adminProfit) * ($resellerProfitPercent / 100), 2);

$basePrice = round($basePrice, 4);
$basePrice = nairaToUsd($basePrice);


// $isApiBalanceAvailable = $api->getBalance();

// $frozenBalance = floatval($isApiBalanceAvailable['data']['frozen_balance'] ?? 0);
// $availableBalance = $apiBalance - $frozenBalance;



// echo json_encode([
//     'status' => 'error',
//     'message' => $isApiBalanceAvailable
// ]);
// exit;


// $buyData = $api->buyNumber($country, $operator, $product);



// if (!$buyData) {
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Failed to purchase number',
//     ]);
//     exit;
// }


// // Send response to frontend
// echo json_encode([
//     'status' => 'success',
//     'message' => 'Number purchased successfully',
//     'data' => $buyData
// ]);
// exit;

$resellerId = $resellerRow['reseller_id'] ?? null;

$buyData = [
    'id' => 12345678,
    'phone' => '+447350690992',
    'operator' => 'vodafone',
    'product' => 'facebook',
    'price' => 21.00,
    'status' => 'PENDING',
    'country' => 'england',
    'sms' => null,
    'forwarding' => false,
    'forwarding_number' => '',
    'created_at' => '2026-02-26T09:00:00Z'
];



$otp = '';

$orderId = $buyData['id'] ?? null;
$phoneNo = $buyData['phone'] ?? null;
$operatorName = $buyData['operator'] ?? $operator;
$productName = $buyData['product'] ?? $product;
$price = $price ?? 0;
$status = $buyData['status'] ?? 'PENDING';
$expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes')) ?? null;
$countryName = $buyData['country'] ?? $country;
$createdAt = isset($buyData['created_at']) ? date('Y-m-d H:i:s', strtotime($buyData['created_at'])) : date('Y-m-d H:i:s');

$stmt = $conn->prepare("
    INSERT INTO sms_orders
    (order_id, user_id, reseller_id, cost, admin_profit, reseller_profit, country, operator, phone_no, otp, service, expiry_time, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");


$stmt->bind_param(
    "siidddssssssss",
    $orderId,
    $userId,
    $resellerId,
    $price,
    $adminProfit,
    $resellerProfit,
    $countryName,
    $operatorName,
    $phoneNo,
    $otp,
    $productName,
    $expiryTime,
    $status,
    $createdAt
);

if ($stmt->execute()) {
    $updateBalanceStmt = $conn->prepare("
        UPDATE user_data
        SET balance = balance - ?
        WHERE id = ? AND balance >= ?
    ");

    $updateBalanceStmt->bind_param("dii", $price, $userId, $price);

    if ($updateBalanceStmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'SMS order placed successfully.',
            'order_id' => $orderId
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Order inserted but failed to deduct balance: ' . $updateBalanceStmt->error
        ]);
    }

    $updateBalanceStmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to SMS order: ' . $stmt->error
    ]);
}

$stmt->close();

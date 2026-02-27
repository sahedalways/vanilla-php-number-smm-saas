<?php

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../helpers/currency_helper.php';
require_once __DIR__ . '/../../../../include/config.php';
require_once __DIR__ . '/../../../../class/FiveSimApi.php';


$api = new FiveSimApi();
authOnly();

$userId = $_SESSION['user_id'] ?? 0;


$csrf_token = $_POST['csrf_token'] ?? '';
if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}


$country  = strtolower(trim($_POST['country'] ?? ''));
$serviceCode = strtolower(trim($_POST['serviceCode'] ?? 'any'));
$operator  = strtolower(trim($_POST['operator'] ?? ''));
$price    = floatval($_POST['price'] ?? 0);


if (empty($country)) {
    echo json_encode(['status' => 'error', 'message' => 'Country is required.']);
    exit;
}

if (empty($operator)) {
    echo json_encode(['status' => 'error', 'message' => 'Operator is required.']);
    exit;
}

if (empty($serviceCode)) {
    echo json_encode(['status' => 'error', 'message' => 'Service is required.']);
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

$resellerProfitPercent = 0;
if ($resellerRow) {
    $resellerProfitPercent = floatval($resellerRow['reseller_profit_percentage'] ?? 0);
}



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



$balanceResponse = $api->getBalance();


$frozenBalance = floatval($balanceResponse['data']['frozen_balance'] ?? 0);
$mainBalance = floatval($balanceResponse['data']['balance'] ?? 0);

$availableBalance = $mainBalance - $frozenBalance;

if ($availableBalance < $basePrice) {
    echo json_encode(['status' => 'error', 'message' => 'An error occured! Please try again later.']);
    exit;
}


$buyData = $api->buyNumber($country, $operator, $serviceCode);


if (
    empty($buyData) ||
    empty($buyData['success']) ||
    $buyData['success'] !== true ||
    (isset($buyData['raw']) &&
        in_array(strtolower($buyData['raw']), ['no free phones', 'bad operator', 'not enough balance']))
) {
    echo json_encode([
        'status' => 'error',
        'message' => $buyData['raw'] ?? 'Failed to purchase number',
        'debug' => $buyData
    ]);
    exit;
}


$purchase = $buyData['data'] ?? null;



$resellerId = $resellerRow['reseller_id'] ?? null;


$otp = '';

$orderId = $purchase['id'] ?? null;
$phoneNo = $purchase['phone'] ?? null;
$operatorName = $purchase['operator'] ?? $operator;
$productName = $purchase['product'] ?? $product;
$price = $price ?? 0;
$status = $purchase['status'] ?? 'RECEIVED';
$expiryTime = isset($purchase['expires']) ? date('Y-m-d H:i:s', strtotime($purchase['expires'])) : date('Y-m-d H:i:s', strtotime('+5 minutes'));
$countryName = $purchase['country'] ?? $country;
$createdAt = isset($purchase['created_at']) ? date('Y-m-d H:i:s', strtotime($purchase['created_at'])) : date('Y-m-d H:i:s');
$resellerId = $resellerRow['reseller_id'] ?? 0;
$otp = '';

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
    $userId = (int)$userId;
    $price  = (float)$price;

    $updateWalletStmt = $conn->prepare("
    UPDATE user_wallet
    SET balance = balance - ?
    WHERE user_id = ? AND balance >= ?
");

    $updateWalletStmt->bind_param("did", $price, $userId, $price);

    // Execute
    $updateWalletStmt->execute();

    if ($updateWalletStmt->affected_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'SMS order placed successfully.',
            'order_id' => $orderId
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to deduct balance. Either user not found or insufficient funds.',
            'user_id' => $userId,
            'price' => $price,
        ]);
    }

    $updateWalletStmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to SMS order: ' . $stmt->error
    ]);
}

$stmt->close();

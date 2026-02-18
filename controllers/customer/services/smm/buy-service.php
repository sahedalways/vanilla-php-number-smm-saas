


<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../helpers/currency_helper.php';
require_once __DIR__ . '/../../../../include/config.php';
require_once __DIR__ . '/../../../../class/SMMApi.php';
$api = new SMMApi();


header('Content-Type: application/json');

authOnly();

$userId = $_SESSION['user_id'];

// CSRF validation
$csrf_token = $_POST['csrf_token'] ?? '';
if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}


// Get POST data
$serviceId   = intval($_POST['service_id'] ?? 0);
$serviceName = trim($_POST['service_name'] ?? '');
$unitPrice   = floatval($_POST['unit_price'] ?? 0);
$quantity    = intval($_POST['quantity'] ?? 0);
$totalPrice  = floatval($_POST['total_price'] ?? 0);
$orderParams  = $_POST['order_params'] ?? [];



// Validation
if ($serviceId <= 0 || $quantity <= 0 || $unitPrice <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
    exit;
}

// Check if service exists and active
$stmt = $conn->prepare("SELECT id, base_price, api_price, type, category, cancel, refill FROM services WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $serviceId);
$stmt->execute();
$res = $stmt->get_result();
$service = $res->fetch_assoc();
$stmt->close();

if (!$service) {
    echo json_encode(['status' => 'error', 'message' => 'Service not found or inactive.']);
    exit;
}


$basePrice = floatval($service['base_price']);


$apiCost = floatval($service['api_price']);

// Check min/max limits
$stmt = $conn->prepare("SELECT min, max FROM services WHERE id = ?");
$stmt->bind_param("i", $serviceId);
$stmt->execute();
$res = $stmt->get_result();
$limits = $res->fetch_assoc();
$stmt->close();

$minQty = intval($limits['min'] ?? 1);
$maxQty = intval($limits['max'] ?? 999999);

if ($quantity < $minQty || $quantity > $maxQty) {
    echo json_encode(['status' => 'error', 'message' => "Quantity must be between $minQty and $maxQty."]);
    exit;
}


$totalPriceNaira = $unitPrice * $quantity;


$totalPriceUSD = nairaToUsd($totalPriceNaira);


$apiBalanceObj = $api->balance();
$apiBalance = floatval($apiBalanceObj->balance ?? 0);


if ($totalPriceUSD > $apiBalance) {
    echo json_encode([
        'status' => 'error',
        'message' => "An error occurred, please try again later."
    ]);
    exit;
}


// Check user balance
$stmt = $conn->prepare("SELECT balance FROM user_data WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

$userBalance = floatval($user['balance'] ?? 0);

if ($totalPrice > $userBalance) {
    echo json_encode(['status' => 'error', 'message' => 'Insufficient balance.']);
    exit;
}

// Determine if user is reseller customer
$resellerId = null;
$stmt = $conn->prepare("SELECT reseller_id FROM reseller_customers WHERE customer_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $resellerId = $row['reseller_id'];
}
$stmt->close();


$apiOrder = $api->order(array_merge(['service' => $serviceId, 'quantity' => $quantity], $orderParams));

if (!$apiOrder || !isset($apiOrder->order)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to place order.']);
    exit;
}

$apiOrderId = $apiOrder->order;


// Calculate profit
$adminProfit = 0;
$resellerProfit = 0;

if ($resellerId) {
    // Reseller customer: calculate profits
    $stmt = $conn->prepare("SELECT price FROM reseller_prices WHERE reseller_id = ? AND service_id = ?");
    $stmt->bind_param("ii", $resellerId, $serviceId);
    $stmt->execute();
    $res = $stmt->get_result();
    $resellerPriceRow = $res->fetch_assoc();
    $stmt->close();

    $resellerPricePerUnit = floatval($resellerPriceRow['price'] ?? $basePrice);


    $adminOriginalProfitPerUnit = $basePrice - $apiCost;
    $resellerExtraProfitPerUnit = max(0, $resellerPricePerUnit - $basePrice);

    // 3. Total profits for order
    $resellerProfit = $resellerExtraProfitPerUnit * $quantity;
    $adminProfit = $adminOriginalProfitPerUnit * $quantity;
} else {
    $adminProfit = ($basePrice - $apiCost) * $quantity;
    $resellerProfit = 0;
}


$serviceType = $service['type'] ?? 'Default';
$category    = $service['category'] ?? '';
$cancel      = intval($service['cancel'] ?? 0);
$refill      = intval($service['refill'] ?? 0);



// Insert order
$stmt = $conn->prepare("
    INSERT INTO smm_orders
    (service_id, quantity, user_id, reseller_id, cost, admin_profit, reseller_profit, api_order_id, status, service_type, category, cancel, refill)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'In Progress', ?, ?, ?, ?)
");


$stmt->bind_param(
    "iiiidddsssii",
    $serviceId,
    $quantity,
    $userId,
    $resellerId,
    $totalPrice,
    $adminProfit,
    $resellerProfit,
    $apiOrderId,
    $serviceType,
    $category,
    $cancel,
    $refill
);





if ($stmt->execute()) {
    $stmt = $conn->prepare("UPDATE user_data SET balance = balance - ? WHERE id = ?");
    $stmt->bind_param("di", $totalPrice, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Order placed successfully.',
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save order locally.']);
}

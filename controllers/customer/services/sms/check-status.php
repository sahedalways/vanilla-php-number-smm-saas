<?php

require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../helpers/currency_helper.php';
require_once __DIR__ . '/../../../../include/config.php';
require_once __DIR__ . '/../../../../class/FiveSimApi.php';

header('Content-Type: application/json');
authOnly();

$userId = $_SESSION['user_id'] ?? 0;

// Validate CSRF
$csrf_token = $_POST['csrf_token'] ?? '';
if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Get order_id string
$orderIdStr = trim($_POST['order_id'] ?? '');
if (!$orderIdStr) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit;
}

// Fetch order by order_id
$stmt = $conn->prepare("SELECT * FROM sms_orders WHERE order_id = ? LIMIT 1");
$stmt->bind_param("s", $orderIdStr);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

// Call API to check SMS
$api = new FiveSimApi();
$apiResponse = $api->getSMS($orderIdStr);

$orderStatus = $apiResponse['status'] ?? $order['status'];
$otpCode = $apiResponse['sms'][0]['code'] ?? null;



// START TRANSACTION
$conn->begin_transaction();
try {

    // Refund for canceled/timeout/banned
    if (in_array($orderStatus, ['CANCELED', 'TIMEOUT', 'BANNED'])) {
        $stmt = $conn->prepare("
    UPDATE user_data
    SET balance = COALESCE(balance, 0) + ?
    WHERE id = ?
");
        $stmt->bind_param("di", $order['cost'], $order['user_id']);
        $stmt->execute();
        $stmt->close();
    }

    // Add profits if finished
    if ($orderStatus === 'FINISHED') {
        $stmt = $conn->prepare("
    UPDATE user_data
    SET balance = COALESCE(balance, 0) + ?
    WHERE type = 'admin'
    LIMIT 1
");
        $stmt->bind_param("d", $order['admin_profit']);
        $stmt->execute();
        $stmt->close();


        // Reseller profit
        if (!empty($order['reseller_id']) && $order['reseller_profit'] > 0) {
            $stmt = $conn->prepare("
        UPDATE user_data
        SET balance = COALESCE(balance, 0) + ?
        WHERE id = ?
    ");
            $stmt->bind_param("di", $order['reseller_profit'], $order['reseller_id']);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Update order status & OTP
    $expiryUpdate = null;
    if (in_array($orderStatus, ['BANNED', 'FINISHED', 'TIMEOUT', 'CANCELED'])) {
        $expiryUpdate = date('Y-m-d H:i:s');
    }


    if ($expiryUpdate) {
        $updateStmt = $conn->prepare("
        UPDATE sms_orders
        SET status = ?, otp = ?, updated_at = NOW(), expiry_time = ?
        WHERE id = ?
    ");
        $updateStmt->bind_param("sssi", $orderStatus, $otpCode, $expiryUpdate, $order['id']);
    } else {
        $updateStmt = $conn->prepare("
        UPDATE sms_orders
        SET status = ?, otp = ?, updated_at = NOW()
        WHERE id = ?
    ");
        $updateStmt->bind_param("ssi", $orderStatus, $otpCode, $order['id']);
    }

    $updateStmt->execute();
    $updateStmt->close();


    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

// Return JSON for frontend
echo json_encode([
    'status' => 'success',
    'order_status' => $orderStatus,
    'otp' => $otpCode
]);

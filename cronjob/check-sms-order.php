<?php
// File: cron/check_sms_orders.php

require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../class/FiveSimApi.php';

$api = new FiveSimApi();
$now = date('Y-m-d H:i:s');


$stmt = $conn->prepare("
    SELECT * FROM sms_orders
    WHERE status IN ('PENDING', 'RECEIVED')
      AND expiry_time > ?
");
$stmt->bind_param("s", $now);
$stmt->execute();
$res = $stmt->get_result();
$orders = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($orders as $order) {
    $orderIdStr = $order['order_id'];

    $orderStatus = $apiResponse['status'] ?? $order['status'];
    $otpCode = $apiResponse['sms'][0]['code'] ?? null;


    $expiryUpdate = null;
    if (in_array($orderStatus, ['CANCELED', 'TIMEOUT', 'BANNED', 'FINISHED'])) {
        $expiryUpdate = date('Y-m-d H:i:s');
    }

    $conn->begin_transaction();
    try {
        if (in_array($orderStatus, ['CANCELED', 'TIMEOUT', 'BANNED'])) {
            $stmt = $conn->prepare("UPDATE user_data SET balance = COALESCE(balance,0) + ? WHERE id = ?");
            $stmt->bind_param("di", $order['cost'], $order['user_id']);
            $stmt->execute();
            $stmt->close();
        }

        // Add profits if finished
        if ($orderStatus === 'FINISHED') {
            // Admin profit
            $stmt = $conn->prepare("UPDATE user_data SET balance = COALESCE(balance,0) + ? WHERE type = 'admin' LIMIT 1");
            $stmt->bind_param("d", $order['admin_profit']);
            $stmt->execute();
            $stmt->close();


            if (!empty($order['reseller_id']) && $order['reseller_profit'] > 0) {
                $stmt = $conn->prepare("UPDATE user_data SET balance = COALESCE(balance,0) + ? WHERE id = ?");
                $stmt->bind_param("di", $order['reseller_profit'], $order['reseller_id']);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Update order status & OTP
        if ($expiryUpdate) {
            $updateStmt = $conn->prepare("
            UPDATE sms_orders
            SET status = ?, otp = ?, updated_at = NOW(), otp_in_time = ?
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
        echo "Order {$order['order_id']} updated successfully.\n";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Failed to update order {$order['order_id']}: {$e->getMessage()}\n";
    }
}

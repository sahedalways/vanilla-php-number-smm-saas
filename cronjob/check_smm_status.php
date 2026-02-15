<?php
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../helpers/smm_helper.php';

$api = new SMMAPI('DUMMY_KEY', true);



$stmt = $conn->prepare("SELECT * FROM smm_orders WHERE status = 'processing'");
$stmt->execute();
$res = $stmt->get_result();
$orders = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$orders) {
    echo "No processing orders found.\n";
    exit;
}

foreach ($orders as $order) {

    $apiOrderId = $order['api_order_id'];
    $orderId = $order['id'];

    $statusData = $api->call('status', ['order' => $apiOrderId]);

    if (!$statusData) {
        echo "Order ID $orderId: API returned no data.\n";
        continue;
    }

    $apiStatus = strtolower($statusData['status'] ?? '');

    // Map API status to local status
    if ($apiStatus === 'completed' || $apiStatus === 'success') {
        $newStatus = 'success';
    } elseif ($apiStatus === 'in progress' || $apiStatus === 'partial') {
        $newStatus = 'processing';
    } elseif ($apiStatus === 'rejected' || $apiStatus === 'failed') {
        $newStatus = 'failed';
    } else {
        $newStatus = 'processing';
    }

    // Handle processing/partial updates
    if ($newStatus === 'processing') {
        $remains = intval($statusData['remains'] ?? $order['remains']);

        $stmt = $conn->prepare("UPDATE smm_orders SET status = ?, remains = ? WHERE id = ?");
        $stmt->bind_param("sii", $newStatus, $remains, $orderId);
        if ($stmt->execute()) {
            echo "Order ID $orderId updated: status = $newStatus, remains = $remains\n";
        } else {
            echo "Order ID $orderId: failed to update remains.\n";
        }
        $stmt->close();
    }

    // Handle completed/success orders
    if ($newStatus === 'success') {
        // Admin balance
        $adminStmt = $conn->prepare("SELECT id FROM user_data WHERE type = 'admin'");
        $adminStmt->execute();
        $adminRes = $adminStmt->get_result();

        while ($adminRow = $adminRes->fetch_assoc()) {
            $adminId = $adminRow['id'];

            $stmt = $conn->prepare("UPDATE user_data SET balance = balance + ? WHERE id = ?");
            $stmt->bind_param("di", $order['admin_profit'], $adminId);
            $stmt->execute();
            $stmt->close();

            echo "Admin ID $adminId balance updated with {$order['admin_profit']}\n";
        }

        $adminStmt->close();

        // Reseller balance
        if ($order['reseller_id']) {
            $stmt = $conn->prepare("UPDATE user_data SET balance = balance + ? WHERE id = ?");
            $stmt->bind_param("di", $order['reseller_profit'], $order['reseller_id']);
            $stmt->execute();
            $stmt->close();
        }


        $stmt = $conn->prepare("UPDATE smm_orders SET status = 'success' WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();

        echo "Order ID $orderId completed. Balances updated.\n";
    }


    if ($newStatus === 'failed') {
        // Refund user
        $stmt = $conn->prepare("UPDATE user_data SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $order['cost'], $order['user_id']);
        $stmt->execute();
        $stmt->close();



        $stmt = $conn->prepare("UPDATE smm_orders SET status = 'failed' WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();

        echo "Order ID $orderId failed. User refunded.\n";
    }
}

echo "Cron job completed.\n";

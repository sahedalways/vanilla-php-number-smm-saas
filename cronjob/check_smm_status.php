<?php
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../class/SMMApi.php';

$api = new SMMApi();




$stmt = $conn->prepare("SELECT * FROM smm_orders WHERE status = 'In Progress'");
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

    $statusData = $api->status($apiOrderId);

    if (!$statusData) {
        echo "Order ID $orderId: API returned no data.\n";
        continue;
    }

    $apiStatus = strtolower($statusData->status ?? '');


    // Map API status to local status
    if ($apiStatus == 'Completed' || $apiStatus === 'Success') {
        $newStatus = 'Completed';
    } elseif ($apiStatus == 'Partial') {
        $newStatus = 'Partial';
    } elseif ($apiStatus == 'Rejected' || $apiStatus == 'Failed') {
        $newStatus = 'Failed';
    } else {
        $newStatus = 'In Progress';
    }

    // Handle processing/partial updates
    if ($newStatus == 'In Progress' || $newStatus == 'Partial') {
        $remains   = intval($statusData->remains ?? $order['remains']);

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
    if ($newStatus == 'Success' || $newStatus == 'Completed' || $newStatus == 'Partial') {
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


        $stmt = $conn->prepare("UPDATE smm_orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();

        echo "Order ID $orderId completed. Balances updated.\n";
    }


    if ($newStatus == 'Rejected' || $newStatus == 'Failed') {
        // Refund user
        $stmt = $conn->prepare("UPDATE user_data SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $order['cost'], $order['user_id']);
        $stmt->execute();
        $stmt->close();



        $stmt = $conn->prepare("UPDATE smm_orders SET status = 'Failed' WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();

        echo "Order ID $orderId failed. User refunded.\n";
    }
}

echo "Cron job completed.\n";

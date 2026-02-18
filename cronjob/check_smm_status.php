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

    // Map API status to local status (all lowercase first for consistency)
    switch ($apiStatus) {
        case 'completed':
        case 'success':
            $newStatus = 'Completed';
            break;
        case 'partial':
            $newStatus = 'Partial';
            break;
        case 'rejected':
        case 'failed':
            $newStatus = 'Failed';
            break;
        default:
            $newStatus = 'In Progress';
            break;
    }

    // Handle processing/partial updates
    if (in_array($newStatus, ['In Progress', 'Partial'])) {
        $remains = intval($statusData->remains ?? $order['remains'] ?? 0);

        $stmt = $conn->prepare("UPDATE smm_orders SET status = ?, remains = ? WHERE id = ?");
        $stmt->bind_param("sii", $newStatus, $remains, $orderId);
        $stmt->execute();
        $stmt->close();

        echo "Order ID $orderId updated: status = $newStatus, remains = $remains\n";
    }

    // Handle completed/success orders
    if (in_array($newStatus, ['Completed', 'Success'])) {
        $stmt = $conn->prepare("
    UPDATE user_data
    SET balance = COALESCE(balance, 0) + ?
    WHERE type = 'admin'
    LIMIT 1
");
        $stmt->bind_param("d", $order['admin_profit']);
        $stmt->execute();
        $stmt->close();

        // Reseller balance
        if (!empty($order['reseller_id'])) {
            $stmt = $conn->prepare("
        UPDATE user_data
        SET balance = COALESCE(balance, 0) + ?
        WHERE id = ?
    ");
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

    // Handle rejected/failed orders
    if ($newStatus == 'Failed') {
        // Refund user
        $stmt = $conn->prepare("
    UPDATE user_data
    SET balance = COALESCE(balance, 0) + ?
    WHERE id = ?
");
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

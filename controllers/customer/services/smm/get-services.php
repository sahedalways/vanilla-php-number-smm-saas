<?php
// get-services.php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

authOnly();

$userId = $_SESSION['user_id'];

// Check if current user is a reseller's customer
$resellerId = null;
$stmt = $conn->prepare("SELECT reseller_id FROM reseller_customers WHERE customer_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $resellerId = $row['reseller_id'];
}
$stmt->close();

// Fetch services
$sql = "SELECT * FROM services WHERE status = 'active' ORDER BY id DESC";
$result = $conn->query($sql);

$services = [];

while ($row = $result->fetch_assoc()) {
    $price = floatval($row['base_price']);


    if ($resellerId) {
        $stmt = $conn->prepare("SELECT price FROM reseller_prices WHERE service_id = ? AND reseller_id = ?");
        $stmt->bind_param("ii", $row['id'], $resellerId);
        $stmt->execute();
        $resPriceResult = $stmt->get_result();
        if ($resPriceRow = $resPriceResult->fetch_assoc()) {
            $price = floatval($resPriceRow['price']);
        }
        $stmt->close();
    }
    $orderParams = [];

    switch ($row['type'] ?? 'Default') {
        case 'Default':
            $orderParams = [
                'link' => '',
                'quantity' => null,
            ];
            break;

        case 'Custom Comments':
        case 'Custom Comments Package':
            $orderParams = [
                'link' => '',
                'comments' => '',
            ];
            break;

        case 'Comment Likes':
            $orderParams = [
                'link' => '',
                'username' => '',
                'quantity' => null,
            ];
            break;

        case 'YouTube Likes':
        case 'TikTok Views':
            $orderParams = [
                'link' => '',
                'quantity' => null,
            ];
            break;

        default:
            $orderParams = [
                'link' => '',
                'quantity' => null,
            ];
            break;
    }

    // Add service to array
    $services[] = [
        'id' => $row['id'],
        'api_service_id' => $row['api_service_id'],
        'name' => $row['name'],
        'base_price' => floatval($row['base_price']),
        'api_price' => floatval($row['api_price']),
        'price' => $price,
        'min' => $row['min'],
        'max' => $row['max'],
        'status' => $row['status'],
        'type' => $row['type'] ?? 'Default',
        'category' => $row['category'] ?? '',
        'refill' => $row['refill'] ?? false,
        'cancel' => $row['cancel'] ?? false,
        'order_params' => $orderParams
    ];
}

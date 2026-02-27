<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

header('Content-Type: application/json');

authOnly();

$userId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

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

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM services WHERE status = 'active'";
$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch services with pagination
$sql = "SELECT * FROM services WHERE status = 'active' ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$services = [];

while ($row = $result->fetch_assoc()) {
    $price = floatval($row['base_price']);

    if ($resellerId) {
        $priceStmt = $conn->prepare("SELECT price FROM reseller_prices WHERE service_id = ? AND reseller_id = ?");
        $priceStmt->bind_param("ii", $row['api_service_id'], $resellerId);
        $priceStmt->execute();
        $resPriceResult = $priceStmt->get_result();
        if ($resPriceRow = $resPriceResult->fetch_assoc()) {
            $price = floatval($resPriceRow['price']);
        }
        $priceStmt->close();
    }

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
        'refill' => (bool)($row['refill'] ?? false),
        'cancel' => (bool)($row['cancel'] ?? false)
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'services' => $services,
    'current_page' => $page,
    'total_pages' => $totalPages,
    'total_rows' => $totalRows,
    'limit' => $limit
]);

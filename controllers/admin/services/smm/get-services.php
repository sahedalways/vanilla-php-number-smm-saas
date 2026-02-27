<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

header('Content-Type: application/json');

// Check if user is reseller
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'reseller') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM services WHERE status = 'active'";
$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch services with pagination
$sql = "
SELECT
    s.id,
    s.api_service_id,
    s.name,
    s.base_price,
    s.api_price,
    s.min,
    s.max,
    s.type,
    s.category,
    s.cancel,
    s.refill,
    s.status,
    rp.price AS reseller_price
FROM services s
LEFT JOIN reseller_prices rp
    ON rp.service_id = s.api_service_id
    AND rp.reseller_id = ?
WHERE s.status = 'active'
ORDER BY s.id DESC
LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $userId, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$services = [];
while ($row = $result->fetch_assoc()) {
    $price = is_null($row['reseller_price']) ? floatval($row['base_price']) : floatval($row['reseller_price']);
    $services[] = [
        'id'             => (int)$row['id'],
        'api_service_id' => (int)$row['api_service_id'],
        'name'           => $row['name'],
        'base_price'     => floatval($row['base_price']),
        'api_price'      => floatval($row['api_price']),
        'price'          => $price,
        'min'            => (int)$row['min'],
        'max'            => (int)$row['max'],
        'status'         => $row['status'],
        'type'           => $row['type'] ?? 'Default',
        'category'       => $row['category'] ?? '',
        'cancel'         => (bool)($row['cancel'] ?? 0),
        'refill'         => (bool)($row['refill'] ?? 0)
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

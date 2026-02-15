<?php
// get-services.php
require_once __DIR__ . '/../../../helpers/session.php';
require_once __DIR__ . '/../../../include/config.php';

authOnly();

$userId = $_SESSION['user_id'];

$sql = "
SELECT
    s.id,
    s.api_service_id,
    s.name,
    s.base_price,
    s.api_price,
    s.min,
    s.max,
    s.status,
    rp.price AS reseller_price
FROM services s
LEFT JOIN reseller_prices rp
    ON rp.service_id = s.api_service_id
    AND rp.reseller_id = ?
WHERE s.status = 'active'
ORDER BY s.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$services = [];
while ($row = $result->fetch_assoc()) {
    $price = is_null($row['reseller_price']) ? floatval($row['base_price']) : floatval($row['reseller_price']);

    $services[] = [
        'id' => $row['id'],
        'api_service_id' => $row['api_service_id'],
        'name' => $row['name'],
        'base_price' => floatval($row['base_price']),
        'api_price' => floatval($row['api_price']),
        'price' => $price,
        'min' => $row['min'],
        'max' => $row['max'],
        'status' => $row['status']
    ];
}

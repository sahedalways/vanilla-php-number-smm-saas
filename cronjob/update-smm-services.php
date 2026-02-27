<?php
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../class/SMMApi.php';
require_once __DIR__ . '/../helpers/currency_helper.php';


$api = new SMMApi();
$services = $api->services();


ini_set('max_execution_time', 300);
$conn->set_charset("utf8mb4");

$api = new SMMApi();
$services = $api->services();

if (empty($services)) {
    echo "No services received\n";
    exit;
}

$conn->query("DELETE FROM services");


// admin profit
$profitRow = $conn->query("
    SELECT profit_percentage
    FROM profit_settings
    ORDER BY id DESC
    LIMIT 1
")->fetch_assoc();

$adminProfitPercent = floatval($profitRow['profit_percentage'] ?? 0);

$currentIds = [];
$servicesData = [];

/**
 * ===============================
 * PREPARE SERVICES
 * ===============================
 */
foreach ($services as $service) {

    // 🔥 object access FIXED
    $usdPrice = floatval($service->rate);
    $basePrice = usdToNaira($usdPrice);

    $priceWithProfit = $basePrice + ($basePrice * $adminProfitPercent / 100);
    $priceWithProfit = round($priceWithProfit, 4);

    $serviceId = intval($service->service);
    $currentIds[] = $serviceId;

    $servicesData[] = [
        'api_service_id' => $serviceId,
        'name' => $service->name,
        'base_price' => $priceWithProfit,
        'api_price' => $basePrice,
        'min' => intval($service->min),
        'max' => intval($service->max),
        'status' => 'active',
        'category' => $service->category ?? null,
        'type' => $service->type ?? null,
        'cancel' => !empty($service->cancel) ? 1 : 0,
        'refill' => !empty($service->refill) ? 1 : 0,
    ];
}

/**
 * ===============================
 * UPSERT SERVICES
 * ===============================
 */
$stmt = $conn->prepare("
    INSERT INTO services (
        api_service_id,
        name,
        base_price,
        api_price,
        status,
        min,
        max,
        category,
        cancel,
        refill,
        type
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        base_price = VALUES(base_price),
        api_price = VALUES(api_price),
        status = VALUES(status),
        min = VALUES(min),
        max = VALUES(max),
        category = VALUES(category),
        cancel = VALUES(cancel),
        refill = VALUES(refill),
        type = VALUES(type)
");

foreach ($servicesData as $s) {
    $stmt->bind_param(
        "isddsiissis",
        $s['api_service_id'],
        $s['name'],
        $s['base_price'],
        $s['api_price'],
        $s['status'],
        $s['min'],
        $s['max'],
        $s['category'],
        $s['cancel'],
        $s['refill'],
        $s['type']
    );

    $stmt->execute();
}

$stmt->close();

/**
 * ===============================
 * DISABLE REMOVED SERVICES 🔥
 * ===============================
 */
if (!empty($currentIds)) {
    $ids = implode(',', array_map('intval', $currentIds));
    $conn->query("
        UPDATE services
        SET status='inactive'
        WHERE api_service_id NOT IN ($ids)
    ");
}

/**
 * ===============================
 * RESELLER PRICE BULK INSERT 🚀
 * ===============================
 */
$conn->query("
    INSERT INTO reseller_prices (reseller_id, service_id, price, created_at, updated_at)
    SELECT r.id, s.api_service_id, s.base_price, NOW(), NOW()
    FROM user_data r
    CROSS JOIN services s
    WHERE r.type='reseller'
    AND NOT EXISTS (
        SELECT 1 FROM reseller_prices rp
        WHERE rp.reseller_id = r.id
        AND rp.service_id = s.api_service_id
    )
");

echo "Services updated at " . date('Y-m-d H:i:s') . PHP_EOL;

<?php
require_once __DIR__ . '/../helpers/smm_helper.php';
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../helpers/currency_helper.php';
$api = new SMMAPI('DUMMY_KEY', true);


$response = $api->services();
$conn->query("TRUNCATE TABLE services");

$profitRow = $conn->query("SELECT profit_percentage FROM profit_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
$adminProfitPercent = floatval($profitRow['profit_percentage'] ?? 0);

// Prepare services array
$servicesData = [];

foreach ($response as $service) {
    $usdPrice = $service['rate'];
    $basePrice = usdToNaira($usdPrice);

    // Add admin profit
    $priceWithProfit = $basePrice + ($basePrice * $adminProfitPercent / 100);

    $servicesData[] = [
        'api_service_id' => $service['service'],
        'name' => $service['name'],
        'base_price' => $priceWithProfit,
        'api_price' => $basePrice,
        'min' => $service['min'],
        'max' => $service['max'],
        'status' => 'active',
    ];
}

// Insert / Update services table
foreach ($servicesData as $s) {
    $stmt = $conn->prepare("
        INSERT INTO services (api_service_id, name, base_price,api_price, status, min, max)
        VALUES (?, ?, ?, ?, ?,?, ?)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            base_price = VALUES(base_price),
            api_price = VALUES(api_price),
            min = VALUES(min),
            max = VALUES(max),
            status = VALUES(status)
    ");


    $stmt->bind_param(
        "isddsss",
        $s['api_service_id'],
        $s['name'],
        $s['base_price'],
        $s['api_price'],
        $s['status'],
        $s['min'],
        $s['max']
    );

    $stmt->execute();
}


// Update reseller prices
$resellers = $conn->query("SELECT id FROM user_data WHERE type = 'reseller'")->fetch_all(MYSQLI_ASSOC);


foreach ($resellers as $reseller) {
    foreach ($servicesData as $s) {

        $stmt = $conn->prepare("
            INSERT INTO reseller_prices
            (reseller_id, service_id, price, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                price = price
        ");

        $stmt->bind_param(
            "iid",
            $reseller['id'],
            $s['api_service_id'],
            $s['base_price']
        );

        $stmt->execute();
    }
}



echo "Services updated at " . date('Y-m-d H:i:s') . PHP_EOL;

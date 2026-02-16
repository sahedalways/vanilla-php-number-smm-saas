<?php
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../class/SMMApi.php';
require_once __DIR__ . '/../helpers/currency_helper.php';


$api = new SMMApi();
$services = $api->services();



$conn->query("DELETE FROM services");


$profitRow = $conn->query("SELECT profit_percentage FROM profit_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
$adminProfitPercent = floatval($profitRow['profit_percentage'] ?? 0);

// Prepare services array
$servicesData = [];

foreach ($services as $service) {
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

        // ADD THESE ↓↓↓
        'category' => $service['category'] ?? null,
        'type' => $service['type'] ?? null,
        'cancel' => $service['cancel'] ?? false,
        'refill' => $service['refill'] ?? false,
    ];
}

// Insert / Update services table
foreach ($servicesData as $s) {

    $cancel = !empty($s['cancel']) ? 1 : 0;
    $refill = !empty($s['refill']) ? 1 : 0;

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
        $cancel,
        $refill,
        $s['type']
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

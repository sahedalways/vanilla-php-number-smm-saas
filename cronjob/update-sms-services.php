<?php
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../class/FiveSimApi.php';
require_once __DIR__ . '/../helpers/currency_helper.php';


$api = new FiveSimApi();

$services = $api->getProducts(null, null);



$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DELETE FROM sms_provider_services");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");



$profitRow = $conn->query("SELECT profit_percentage FROM profit_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
$adminProfitPercent = floatval($profitRow['profit_percentage'] ?? 0);

$servicesData = [];

foreach ($services as $country => $countryServices) {
    foreach ($countryServices as $serviceCode => $operators) {
        foreach ($operators as $operator => $info) {

            $usdPrice = $info['cost'] ?? 0;
            $count = $info['count'] ?? 0;
            $rate = $info['rate'] ?? 0;

            $basePrice = usdToNaira($usdPrice);
            $adminProfit = $basePrice * $adminProfitPercent / 100;
            $priceWithProfit = $basePrice + $adminProfit;

            $servicesData[] = [
                'country' => $country,
                'service_code' => $serviceCode,
                'operator' => $operator,
                'provider_cost' => $basePrice,
                'admin_profit' => $adminProfit,
                'base_price' => $priceWithProfit,
                'count' => $count,
                'rate' => $rate
            ];
        }
    }
}

// Insert / Update services table
$stmt = $conn->prepare("
    INSERT INTO sms_provider_services (
        country, service_code, operator,
        provider_cost, admin_profit, base_price,
        count, rate
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($servicesData as $s) {
    $stmt->bind_param(
        "sssdddii",
        $s['country'],
        $s['service_code'],
        $s['operator'],
        $s['provider_cost'],
        $s['admin_profit'],
        $s['base_price'],
        $s['count'],
        $s['rate']
    );
    $stmt->execute();
}

// Update reseller prices
$resellers = $conn->query("SELECT id FROM user_data WHERE type = 'reseller'")->fetch_all(MYSQLI_ASSOC);

foreach ($resellers as $reseller) {
    foreach ($servicesData as $s) {

        $serviceIdRow = $conn->query("
            SELECT id FROM sms_provider_services
            WHERE country = '{$s['country']}'
            AND service_code = '{$s['service_code']}'
            AND operator = '{$s['operator']}'
            ORDER BY id DESC LIMIT 1
        ")->fetch_assoc();

        $serviceId = $serviceIdRow['id'] ?? 0;
        if ($serviceId > 0) {
            $resellerPrice = $s['base_price'];

            $stmt2 = $conn->prepare("
                INSERT INTO reseller_sms_services_prices (reseller_id, service_id, reseller_price)
                VALUES (?, ?, ?)
            ");
            $stmt2->bind_param("iid", $reseller['id'], $serviceId, $resellerPrice);
            $stmt2->execute();
        }
    }
}

echo "Services updated at " . date('Y-m-d H:i:s') . PHP_EOL;

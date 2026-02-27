<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../helpers/currency_helper.php';
require_once __DIR__ . '/../../../../include/config.php';
require_once __DIR__ . '/../../../../class/FiveSimApi.php';

header('Content-Type: application/json');
authOnly();



// CSRF check
$csrf_token = $_GET['csrf_token'] ?? '';
if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

$country = $_GET['country'] ?? 'any';
$service = $_GET['service'] ?? 'any';



$api = new FiveSimApi();
$servicesResponse = $api->getProducts($country, $service);




$profitRow = $conn->query("SELECT profit_percentage FROM profit_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
$adminProfitPercent = floatval($profitRow['profit_percentage'] ?? 0);

if (!($servicesResponse['success'] ?? false)) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch services',
        'details' => $servicesResponse
    ]);
    exit;
}


$services = $servicesResponse['data'] ?? [];


foreach ($services as $countryCode => &$countryData) {
    foreach ($countryData as $operatorName => &$products) {
        if ($operatorName === 'PriceWithProfit') continue;

        foreach ($products as $productId => &$product) {
            $usdPrice = floatval($product['cost'] ?? 0);
            $basePrice = usdToNaira($usdPrice);

            $adminProfit = $basePrice * $adminProfitPercent / 100;
            $resellerProfit = 0;

            $product['PriceWithProfit'] = round($basePrice + $adminProfit + $resellerProfit, 2);
            $product['admin_profit'] = round($adminProfit, 2);
        }


        $operatorTotal = array_sum(array_map(fn($p) => $p['PriceWithProfit'] ?? 0, $products));
        $products['PriceWithProfit'] = round($operatorTotal, 2);
    }
}

echo json_encode([
    'success' => true,
    'status_code' => 200,
    'data' => $services
]);

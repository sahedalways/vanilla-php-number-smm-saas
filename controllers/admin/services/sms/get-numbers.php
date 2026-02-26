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
$operator = $_GET['operator'] ?? 'any';



$api = new FiveSimApi();
$servicesResponse = $api->getProducts($country, $operator);


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

// Get the actual services data
$services = $servicesResponse['data'] ?? [];


foreach ($services as $serviceId => $service) {
    $usdPrice = floatval($service['Price'] ?? 0);
    $basePrice = usdToNaira($usdPrice);

    $adminProfit = $basePrice * $adminProfitPercent / 100;

    $services[$serviceId]['PriceWithProfit'] = $basePrice + $adminProfit + $resellerProfit;
    $services[$serviceId]['admin_profit'] = round($adminProfit, 2);
}



echo json_encode([
    'success' => true,
    'status_code' => 200,
    'data' => $services
]);

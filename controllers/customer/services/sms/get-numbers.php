<?php
require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../helpers/currency_helper.php';
require_once __DIR__ . '/../../../../include/config.php';
require_once __DIR__ . '/../../../../class/FiveSimApi.php';

header('Content-Type: application/json');
authOnly();

$userId = $_SESSION['user_id'] ?? 0;

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


// Fetch admin profit percentage
$profitRow = $conn->query("SELECT profit_percentage FROM profit_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
$adminProfitPercent = floatval($profitRow['profit_percentage'] ?? 0);

// Fetch reseller profit percentage
$resellerRow = $conn->query("
    SELECT r.profit_percentage
    FROM reseller_customers rc
    JOIN reseller_sms_profit_settings r ON rc.reseller_id = r.user_id
    WHERE rc.customer_id = {$userId}
    ORDER BY r.id DESC
    LIMIT 1
")->fetch_assoc();
$resellerProfitPercent = floatval($resellerRow['profit_percentage'] ?? 0);

// Check API response success
if (!($servicesResponse['success'] ?? false)) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch services',
        'details' => $servicesResponse
    ]);
    exit;
}

// Get the actual services data
$servicesData = $servicesResponse['data'] ?? [];

foreach ($servicesData as $countryName => &$countryData) {
    $countryTotal = 0;

    foreach ($countryData as $serviceName => &$serviceProducts) {
        // Skip non-array entries like "PriceWithProfit" if exists
        if (!is_array($serviceProducts)) continue;

        foreach ($serviceProducts as $productId => &$productInfo) {
            // Skip non-array entries like nested "PriceWithProfit" keys
            if (!is_array($productInfo) || !isset($productInfo['cost'])) continue;

            $usdPrice = floatval($productInfo['cost']);
            $basePrice = usdToNaira($usdPrice);

            $adminProfit = $basePrice * $adminProfitPercent / 100;
            $resellerProfit = $resellerProfitPercent ? ($basePrice * $resellerProfitPercent / 100) : 0;

            $productInfo['PriceWithProfit'] = $basePrice + $adminProfit + $resellerProfit;

            $countryTotal += $productInfo['PriceWithProfit'];
        }
    }

    // Optional: store total PriceWithProfit for the country
    $countryData['PriceWithProfit'] = $countryTotal;
}

// Return JSON
echo json_encode([
    'success' => true,
    'status_code' => 200,
    'data' => $servicesData
]);

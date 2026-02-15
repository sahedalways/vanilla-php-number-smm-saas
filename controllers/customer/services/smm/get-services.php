<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../helpers/smm_helper.php';
require_once __DIR__ . '/../../../../helpers/currency_helper.php';

authOnly();

$response = smmAPI('services');
$services = [];



foreach ($response as $service) {
    $usdPrice = $service['rate'];
    $nairaPrice = usdToNaira($usdPrice);


    $services[] = [

        'id' => $service['service'],
        'name' => $service['name'],
        'price' => $nairaPrice,
        'min' => $service['min'],
        'max' => $service['max']

    ];
}

echo json_encode([
    'status' => 'success',
    'services' => $services
]);

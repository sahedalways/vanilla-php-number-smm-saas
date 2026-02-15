<?php

function smmAPI($action = 'services')
{
    // Development: call dummy function directly
    require_once __DIR__ . '/../dummy_smm_api.php';

    return dummySMM($action);

    /*
    ----------------------------
    Production / Real API usage
    ----------------------------
    $url = 'https://pikasmm.com/api/v2';
    $params = [
        'key' => 'YOUR_REAL_API_KEY',
        'action' => $action,
        // other required parameters
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
    */
}

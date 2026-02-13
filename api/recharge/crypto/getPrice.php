<?php

$apiUrl = 'https://www.kucoin.com/_api/currency/prices?base=NGN&targets=&lang=en_US';

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Someting Went Wrong';
} else {
    $data = json_decode($response, true);
    $trxToInrRate = $data['data']['TRX'];
    $usdtToInrRate = $data['data']['USDT'];
    $trxValue = number_format((float)$trxToInrRate, 2, '.', '');
    $usdtValue = number_format((float)$usdtToInrRate, 2, '.', '');

    echo'{"trx":'.$trxValue.',"usdt":'.$usdtValue.'}';
}
curl_close($ch);

?>
<?php

require_once __DIR__ . '/../dummy_smm_api.php';

class SMMAPI
{
    private $apiKey;
    private $useDummy;

    public function __construct($apiKey = '', $useDummy = true)
    {
        $this->apiKey = $apiKey;
        $this->useDummy = $useDummy;
    }

    /**
     * Call API action
     */
    public function call($action = 'services', $params = [])
    {
        if ($this->useDummy) {
            return dummySMM($action, $params);
        }

        // // Real API
        // $url = 'https://pikasmm.com/api/v2';
        // $params['key'] = $this->apiKey;
        // $params['action'] = $action;

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $response = curl_exec($ch);
        // curl_close($ch);

        // return json_decode($response, true);
    }

    // Convenience methods
    public function services()
    {
        return $this->call('services');
    }

    public function addOrder($serviceId, $quantity)
    {
        return $this->call('add', ['service' => $serviceId, 'quantity' => $quantity]);
    }

    public function status($orderId)
    {
        return $this->call('status', ['order' => $orderId]);
    }

    public function balance()
    {
        return $this->call('balance');
    }
}

// // -------------------
// // Example usage
// // -------------------
// $api = new SMMAPI('DUMMY_KEY', true);

// // Get services
// $services = $api->services();

// // Place order
// $order = $api->addOrder(1, 100);

// // Check status
// $status = $api->status($order['order'] ?? 0);

// // Check balance
// $balance = $api->balance();

// print_r($order);

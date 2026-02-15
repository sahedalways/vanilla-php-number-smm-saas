<?php
class ApiService
{

    private $api_url;
    private $api_key;

    public function __construct($api_url = null, $api_key = null)
    {
        // Development purpose default URL to dummy API
        $this->api_url = $api_url ?? "http://localhost/dummy_api.php";
        $this->api_key = $api_key ?? "DUMMY_KEY";
    }

    public function call($action, $params = [])
    {
        $params['action'] = $action;
        $params['key'] = $this->api_key;

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function services()
    {
        return $this->call('services');
    }

    public function addOrder($service_id, $link, $quantity)
    {
        return $this->call('add', [
            "service" => $service_id,
            "link" => $link,
            "quantity" => $quantity
        ]);
    }

    public function status($order_id)
    {
        return $this->call('status', ["order" => $order_id]);
    }

    public function balance()
    {
        return $this->call('balance');
    }
}

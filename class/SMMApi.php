<?php

// for production use, set your API key in the $api_key variable below or set it as an environment variable.
// class SMMApi
// {
//     /** API URL */
//     public $api_url = 'https://pikasmm.com/api/v2';

//     /** Your API key */
//     public $api_key = '';

//     /** Add order */
//     public function order($data)
//     {
//         $post = array_merge(['key' => $this->api_key, 'action' => 'add'], $data);
//         return json_decode((string)$this->connect($post));
//     }

//     /** Get order status  */
//     public function status($order_id)
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'status',
//                 'order' => $order_id
//             ])
//         );
//     }

//     /** Get orders status */
//     public function multiStatus($order_ids)
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'status',
//                 'orders' => implode(",", (array)$order_ids)
//             ])
//         );
//     }

//     /** Get services */
//     public function services()
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'services',
//             ])
//         );
//     }

//     /** Refill order */
//     public function refill(int $orderId)
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'refill',
//                 'order' => $orderId,
//             ])
//         );
//     }

//     /** Refill orders */
//     public function multiRefill(array $orderIds)
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'refill',
//                 'orders' => implode(',', $orderIds),
//             ]),
//             true,
//         );
//     }

//     /** Get refill status */
//     public function refillStatus(int $refillId)
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'refill_status',
//                 'refill' => $refillId,
//             ])
//         );
//     }

//     /** Get refill statuses */
//     public function multiRefillStatus(array $refillIds)
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'refill_status',
//                 'refills' => implode(',', $refillIds),
//             ]),
//             true,
//         );
//     }

//     /** Cancel orders */
//     public function cancel(array $orderIds)
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'cancel',
//                 'orders' => implode(',', $orderIds),
//             ]),
//             true,
//         );
//     }

//     /** Get balance */
//     public function balance()
//     {
//         return json_decode(
//             $this->connect([
//                 'key' => $this->api_key,
//                 'action' => 'balance',
//             ])
//         );
//     }

//     private function connect($post)
//     {
//         $_post = [];
//         if (is_array($post)) {
//             foreach ($post as $name => $value) {
//                 $_post[] = $name . '=' . urlencode($value);
//             }
//         }

//         $ch = curl_init($this->api_url);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//         curl_setopt($ch, CURLOPT_POST, 1);
//         curl_setopt($ch, CURLOPT_HEADER, 0);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

//         if (is_array($post)) {
//             curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
//         }
//         curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
//         $result = curl_exec($ch);
//         if (curl_errno($ch) != 0 && empty($result)) {
//             $result = false;
//         }
//         curl_close($ch);
//         return $result;
//     }
// }

// // Examples

// $api = new SMMApi();

// $services = $api->services(); # Return all services

// $balance = $api->balance(); # Return user balance

// // Add order

// $order = $api->order(['service' => 1, 'link' => 'http://example.com/test', 'quantity' => 100, 'runs' => 2, 'interval' => 5]); # Default

// $order = $api->order(['service' => 1, 'link' => 'http://example.com/test', 'comments' => "good pic\ngreat photo\n:)\n;)"]); # Custom Comments

// $order = $api->order(['service' => 1, 'link' => 'http://example.com/test']); # Package

// $order = $api->order(['service' => 1, 'link' => 'http://example.com/test', 'quantity' => 100, 'username' => "test"]); # Comment Likes


// $status = $api->status($order->order); # Return status, charge, remains, start count, currency

// $statuses = $api->multiStatus([1, 2, 3]); # Return orders status, charge, remains, start count, currency
// $refill = (array) $api->multiRefill([1, 2]);
// $refillIds = array_column($refill, 'refill');
// if ($refillIds) {
//     $refillStatuses = $api->multiRefillStatus($refillIds);
// }




// for development and testing purposes, this dummy class simulates the SMM API responses without making actual HTTP requests. You can replace the methods with real API calls when you're ready to go live.
class SMMApi
{

    /** Add order */
    public function order($data)
    {
        // dummy order id generate
        return (object)[
            'order' => rand(100000, 999999),
            'status' => 'success'
        ];
    }

    /** Get order status */
    public function status($order_id)
    {
        return (object)[
            'order' => $order_id,
            'charge' => rand(10, 100) / 10,
            'start_count' => rand(100, 5000),
            'status' => 'In progress',
            'remains' => rand(0, 500)
        ];
    }

    /** Get multiple order status */
    public function multiStatus($order_ids)
    {
        $result = [];

        foreach ($order_ids as $id) {
            $result[$id] = [
                'charge' => rand(10, 100) / 10,
                'start_count' => rand(100, 5000),
                'status' => 'Completed',
                'remains' => 0
            ];
        }

        return $result;
    }

    /** Get services */
    public function services()
    {
        return [

            [
                "service" => 1,
                "name" => "Instagram Followers [Real]",
                "type" => "Default",
                "category" => "Instagram Followers",
                "rate" => "0.90",
                "min" => "50",
                "max" => "10000",
                "refill" => true,
                "cancel" => true,
                "order_params" => [
                    "key" => "",
                    "action" => "add",
                    "service" => 1,
                    "link" => "",
                    "quantity" => null,
                    "runs" => null,
                    "interval" => null
                ]
            ],

            [
                "service" => 2,
                "name" => "Instagram Comments [Custom]",
                "type" => "Custom Comments",
                "category" => "Instagram Comments",
                "rate" => "8",
                "min" => "10",
                "max" => "1500",
                "refill" => false,
                "cancel" => true,
                "order_params" => [
                    "key" => "",
                    "action" => "add",
                    "service" => 2,
                    "link" => "",
                    "comments" => ""
                ]
            ],

            [
                "service" => 3,
                "name" => "YouTube Likes",
                "type" => "Default",
                "category" => "YouTube",
                "rate" => "1.5",
                "min" => "20",
                "max" => "5000",
                "refill" => true,
                "cancel" => false,
                "order_params" => [
                    "key" => "",
                    "action" => "add",
                    "service" => 3,
                    "link" => "",
                    "quantity" => null
                ]
            ],

            [
                "service" => 4,
                "name" => "TikTok Views",
                "type" => "Default",
                "category" => "TikTok",
                "rate" => "0.5",
                "min" => "100",
                "max" => "100000",
                "refill" => false,
                "cancel" => false,
                "order_params" => [
                    "key" => "",
                    "action" => "add",
                    "service" => 4,
                    "link" => "",
                    "quantity" => null
                ]
            ],

            [
                "service" => 5,
                "name" => "Instagram Comments Package",
                "type" => "Custom Comments Package",
                "category" => "Instagram Comments",
                "rate" => "10",
                "min" => "10",
                "max" => "2000",
                "refill" => false,
                "cancel" => true,
                "order_params" => [
                    "key" => "",
                    "action" => "add",
                    "service" => 5,
                    "link" => "",
                    "comments" => ""
                ]
            ],

            [
                "service" => 6,
                "name" => "Comment Likes",
                "type" => "Comment Likes",
                "category" => "Instagram / YouTube Comments",
                "rate" => "0.20",
                "min" => "1",
                "max" => "1000",
                "refill" => false,
                "cancel" => false,
                "order_params" => [
                    "key" => "",
                    "action" => "add",
                    "service" => 6,
                    "link" => "",
                    "quantity" => null,
                    "username" => ""
                ]
            ],

        ];
    }


    /** Refill order */
    public function refill(int $orderId)
    {
        return (object)[
            'refill' => rand(1000, 9999),
            'status' => 'Refill started'
        ];
    }

    /** Multiple refill */
    public function multiRefill(array $orderIds)
    {
        $result = [];

        foreach ($orderIds as $id) {
            $result[$id] = [
                'refill' => rand(1000, 9999),
                'status' => 'Refill started'
            ];
        }

        return $result;
    }

    /** Refill status */
    public function refillStatus(int $refillId)
    {
        return (object)[
            'refill' => $refillId,
            'status' => 'Completed'
        ];
    }

    /** Multiple refill status */
    public function multiRefillStatus(array $refillIds)
    {
        $result = [];

        foreach ($refillIds as $id) {
            $result[$id] = [
                'status' => 'Completed'
            ];
        }

        return $result;
    }

    /** Cancel order */
    public function cancel(array $orderIds)
    {
        $result = [];

        foreach ($orderIds as $id) {
            $result[$id] = [
                'status' => 'Cancelled'
            ];
        }

        return $result;
    }

    /** Get balance */
    public function balance()
    {
        return (object)[
            'balance' => 250.75,
            'currency' => 'USD'
        ];
    }
}

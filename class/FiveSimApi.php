<?php

class FiveSimApi
{


    // private $apiKey;
    // private $baseUrl = "https://5sim.net/v1/user";

    // public function __construct($apiKey)
    // {
    //     $this->apiKey = $apiKey;
    // }

    // private function request($endpoint, $method = "GET")
    // {

    //     $ch = curl_init();

    //     curl_setopt_array($ch, [
    //         CURLOPT_URL => $this->baseUrl . $endpoint,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_HTTPHEADER => [
    //             "Authorization: Bearer " . $this->apiKey,
    //             "Accept: application/json"
    //         ]
    //     ]);

    //     if ($method == "POST") {
    //         curl_setopt($ch, CURLOPT_POST, true);
    //     }

    //     $response = curl_exec($ch);

    //     curl_close($ch);

    //     return json_decode($response, true);
    // }

    // // Get balance
    // public function getBalance()
    // {
    //     return $this->request("/profile");
    // }

    // // Get available numbers
    // public function getProducts($country, $service)
    // {
    //     return $this->request("/prices/$country/$service");
    // }

    // // Buy number
    // public function buyNumber($country, $service)
    // {
    //     return $this->request("/buy/activation/$country/any/$service", "GET");
    // }

    // // Get SMS
    // public function getSMS($orderId)
    // {
    //     return $this->request("/check/$orderId");
    // }

    // // Finish order
    // public function finishOrder($orderId)
    // {
    //     return $this->request("/finish/$orderId");
    // }

    // // Cancel order
    // public function cancelOrder($orderId)
    // {
    //     return $this->request("/cancel/$orderId");
    // }



    private function request($endpoint, $method = "GET")
    {
        if (strpos($endpoint, '/prices/') !== false) {

            return [

                'bangladesh' => [

                    'whatsapp' => [
                        'any' => [
                            'cost' => 0.20,
                            'count' => 25,
                            'rate' => 0
                        ],
                        'virtual21' => [
                            'cost' => 0.22,
                            'count' => 15,
                            'rate' => 0
                        ]
                    ],

                    'telegram' => [
                        'any' => [
                            'cost' => 0.15,
                            'count' => 18,
                            'rate' => 0
                        ],
                        'virtual21' => [
                            'cost' => 0.18,
                            'count' => 12,
                            'rate' => 0
                        ]
                    ],

                ],

                'usa' => [

                    'facebook' => [
                        'vodafone' => [
                            'cost' => 4,
                            'count' => 1260,
                            'rate' => 99.99
                        ],
                        'virtual60' => [
                            'cost' => 4,
                            'count' => 935,
                            'rate' => 99.99
                        ],
                        'virtual52' => [
                            'cost' => 4,
                            'count' => 0,
                            'rate' => 99.99
                        ]
                    ],

                    'google' => [
                        'any' => [
                            'cost' => 0.35,
                            'count' => 20,
                            'rate' => 0
                        ],
                        'virtual21' => [
                            'cost' => 0.38,
                            'count' => 15,
                            'rate' => 0
                        ]
                    ],

                ],

                'england' => [

                    'facebook' => [
                        'vodafone' => [
                            'cost' => 4,
                            'count' => 1260,
                            'rate' => 99.99
                        ],
                        'virtual60' => [
                            'cost' => 4,
                            'count' => 935,
                            'rate' => 99.99
                        ],
                        'virtual52' => [
                            'cost' => 4,
                            'count' => 0,
                            'rate' => 99.99
                        ]
                    ],

                    'telegram' => [
                        'virtual60' => [
                            'cost' => 0.28,
                            'count' => 40,
                            'rate' => 0
                        ]
                    ]

                ]

            ];
        }



        if (strpos($endpoint, '/profile') !== false) {
            return [
                'balance' => 50.00,
                'currency' => 'USD'
            ];
        }

        if (strpos($endpoint, '/buy/activation') !== false) {
            return [
                'id' => rand(100000, 999999),
                'phone' => '+88017' . rand(10000000, 99999999),
                'price' => 0.20,
                'status' => 'PENDING'
            ];
        }

        if (strpos($endpoint, '/check/') !== false) {

            // simulate SMS after random
            if (rand(0, 1)) {
                return [
                    'status' => 'FINISHED',
                    'sms' => [
                        [
                            'code' => rand(1000, 9999)
                        ]
                    ]
                ];
            } else {
                return [
                    'status' => 'PENDING'
                ];
            }
        }

        if (strpos($endpoint, '/finish/') !== false) {
            return [
                'status' => 'FINISHED'
            ];
        }

        if (strpos($endpoint, '/cancel/') !== false) {
            return [
                'status' => 'CANCELLED'
            ];
        }

        return ['status' => 'TEST_MODE'];
    }





    public function getBalance()
    {
        return $this->request("/profile");
    }

    public function getProducts($country, $service)
    {
        return $this->request("/prices/$country/$service");
    }

    public function buyNumber($country, $operator, $service)
    {
        return $this->request("/buy/activation/$country/$operator/$service");
    }

    public function getSMS($orderId)
    {
        return $this->request("/check/$orderId");
    }

    public function finishOrder($orderId)
    {
        return $this->request("/finish/$orderId");
    }

    public function cancelOrder($orderId)
    {
        return $this->request("/cancel/$orderId");
    }
}

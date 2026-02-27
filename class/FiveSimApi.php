<?php

loadEnv(__DIR__ . '/../.env');
$apiKey = getenv('FIVESIM_API_KEY');

class FiveSimApi
{
    private $apiKey;
    private $baseUrl = "https://5sim.net/v1/user";
    private $buyUrl = "https://5sim.net/v1/user/buy/activation";
    private $getProductBaseUrl = "https://5sim.net/v1/guest/prices";
    private $checkOrderOtpUrl = "https://5sim.net/v1/user/check";

    public function __construct($apiKey = null)
    {
        $this->apiKey = 'eyJhbGciOiJSUzUxMiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE4MDM2NjU4ODEsImlhdCI6MTc3MjEyOTg4MSwicmF5IjoiODdkMWQ2YTQxOGVjNTUwMjcyMjE4YmMxMTBhODc1YTkiLCJzdWIiOjM4MTIzMDB9.vHMRukSCVBt74wXe1AH-jZsCweZnAtaA49ord0_RD8z7kPGwI_fZUoMFVW7npnpRy73_YD5GswqbLWOwsc_16tYCd0rMWV-5R9-MaImdi88w23qD5L5ML8sgam1IFlmjc5xjWbzJlfW649xkoDYwvZtEeqzUhPXTD2OJ3-2h9MbKYWcJISrVufZCGRb3nqMHyCdcXjQfFKurSns3KCOAWHidvttqTkvV-2t0pxWEbK0Un-mTn_VNBVHy_YLEIGgCHwwy_2kxj1U7PyZGJM8MPZei1LbHWMmAoUwhwSv46RW3aXLDGvE7KJLitmJmLs1Z1wkxAqqaaGnc3XQWT1dPGA';
    }

    private function getProductRequest($endpoint, $method = "GET")
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->getProductBaseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->apiKey,
                "Accept: application/json"
            ]
        ]);

        if ($method === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'type' => 'curl_error',
                'message' => $error
            ];
        }


        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            return [
                'success' => false,
                'type' => 'http_error',
                'status_code' => $httpCode,
                'response' => $data
            ];
        }

        return [
            'success' => true,
            'status_code' => $httpCode,
            'data' => $data
        ];
    }



    private function getBalanceRequest($endpoint, $method = "GET")
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->apiKey,
                "Accept: application/json"
            ]
        ]);

        if ($method === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'type' => 'curl_error',
                'message' => $error
            ];
        }


        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            return [
                'success' => false,
                'type' => 'http_error',
                'status_code' => $httpCode,
                'response' => $data
            ];
        }

        return [
            'success' => true,
            'status_code' => $httpCode,
            'data' => $data
        ];
    }


    private function buyRequest($endpoint, $method = "GET")
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->buyUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FAILONERROR => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->apiKey,
                "Accept: application/json"
            ]
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'type' => 'curl_error',
                'message' => $error
            ];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        $decoded = json_decode($response, true);


        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            $decoded = null;
        }

        if ($httpCode >= 400) {
            return [
                'success' => false,
                'type' => 'http_error',
                'status_code' => $httpCode,
                'response' => $decoded,
                'raw' => trim($response)
            ];
        }

        return [
            'success' => true,
            'status_code' => $httpCode,
            'data' => $decoded,
            'raw' => trim($response)
        ];
    }


    private function checkOrderRequest($endpoint, $method = "GET")
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->checkOrderOtpUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->apiKey,
                "Accept: application/json"
            ]
        ]);

        if ($method === "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            return [
                'success' => false,
                'type' => 'curl_error',
                'message' => $error
            ];
        }


        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        $decoded = json_decode($response, true);


        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            $decoded = null;
        }

        if ($httpCode >= 400) {
            return [
                'success' => false,
                'type' => 'http_error',
                'status_code' => $httpCode,
                'response' => $decoded,
                'raw' => trim($response)
            ];
        }

        return [
            'success' => true,
            'status_code' => $httpCode,
            'data' => $decoded,
            'raw' => trim($response)
        ];
    }



    // Get balance
    public function getBalance()
    {
        return $this->getBalanceRequest("/profile");
    }

    // Get available numbers
    public function getProducts($country, $service)
    {
        $endpoint = "?country=" . urlencode($country) . "&product=" . urlencode($service);
        return $this->getProductRequest($endpoint);
    }

    // Buy number
    public function buyNumber($country, $operator, $service)
    {
        return $this->buyRequest("/$country/$operator/$service", "GET");
    }

    // // Get SMS
    public function getSMS($orderId)
    {
        return $this->checkOrderRequest("/$orderId");
    }

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



    // private function request($endpoint, $method = "GET")
    // {
    //     if (strpos($endpoint, '/prices/') !== false) {

    //         return [

    //             'bangladesh' => [

    //                 'whatsapp' => [
    //                     'any' => [
    //                         'cost' => 0.20,
    //                         'count' => 25,
    //                         'rate' => 0
    //                     ],
    //                     'virtual21' => [
    //                         'cost' => 0.22,
    //                         'count' => 15,
    //                         'rate' => 0
    //                     ]
    //                 ],

    //                 'telegram' => [
    //                     'any' => [
    //                         'cost' => 0.15,
    //                         'count' => 18,
    //                         'rate' => 0
    //                     ],
    //                     'virtual21' => [
    //                         'cost' => 0.18,
    //                         'count' => 12,
    //                         'rate' => 0
    //                     ]
    //                 ],

    //             ],

    //             'usa' => [

    //                 'facebook' => [
    //                     'vodafone' => [
    //                         'cost' => 4,
    //                         'count' => 1260,
    //                         'rate' => 99.99
    //                     ],
    //                     'virtual60' => [
    //                         'cost' => 4,
    //                         'count' => 935,
    //                         'rate' => 99.99
    //                     ],
    //                     'virtual52' => [
    //                         'cost' => 4,
    //                         'count' => 0,
    //                         'rate' => 99.99
    //                     ]
    //                 ],

    //                 'google' => [
    //                     'any' => [
    //                         'cost' => 0.35,
    //                         'count' => 20,
    //                         'rate' => 0
    //                     ],
    //                     'virtual21' => [
    //                         'cost' => 0.38,
    //                         'count' => 15,
    //                         'rate' => 0
    //                     ]
    //                 ],

    //             ],

    //             'england' => [

    //                 'facebook' => [
    //                     'vodafone' => [
    //                         'cost' => 4,
    //                         'count' => 1260,
    //                         'rate' => 99.99
    //                     ],
    //                     'virtual60' => [
    //                         'cost' => 4,
    //                         'count' => 935,
    //                         'rate' => 99.99
    //                     ],
    //                     'virtual52' => [
    //                         'cost' => 4,
    //                         'count' => 0,
    //                         'rate' => 99.99
    //                     ]
    //                 ],

    //                 'telegram' => [
    //                     'virtual60' => [
    //                         'cost' => 0.28,
    //                         'count' => 40,
    //                         'rate' => 0
    //                     ]
    //                 ]

    //             ]

    //         ];
    //     }



    //     if (strpos($endpoint, '/profile') !== false) {
    //         return [
    //             'balance' => 50.00,
    //             'currency' => 'USD'
    //         ];
    //     }

    //     if (strpos($endpoint, '/buy/activation') !== false) {
    //         return [
    //             'id' => rand(100000, 999999),
    //             'phone' => '+88017' . rand(10000000, 99999999),
    //             'price' => 0.20,
    //             'status' => 'PENDING'
    //         ];
    //     }

    //     if (strpos($endpoint, '/check/') !== false) {

    //         // simulate SMS after random
    //         if (rand(0, 1)) {
    //             return [
    //                 'status' => 'FINISHED',
    //                 'sms' => [
    //                     [
    //                         'code' => rand(1000, 9999)
    //                     ]
    //                 ]
    //             ];
    //         } else {
    //             return [
    //                 'status' => 'PENDING'
    //             ];
    //         }
    //     }

    //     if (strpos($endpoint, '/finish/') !== false) {
    //         return [
    //             'status' => 'FINISHED'
    //         ];
    //     }

    //     if (strpos($endpoint, '/cancel/') !== false) {
    //         return [
    //             'status' => 'CANCELLED'
    //         ];
    //     }

    //     return ['status' => 'TEST_MODE'];
    // }





    // public function getBalance()
    // {
    //     return $this->request("/profile");
    // }

    // public function getProducts($country, $service)
    // {
    //     return $this->request("/prices/$country/$service");
    // }

    // public function buyNumber($country, $operator, $service)
    // {
    //     return $this->request("/buy/activation/$country/$operator/$service");
    // }

    // public function getSMS($orderId)
    // {
    //     return $this->request("/check/$orderId");
    // }

    // public function finishOrder($orderId)
    // {
    //     return $this->request("/finish/$orderId");
    // }

    // public function cancelOrder($orderId)
    // {
    //     return $this->request("/cancel/$orderId");
    // }
}

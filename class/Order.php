<?php
require_once 'ApiService.php';
require_once 'Wallet.php';

class Order
{

    public static function create($user, $service_id, $quantity, $conn)
    {

        $api = new ApiService();
        $service = ["rate" => 1];
        $cost = $service['rate'] * $quantity;

        if ($user['wallet'] < $cost) {
            return ["error" => "Insufficient balance"];
        }

        // Deduct wallet
        Wallet::deduct($user['id'], $cost, $conn);

        // Call API
        $response = $api->addOrder($service_id, "http://example.com", $quantity);
        $api_order_id = $response['order'] ?? rand(10000, 99999);

        $reseller_profit = $cost * 0.5;


        if ($user['role'] == 'customer' && $user['parent_id']) {
            Wallet::add($user['parent_id'], $reseller_profit, $conn);
        }

        // Save order
        $stmt = $conn->prepare("INSERT INTO orders
            (user_id, service_id, api_order_id, cost, reseller_profit, status)
            VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $user['id'],
            $service_id,
            $api_order_id,
            $cost,
            $reseller_profit,
            "processing"
        ]);

        return ["success" => true, "order_id" => $api_order_id];
    }
}

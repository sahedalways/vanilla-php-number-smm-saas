<?php
function dummySMM($action = 'services', $data = [])
{
    // List of services
    if ($action === 'services') {
        return [
            ["service" => 1, "name" => "Followers", "rate" => 0.9, "min" => 50, "max" => 10000],
            ["service" => 2, "name" => "Comments", "rate" => 8, "min" => 10, "max" => 1500]
        ];
    }

    // Add order
    if ($action === 'add') {
        $serviceId = intval($data['service'] ?? 0);
        $quantity = intval($data['quantity'] ?? 0);

        if ($serviceId <= 0 || $quantity <= 0) {
            return [
                "error" => "Invalid service ID or quantity."
            ];
        }

        $orderId = rand(10000, 99999);
        $charge = round(rand(1, 10) / 10, 2);

        return [
            "order" => $orderId,
            "service" => $serviceId,
            "quantity" => $quantity,
            "charge" => $charge,
            "status" => "processing",
            "message" => "Order has been successfully created."
        ];
    }

    // Check order status
    if ($action === 'status') {
        return [
            "charge" => round(rand(1, 10) / 10, 2),
            "start_count" => rand(50, 5000),
            "status" => "In Progress",
            "remains" => rand(0, 100)
        ];
    }

    // Balance check
    if ($action === 'balance') {
        return [
            "balance" => 100.0,
            "currency" => "USD"
        ];
    }

    return [
        "error" => true,
        "message" => "Unknown action."
    ];
}

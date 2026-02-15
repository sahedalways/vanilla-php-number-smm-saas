<?php
function dummySMM($action = 'services')
{
    if ($action === 'services') {
        return [
            ["service" => 1, "name" => "Followers", "rate" => 0.9, "min" => 50, "max" => 10000],
            ["service" => 2, "name" => "Comments", "rate" => 8, "min" => 10, "max" => 1500]
        ];
    }

    if ($action === 'add') {
        return ["order" => rand(10000, 99999)];
    }

    if ($action === 'status') {
        return [
            "charge" => rand(1, 10) / 10,
            "start_count" => rand(50, 5000),
            "status" => "In progress",
            "remains" => rand(0, 100)
        ];
    }

    if ($action === 'balance') {
        return [
            "balance" => 100.0,
            "currency" => "USD"
        ];
    }

    return [];
}

<?php

require_once __DIR__ . "/include/config.php";

$current_time_in_ist = date('Y-m-d H:i:s');

$reference = $_GET['reference'] ?? $_GET['trxref'] ?? null;

if (!$reference) {
    die("No reference supplied");
}


$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . $reference,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);

if ($response === false) {
    die(curl_error($curl));
}

curl_close($curl);

$res = json_decode($response, true);

if (!$res) {
    die("Invalid JSON from Paystack");
}

if (
    isset($res['status']) &&
    $res['status'] == true &&
    isset($res['data']['status']) &&
    $res['data']['status'] === 'success'
) {

    $amountPaid = $res['data']['amount'] / 100;
    $email = $res['data']['customer']['email'];
    $txn_id = $res['data']['reference'];

    // 🔎 get user
    $sql = mysqli_query($conn, "SELECT * FROM user_data WHERE email='$email'");
    $user_data = mysqli_fetch_assoc($sql);

    if (!$user_data) {
        die("User not found");
    }

    $user_id = $user_data['id'];


    $sql1 = mysqli_query($conn, "SELECT * FROM upi_recharge WHERE txn_id='$txn_id'");
    if (mysqli_num_rows($sql1) == 0) {

        $sql2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$user_id'");
        $user_wallet = mysqli_fetch_assoc($sql2);

        if (!$user_wallet) {

            mysqli_query($conn, "INSERT INTO user_wallet (user_id, balance, total_recharge, total_otp)
                         VALUES ('$user_id', 0, 0, 0)");


            $sql2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$user_id'");
            $user_wallet = mysqli_fetch_assoc($sql2);
        }


        $add_balance = $user_wallet['balance'] + $amountPaid;
        $add_total_rc = $user_wallet['total_recharge'] + $amountPaid;


        mysqli_query($conn, "INSERT INTO upi_recharge
            (user_id, amount, txn_id, recharge_time, status)
            VALUES
            ('$user_id', '$amountPaid', '$txn_id', '$current_time_in_ist', '1')");

        $stmt = $conn->prepare("UPDATE user_wallet SET balance=?, total_recharge=? WHERE user_id=?");
        $stmt->bind_param("ddi", $add_balance, $add_total_rc, $user_id);
        $stmt->execute();

        mysqli_query($conn, "INSERT INTO user_transaction
            (user_id, amount, date, type, txn_id, status)
            VALUES
            ('$user_id', '$amountPaid', '$current_time_in_ist', 'Paystack Recharge', '$txn_id', '1')");
    }

    session_start();
    $_SESSION['success_msg'] = "Payment of ₦$amountPaid was successful";
    header('Location: dashboard');
    exit;
}

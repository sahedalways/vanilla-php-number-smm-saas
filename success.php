<?php 
require_once __DIR__ . "/include/config.php";

$current_time_in_ist = date('Y-m-d H:i:s');  

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'cancelled') {
        header('Location: index');
        exit();
   } elseif ($_GET['status'] == 'successful' || $_GET['status'] == 'completed' && isset($_GET['transaction_id'])) {
    $txn_id = $_GET['transaction_id'];
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/{$txn_id}/verify",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " . SECRET_KEY
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $res = json_decode($response);

        if ($res->status == 'success') {
            $amountPaid = $res->data->charged_amount;
            $email = $res->data->customer->email;                
            $amount = number_format($amountPaid, 2);
            $amount = str_replace(',', '', $amount);
            $amount = (float)$amount;
            $sql = mysqli_query($conn, "SELECT * FROM user_data WHERE email='$email'");
            $user_data = mysqli_fetch_assoc($sql);

                if ($user_data) {
                    $sql1 = mysqli_query($conn, "SELECT * FROM upi_recharge WHERE txn_id='$txn_id'");
                    if (mysqli_num_rows($sql1) == 0) {                        
                        $user_id = $user_data['id'];
                        $sql2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$user_id'");
                        $user_wallet = mysqli_fetch_assoc($sql2);

                        if ($user_wallet) {
                            $user_balance = $user_wallet['balance'];
                            $total_recharge = $user_wallet['total_recharge'];
                            $add_balance = $user_balance + $amount;
                            $add_total_rc = $total_recharge + $amount;

                            mysqli_query($conn, "INSERT INTO upi_recharge (user_id, amount, txn_id, recharge_time, status) VALUES ('$user_id', '$amount', '$txn_id', '$current_time_in_ist', '1')");
                            $stmt = $conn->prepare("UPDATE user_wallet SET balance=?, total_recharge=? WHERE user_id=?");
                            $stmt->bind_param("ddi", $add_balance, $add_total_rc, $user_id);
                            $stmt->execute();                     
                            mysqli_query($conn, "INSERT INTO user_transaction (user_id, amount, date, type, txn_id, status) VALUES ('$user_id', '$amount', '$current_time_in_ist', 'Rave Recharge', '$txn_id', '1')");   
                            header('Location: dashboard');
                            exit();
                        }
                    }
                }            
        }
        header('Location: login');
        exit();
    }
} else {
    header('Location: login');
    exit();
}
?>

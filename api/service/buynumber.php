<?php
include __DIR__ . '/../../include/config.php';
function generateRandomString($length = 20)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
}
function custom_price($user_id, $service_id, $server_id, $price, $conn)
{
    $sql = mysqli_query($conn, "SELECT * FROM custom_price WHERE user_id='" . $user_id . "' AND service_id='" . $service_id . "' AND server_id='" . $server_id . "'");
    if (mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        if ($data['type'] == "flat") {
            return $data['discount'];
        } elseif ($data['type'] == "percent") {
            $percent = $data['discount'];
            $final_percent = ($percent / 100) * $price;
            $sub = $price - $final_percent;
            return $sub;
        }
    } else {
        return $price;
    }
}
if (!isset($_GET['server']) || $_GET['server'] == "") {
    echo '{"status":"500","message":"Invalid Server"}';
} elseif (!isset($_GET['service']) || $_GET['service'] == "") {
    echo '{"status":"500","message":"Invalid Service"}';
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
    echo '{"status":"500","message":"Token Blank"}';
} else {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    // $find_token = new radiumsahil();
    $check_token = check_token($token, $conn);
    // $find_token->closeConnection();
    if ($check_token === false) {
        echo '{"status":"500","message":"Token Expired Please Logout And Login Again"}';
    } else {
        $server = $_GET['server'];
        $service = $_GET['service'];
        $sql = mysqli_query($conn, "SELECT * FROM service WHERE service_id='" . $service . "' AND server_id='" . $server . "'");
        if (mysqli_num_rows($sql) != 1) {
            echo '{"status":"500","message":"Service Not Found"}';
        } else {
            $user_id = $check_token;
            $sql2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $user_id . "'");
            $service_data = mysqli_fetch_assoc($sql);
            $user_data = mysqli_fetch_assoc($sql2);
            $user_balance = $user_data['balance'];
            // $service_price = $service_data['service_price'];
            $service_price = custom_price($check_token, $service_data['service_id'], $service_data['server_id'], $service_data['service_price'], $conn);

            if ($user_balance >= $service_price) {
                $server_id = $service_data['server_id'];
                $sql3 = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $server_id . "'");
                $server_data = mysqli_fetch_assoc($sql3);
                $api_id = $server_data['api_id'];
                $sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='" . $api_id . "'");
                $api_data = mysqli_fetch_assoc($sql4);
                $api_key = $api_data['api_key'];
                $api_url = $api_data['api_url'];
                $server_code = $server_data['server_code'];
                $url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getNumber&service={$service}&country={$server_code}";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                $response = explode(':', $result);

                if ($response[0] != "ACCESS_NUMBER") {
                    echo '{"status":"500","message":"Error : ' . $response[0] . '"}';
                } else {

                    $random_order = generateRandomString();
                    mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
                    try {
                        $sql200 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $user_id . "'");
                        $user_data = mysqli_fetch_assoc($sql200);
                        $user_balance = $user_data['balance'];
                        $current_time_in_ist = date('Y-m-d H:i:s');
                        $cut_balance = $user_balance - $service_price;
                        $user_otp = $user_data['total_otp'];
                        $add_otp = $user_otp + 1;
                        $service_name = $service_data['service_name'];
                        if ($user_balance >= $service_price) {
                            $sql5 = mysqli_query($conn, "UPDATE user_wallet SET balance='$cut_balance', total_otp='$add_otp' WHERE user_id='$user_id'");
                            // mysqli_query($conn,"INSERT INTO `action` (`user_id`, `new_balance`, `old_balance`, `total_otp`, `order_id`, `type`, `date`) VALUES ('".$user_id."', '".$cut_balance."', '".$user_balance ."', '". $add_otp."', '".$random_order."', 'BUY_NUMBER','".$current_time_in_ist."')");
                            if ($sql5) {
                                $sql6 = mysqli_query($conn, "INSERT INTO active_number(user_id, number_id, number, server_id, service_id, order_id, buy_time, status, sms_text, service_price, service_name, active_status) VALUES ('" . $user_id . "','" . $response[1] . "','" . $response[2] . "','" . $server . "','" . $service . "','" . $random_order . "','" . $current_time_in_ist . "','2','','" . $service_price . "','" . $service_name . "','2')");
                                echo '{"status":"200","message":"Number Purchased","res":"ACCESS_NUMBER:' . $random_order . ':' . $response[2] . '"}';
                            } else {
                                echo '{"status":"500","message":"Sql Error #1"}';
                            }
                        } else {
                            echo '{"status":"500","message":"Insufficient Balance"}';

                        }
                        mysqli_commit($conn);
                    } catch (Exception $e) {
                        mysqli_rollback($conn);
                        echo '{"status":"500","message":"' . $e->getMessage() . '"}';
                    }
                }
            } else {
                echo '{"status":"500","message":"Insufficient Balance"}';
            }
        }
    }
}
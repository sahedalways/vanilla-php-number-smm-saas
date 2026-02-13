<?php
include_once __DIR__ . '/../../include/config.php';

if (!isset($_GET['order_id']) || $_GET['order_id'] == "") {
    echo '{"status":"500","message":"Invalid Order id"}';
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
        $user_id = $check_token;
        $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
        $sql1 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id' and active_status='2'");
        if (mysqli_num_rows($sql1) == 1) {
            $active_data = mysqli_fetch_assoc($sql1);

            function check_wait($active_data, $conn)
            {
                if ($active_data['sms_text'] == "") {
                    $sql30 = mysqli_query($conn, "SELECT * FROM time_wait WHERE server_id='" . $active_data['server_id'] . "' and service_id='" . $active_data['service_id'] . "'");
                    if (mysqli_num_rows($sql30) == 1) {
                        $wait_data = mysqli_fetch_assoc($sql30);


                        $givenTime = strtotime($active_data['buy_time']);
                        $currentTime = time();

                        $twoMinutesInSeconds = $wait_data['wait_sec'];

                        $timeLeftSeconds = ($givenTime + $twoMinutesInSeconds) - $currentTime;
                        if ($timeLeftSeconds >= 0) {
                            $minutes = $twoMinutesInSeconds / 60;

                            return '{"status":"600","message":"You Can Cancel Number After ' . $minutes . ' Min"}';
                        }
                    }
                }
            }
            $waiting = check_wait($active_data, $conn);
            if ($waiting != "") {
                echo $waiting;
            } else {
                if ($active_data['sms_text'] == "") {

                    if ($active_data['active_status'] == 1) {
                        echo '{"status":"500","message":"Already Cancelled #1"}';
                    } else {

                        $sql3 = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $active_data['server_id'] . "'");
                        $server_data = mysqli_fetch_assoc($sql3);
                        $api_id = $server_data['api_id'];
                        $sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='" . $api_id . "'");
                        $api_data = mysqli_fetch_assoc($sql4);
                        $sql100 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id'");
                        $active_data1 = mysqli_fetch_assoc($sql100);
                        $api_url = $api_data['api_url'];
                        $api_key = $api_data['api_key'];
                        $number_id = $active_data['number_id'];
                        $url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        // file_put_contents("xyz.txt", "$result");
                        mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);
                        try {
                            $mysql99 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id' and active_status='2'");
                            if (mysqli_num_rows($mysql99) == 1) {
                                $check_api2 = mysqli_fetch_assoc($mysql99);
                                if ($check_api2['sms_text'] == "") {

                                    $sql100 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id'");
                                    $active_data1 = mysqli_fetch_assoc($sql100);
                                    if ($active_data1['sms_text'] == "") {
                                        if ($active_data1['active_status'] == "2") {
                                            $current_time_in_ist = date('Y-m-d H:i:s');
                                            $sql5 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $user_id . "'");
                                            $user_data = mysqli_fetch_assoc($sql5);
                                            $add_balance = $user_data['balance'] + $active_data['service_price'];
                                            $add_balance = $conn->real_escape_string($add_balance);
                                            $cut_otp = $user_data['total_otp'] - 1;
                                            $sql1001 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id'");
                                            $active_data100 = mysqli_fetch_assoc($sql1001);
                                            if ($active_data100['status'] == "2") {
                                                $sql50 = mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $active_data['id'] . "'");
                                                // $sql6 = mysqli_query($conn, "UPDATE user_wallet SET balance='" . $add_balance . "', total_otp='" . $cut_otp . "' WHERE user_id='" . $user_id . "'");
                                                mysqli_query($conn,"CALL UpdateUserBalance(".$user_id.", ".$active_data['service_price'].", 'add')");
                                                if (mysqli_affected_rows($conn) == 0) {
                                                    // mysqli_query($conn, "INSERT INTO `action` (`user_id`, `new_balance`, `old_balance`, `total_otp`, `order_id`, `type`, `date`) VALUES ('" . $user_id . "', '" . $add_balance . "', '" . $user_data['balance'] . "', '" . $cut_otp . "', '" . $order_id . "', 'FAILED_NUMBER','" . $current_time_in_ist . "')");
                                                } else {
                                                    // mysqli_query($conn, "INSERT INTO `action` (`user_id`, `new_balance`, `old_balance`, `total_otp`, `order_id`, `type`, `date`) VALUES ('" . $user_id . "', '" . $add_balance . "', '" . $user_data['balance'] . "', '" . $cut_otp . "', '" . $order_id . "', 'CANCLE_NUMBER','" . $current_time_in_ist . "')");

                                                }
                                                $sql50 = mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $active_data['id'] . "'");
                                                echo '{"status":"200","message":"Number Cancelled #1"}';
                                            } else {
                                                // echo '{"status":"500","message":"Already Cancelled #5"}';
                                                throw new Exception("Already Cancelled #5");

                                            }
                                        } else {
                                            // echo '{"status":"500","message":"Already Cancelled #4"}';
                                            throw new Exception("Already Cancelled #4");

                                        }
                                    } else {
                                        $sql50 = mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $active_data['id'] . "'");
                                        echo '{"status":"200","message":"Number Cancelled #2"}';
                                    }

                                } else {
                                    $sql100 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id'");
                                    $active_data1 = mysqli_fetch_assoc($sql100);

                                    if ($active_data1['active_status'] == 1) {
                                        // echo '{"status":"500","message":"Already Cancelled #2"}';
                                        throw new Exception("Already Cancelled #2");

                                    } else {
                                        $sql5 = mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $active_data['id'] . "'");
                                        echo '{"status":"200","message":"Number Cancelled #3"}';

                                    }
                                }
                            } else {
                                // echo '{"status":"500","message":"Already Cancelled"}';
                                throw new Exception("Already Cancelled");

                            }
                            mysqli_commit($conn);
                        } catch (Exception $e) {
                            mysqli_rollback($conn);
                            echo '{"status":"500","message":"' . $e->getMessage() . '"}';
                        }

                    }

                } else {
                    $sql100 = mysqli_query($conn, "SELECT * FROM active_number WHERE order_id='$order_id'");
                    $active_data1 = mysqli_fetch_assoc($sql100);

                    if ($active_data1['active_status'] == 1) {
                        echo '{"status":"500","message":"Already Cancelled #3"}';
                    } else {
                        $sql5 = mysqli_query($conn, "UPDATE active_number SET active_status='1', status='1' WHERE id='" . $active_data['id'] . "'");
                        echo '{"status":"200","message":"Number Cancelled #4"}';

                    }
                }
            }
        } else {
            echo '{"status":"500","message":"Invalid Order Id Or Already Cancelled"}';
        }
    }
}
?>
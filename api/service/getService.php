<?php
include __DIR__ . '/../../include/config.php';
function makeCurlRequest($url, $api_key, $action, $country)
{
    $url .= '?api_key=' . urlencode($api_key);
    $url .= '&action=' . urlencode($action);
    $url .= '&country=' . urlencode($country);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);


    curl_close($ch);


    return $response;
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
    echo "Invalid Server";
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
    echo "Invalid Token";
} else {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    // $find_token = new radiumsahil();
    $check_token = check_token($token, $conn);
    // $find_token->closeConnection();
    if ($check_token === false) {
        echo 'Token Expired Please Logout And Login Again';
    } else {
        $server = $_GET['server'];
        $sql = "SELECT * FROM service WHERE server_id = '" . $server . "'";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $service_sql = mysqli_fetch_assoc($result);
            $server_sql = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $service_sql['server_id'] . "'");
            $server_data = mysqli_fetch_assoc($server_sql);
            $api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='" . $server_data['api_id'] . "'");
            $api_data = mysqli_fetch_assoc($api_sql);

            $url = $api_data['api_url'] . '/stubs/handler_api.php';
            $action = 'getNumbersStatus';
            $country = $server_data['server_code'];

            $stock_data = makeCurlRequest($url, $api_data['api_key'], $action, $country);
            $stock_data = json_decode($stock_data, true);

            $final = array();

            $sql_logo = mysqli_query($conn, "SELECT * FROM service_icon WHERE short_code='" . $service_sql['service_id'] . "'");
            if (mysqli_num_rows($sql_logo) > 0) {
                $logo_data = mysqli_fetch_assoc($sql_logo);
                $logo_url = $logo_data['img_url'];
            } else {
                $logo_url = "https://i.ibb.co/ySRhxqh/default.png";
            }
            $short_code = $service_sql['service_id'] . '_0';
            $send_stock = false;
            if (isset($stock_data[$short_code])) {
                $send_stock = $stock_data[$short_code];
            } else {
                $send_stock = false;
            }
            $op_price = custom_price($check_token, $service_sql['service_id'], $service_sql['server_id'], $service_sql['service_price'], $conn);
            array_push($final, array(
                'id' => $service_sql['service_id'],
                'service_name' => $service_sql['service_name'],
                'service_price' => $op_price,
                'server_id' => $service_sql['server_id'],
                'logo_url' => $logo_url,
                'stock' => $send_stock
            )
            );

            while ($row = mysqli_fetch_assoc($result)) {
                $sql_logo = mysqli_query($conn, "SELECT * FROM service_icon WHERE short_code='" . $row['service_id'] . "'");
                if (mysqli_num_rows($sql_logo) > 0) {
                    $logo_data = mysqli_fetch_assoc($sql_logo);
                    $logo_url = $logo_data['img_url'];
                } else {
                    $logo_url = "https://i.ibb.co/ySRhxqh/default.png";
                }
                $short_code = $row['service_id'] . '_0';
                $send_stock = false;
                if (isset($stock_data[$short_code])) {
                    $send_stock = $stock_data[$short_code];
                } else {
                    $send_stock = false;
                }
                $op_price = custom_price($check_token, $row['service_id'], $row['server_id'], $row['service_price'], $conn);

                array_push($final, array(
                    'id' => $row['service_id'],
                    'service_name' => $row['service_name'],
                    'service_price' => $op_price,
                    'server_id' => $row['server_id'],
                    'logo_url' => $logo_url,
                    'stock' => $send_stock
                )
                );
            }

            $data = array('service' => $final);

            echo json_encode($data);
        } else {
            echo "Error executing database";
        }

    }
    mysqli_close($conn);
}
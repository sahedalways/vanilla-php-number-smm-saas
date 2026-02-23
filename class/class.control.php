<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class radiumsahil
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function get_token()
    {
        return $_SESSION['token'];
    }
    public function check_token($token)
    {

        $sql = mysqli_query($this->conn, "SELECT * FROM `login_token` WHERE token='$token' and status='1'");
        if (mysqli_num_rows($sql) == 0) {
            return false;
        } else {
            $data = mysqli_fetch_assoc($sql);
            $user_id = $data['user_id'];
            $sql20 = mysqli_query($this->conn, "SELECT * FROM `user_wallet` WHERE user_id='$user_id'");
            $data1 = mysqli_fetch_assoc($sql20);
            $xy = $this->check_activities($data1['balance'], $data1['total_otp'], $data1['total_recharge'], $user_id);
            $sql2 = mysqli_query($this->conn, "SELECT * FROM `user_data` WHERE id='$user_id' and status='1'");
            if (mysqli_num_rows($sql2) == 0) {
                return false;
            } else {
                return $user_id;
            }
        }
    }
    public function balancedata()
    {

        $user_id = $_SESSION['user_id'] ?? null;


        if ($user_id === false) {
            return false;
        } else {
            $sql = "SELECT * FROM `user_wallet` WHERE user_id='" . $user_id . "'";
            $result = mysqli_query($this->conn, $sql);
            return $result->fetch_array();
        }
    }
    public function userdata()
    {

        $user_id = $_SESSION['user_id'] ?? null;


        if ($user_id === false) {
            return false;
        } else {
            $sql = "SELECT * FROM `user_data` WHERE id='" . $user_id . "'";
            $result = mysqli_query($this->conn, $sql);
            return $result->fetch_array();
        }
    }
    public function userwallet()
    {

        $user_id = $_SESSION['user_id'] ?? null;



        if ($user_id === false) {
            return false;
        } else {
            $sql = "SELECT * FROM `user_wallet` WHERE user_id='" . $user_id . "'";
            $result = mysqli_query($this->conn, $sql);
            return $result->fetch_array();
        }
    }
    public function all_server()
    {
        $final = array(); // Initialize an empty array to store results

        $sql = mysqli_query($this->conn, "SELECT * FROM `otp_server` WHERE status='1'");

        while ($row = $sql->fetch_array()) {
            array_push($final, array(
                'id' => $row['id'],
                'server_name' => $row['server_name'],
            ));
        }

        return $final;
    }
    public function number_history()
    {

        $user_id = $_SESSION['user_id'] ?? null;


        if ($user_id === false) {
            return false;
        } else {
            $final = array(); // Initialize an empty array to store results

            $sql = mysqli_query($this->conn, "SELECT * FROM `active_number` WHERE user_id='" . $user_id . "' and status='1' ORDER BY id DESC");

            while ($row = $sql->fetch_array()) {
                $sql2 = mysqli_query($this->conn, "SELECT * FROM otp_server WHERE id='" . $row['server_id'] . "'");

                $sql4 = mysqli_query($this->conn, "SELECT * FROM service_icon WHERE short_code='" . $row['service_id'] . "'");
                if (mysqli_num_rows($sql4) == 1) {
                    $img_data = mysqli_fetch_assoc($sql4);
                    $img_url = $img_data['img_url'];
                } else {
                    $img_url = "https://i.ibb.co/ySRhxqh/default.png";
                }
                $timestamp = strtotime($row['buy_time']);
                $formattedDate = date("M d, Y - h:i A", $timestamp);
                array_push($final, array(
                    'number' => $row['number'],
                    'service_name' => $row['service_name'],
                    'server_id' => $row['server_id'],
                    'buy_time' => $formattedDate,
                    'sms' => $row['sms_text'],
                    'service_price' => $row['service_price'],
                    'logo' =>  $img_url
                ));
            }
        }
        return $final;
    }
    public function all_service()
    {
        $final = array(); // Initialize an empty array to store results

        $sql = mysqli_query($this->conn, "SELECT * FROM `categorys`");

        while ($row = $sql->fetch_array()) {
            array_push($final, array(
                'id' => $row['id'],
                'name' => $row['name'],
                'amount' => $row['amount'],
                'stock' => $row['total_stock'],
            ));
        }

        return $final;
    }
    public function account_history()
    {

        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === false) {
            return false;
        } else {
            $final = array(); // Initialize an empty array to store results

            $sql = mysqli_query($this->conn, "SELECT * FROM `sold` WHERE user_id='" . $user_id . "'");

            while ($row = $sql->fetch_array()) {
                array_push($final, array(
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'password' => $row['password'],
                ));
            }
        }
        return $final;
    }
    public function transaction_history()
    {

        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === false) {
            return false;
        } else {
            $final = array(); // Initialize an empty array to store results

            $sql = mysqli_query($this->conn, "SELECT * FROM `user_transaction` WHERE user_id='" . $user_id . "' ORDER BY id DESC");

            while ($row = $sql->fetch_array()) {
                array_push($final, array(
                    'amount' => $row['amount'],
                    'date' => $row['date'],
                    'type' => $row['type'],
                    'txn_id' => $row['txn_id'],
                    'status' => $row['status'],
                ));
            }
        }
        return $final;
    }
    public function closeConnection()
    {
        $this->conn->close();
    }
    public function negativebal($balance)
    {
        if ($balance < 0) {
            return "1";
        } else {
            return "2";
        }
    }
    public function check_activities($balance, $total_otp, $lifetime, $token)
    {
        $oauthid = $token;

        if ($this->negativebal($balance) == 1 || $this->negativebal($total_otp) == 1 || $this->negativebal($lifetime) == 1 || ($balance > $lifetime)) {
            $sql2 = mysqli_query($this->conn, "SELECT * FROM user_data WHERE id = '$oauthid' AND status = '2'");

            if (mysqli_num_rows($sql2) > 0) {
                return "Already Action";
            } else {
                $sql3 = mysqli_query($this->conn, "UPDATE user_data SET status = '2' WHERE id = '$oauthid'");
                return "#1";
            }
        }

        // Return a default message if none of the conditions are met
        return "No action required";
    }
    public function generateRandomString($length = 25)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random_string = '';

        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $random_string;
    }
    public function generateRandomString32($length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random_string = '';

        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $random_string;
    }
    public function refer_data()
    {
        $token = $this->get_token();
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === false) {
            return false;
        } else {
            $random = $this->generateRandomString();
            $sql = "SELECT * FROM `refer_data` WHERE user_id='" . $user_id . "'";
            $result = mysqli_query($this->conn, $sql);
            if (mysqli_num_rows($result) == 0) {
                mysqli_query($this->conn, "INSERT INTO refer_data (user_id, balance, own_code, refer_by, transfer, total_earn) VALUES ('" . $user_id . "', '0', '" . $random . "', '', '0','0')");
                $sql = "SELECT * FROM `refer_data` WHERE user_id='" . $user_id . "'";
                $result = mysqli_query($this->conn, $sql);
                return $result->fetch_array();
            } else {
                return $result->fetch_array();
            }
        }
    }
    public function refer_users()
    {

        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === false) {
            return false;
        } else {
            $refers = $this->refer_data();
            $sql = "SELECT * FROM `refer_data` WHERE refer_by='" . $refers['own_code'] . "'";
            $result = mysqli_query($this->conn, $sql);
            if (mysqli_num_rows($result) == 0) {
                return 0;
            } else {
                return mysqli_num_rows($result);
            }
        }
    }
    public function recent_history()
    {

        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === false) {
            return false;
        } else {
            $final = array(); // Initialize an empty array to store results

            $sql = mysqli_query($this->conn, "SELECT * FROM `user_transaction` WHERE user_id='" . $user_id . "' ORDER BY id DESC LIMIT 15 ");

            while ($row = $sql->fetch_array()) {
                array_push($final, array(
                    'amount' => $row['amount'],
                    'date' => $row['date'],
                    'type' => $row['type'],
                    'txn_id' => $row['txn_id'],
                    'status' => $row['status'],
                ));
            }
        }
        return $final;
    }
    public function api_data()
    {

        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id === false) {
            return false;
        } else {
            $current_time_in_ist = date('Y-m-d H:i:s');

            $random = $this->generateRandomString32();
            $sql = "SELECT * FROM `user_api` WHERE user_id='" . $user_id . "'";
            $result = mysqli_query($this->conn, $sql);
            if (mysqli_num_rows($result) == 0) {
                mysqli_query($this->conn, "INSERT INTO user_api (user_id, api_key, create_time) VALUES ('" . $user_id . "', '" . $random . "', '" . $current_time_in_ist . "')");
                $sql = "SELECT * FROM `user_api` WHERE user_id='" . $user_id . "'";
                $result = mysqli_query($this->conn, $sql);
                return $result->fetch_array();
            } else {
                return $result->fetch_array();
            }
        }
    }


    public function top_services()
    {
        $sql = mysqli_query($this->conn, "SELECT * FROM top_services ORDER BY id DESC");
        $final = array();
        while ($row = $sql->fetch_array()) {
            $sql2 = mysqli_query($this->conn, "SELECT * FROM otp_server WHERE id='" . $row['server_id'] . "'");
            if (mysqli_num_rows($sql2) == 1) {
                $sql3 = mysqli_query($this->conn, "SELECT * FROM  service WHERE server_id='" . $row['server_id'] . "' AND service_id='" . $row['service_id'] . "'");
                if (mysqli_num_rows($sql3) == 1) {
                    $sql4 = mysqli_query($this->conn, "SELECT * FROM service_icon WHERE short_code='" . $row['service_id'] . "'");
                    if (mysqli_num_rows($sql4) == 1) {
                        $img_data = mysqli_fetch_assoc($sql4);
                        $img_url = $img_data['img_url'];
                    } else {
                        $img_url = "https://i.ibb.co/ySRhxqh/default.png";
                    }
                    $server_data = mysqli_fetch_assoc($sql2);
                    $service_data = mysqli_fetch_assoc($sql3);
                    array_push($final, array(
                        'service_name' => $service_data['service_name'],
                        'service_price' => $service_data['service_price'],
                        'server_name' => $server_data['server_name'],
                        'service_logo' => $img_url,
                    ));
                }
            }
        }
        return $final;
    }
}

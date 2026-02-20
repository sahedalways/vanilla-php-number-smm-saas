<?php
require_once __DIR__ . '/env.php';


loadEnv(__DIR__ . '/../.env');

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
//  error_reporting(0);
date_default_timezone_set('Africa/Lagos');

define('DB_SERVER', getenv('DB_SERVER'));
define('DB_USERNAME', getenv('DB_USERNAME'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));
define('DB_DATABASE', getenv('DB_DATABASE'));

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$site_sql = $conn->query("SELECT * FROM settings WHERE id='1'");
$site_data = $site_sql->fetch_assoc();
$theam = $site_data['theam'];
$protocol = 'https';
$host = $_SERVER['HTTP_HOST'] ?? getenv('SITE_HOST') ?? 'localhost';
$website_url = getenv('BASE_URL');


$web_name = $site_data['web_name'];

define("THEAM", $theam);
define("WEBSITE_URL", $website_url);
define('SECRET_KEY', getenv('SECRET_KEY'));


function check_token($token, $conn)
{
    $sql = mysqli_query($conn, "SELECT * FROM `login_token` WHERE token='$token' and status='1'");
    if (mysqli_num_rows($sql) == 0) {
        return false;
    } else {
        $data = mysqli_fetch_assoc($sql);
        $user_id = $data['user_id'];
        $sql20 = mysqli_query($conn, "SELECT * FROM `user_wallet` WHERE user_id='$user_id'");
        $data1 = mysqli_fetch_assoc($sql20);
        check_activities($data1['balance'], $data1['total_otp'], $data1['total_recharge'], $user_id, $conn);
        $sql2 = mysqli_query($conn, "SELECT * FROM `user_data` WHERE id='$user_id' and status='1'");
        if (mysqli_num_rows($sql2) == 0) {
            return false;
        } else {
            return $user_id;
        }
    }
}
function check_activities($balance, $total_otp, $lifetime, $token, $conn)
{
    $oauthid = $token;

    function negativebal($value)
    {
        return ($value < 0) ? 1 : 0;
    }

    if (negativebal($balance) == 1 || negativebal($total_otp) == 1 || negativebal($lifetime) == 1 || ($balance > $lifetime)) {
        $sql2 = mysqli_query($conn, "SELECT * FROM user_data WHERE id = '$oauthid' AND status = '2'");

        if (mysqli_num_rows($sql2) > 0) {
            return "Already Action";
        } else {
            mysqli_query($conn, "UPDATE user_data SET status = '2' WHERE id = '$oauthid'");
            return "#1";
        }
    }

    return "No action required";
}

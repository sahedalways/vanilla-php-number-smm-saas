<?php
include __DIR__ . '/../../include/config.php';

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
}

function validateEmail($email) {
    // A simple regular expression to check if the email is valid
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
function random($length = 25) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $random_string = '';

    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $random_string;
}
if (!isset($_POST['email']) || $_POST['email'] == "") {
    echo '{"status": "2", "msg": "Enter Email"}';
} elseif (!isset($_POST['password']) || $_POST['password'] == "") {
    echo '{"status": "2", "msg": "Enter Password"}';
} elseif (!isset($_POST['name']) || $_POST['name'] == "") {
    echo '{"status": "2", "msg": "Enter Name"}';
} elseif (strlen($_POST['password']) < 6) {
    echo '{"status": "2", "msg": "Password must be at least 6 characters long"}';
} elseif (!validateEmail($_POST['email'])) {
    echo '{"status": "2", "msg": "Enter Valid Email Address"}';
} else {
$response = $_POST['g-recaptcha-response'];
$secret_key = $site_data['captcha_secret_key'];
$verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret=$secret_key&response=$response";
$verificationResponse = file_get_contents($verifyUrl);
$responseData = json_decode($verificationResponse);

if ($responseData->success) {
    $date = date("Y-m-d H:i:s");
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = generateRandomString();
    $password = md5($_POST['password']);
    $sql = $conn->prepare("SELECT * FROM user_data WHERE email=?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();
    if ($result->num_rows == 0) {

      $sql2 = $conn->prepare("INSERT INTO user_data(name, username, email, password, type, register_date, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$type = "user";
$img_url = "";
$status = 1; // Change this to an integer variable
$sql2->bind_param("sssssssi", $name, $username, $email, $password, $type, $date, $img_url, $status);
$sql2->execute();

        $inserted_id = $conn->insert_id;
       $balance = 0;
$total_recharge = 0;
$total_otp = 0;
$sql3 = $conn->prepare("INSERT INTO user_wallet(user_id, balance, total_recharge, total_otp) VALUES (?, ?, ?, ?)");
$sql3->bind_param("siii", $inserted_id, $balance, $total_recharge, $total_otp);
$sql3->execute();
if(isset($_POST['refer_id']) || $_POST['refer_id'] != "") {
$random = random();
$sql33=$conn->query("SELECT* FROM refer_data WHERE own_code='".$_POST['refer_id']."'");
if(mysqli_num_rows($sql33) == 1){
mysqli_query($conn,"INSERT INTO refer_data (user_id, balance, own_code, refer_by, transfer, total_earn) VALUES ('".$inserted_id."', '0', '".$random."', '".$_POST['refer_id']."', '0','0')");          
}else{
mysqli_query($conn,"INSERT INTO refer_data (user_id, balance, own_code, refer_by, transfer, total_earn) VALUES ('".$inserted_id."', '0', '".$random."', '', '0','0')");          
} 
}   
        echo '{"status": "1","msg": "Register Successful"}';
    } else {
        echo '{"status": "2","msg": "Email Already Registered"}';
    }
}else{   
   echo '{"status": "2","msg": "Captcha Expired Refresh Page"}';        
}
}
$conn->close();
?>

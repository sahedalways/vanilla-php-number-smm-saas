<?php
include __DIR__ . '/../../include/config.php';

if(!isset($_GET['token']) || $_GET['token'] == "") {
echo'{"status":"500","message":"Token Blank"}';

} else {
$token = mysqli_real_escape_string($conn,$_GET['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if($check_token === false){
echo'{"status":"500","message":"Token Expired Please Logout And Login Again"}';
}else{
$user_id = $check_token;
$sql = "SELECT * FROM active_number WHERE user_id = '" . $user_id . "' AND active_status = '2' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$currentTime = date("Y-m-d H:i:s");



if ($result === false) {
    echo '{"status":"500","message":"Database Query Error: ' . mysqli_error($conn) . '"}';
}else{ 
$final = array();   
    while ($row = mysqli_fetch_array($result)) {
        $sql_logo = mysqli_query($conn, "SELECT * FROM service_icon WHERE short_code='".$row['service_id']."'");
        
         if(mysqli_num_rows($sql_logo) > 0) {
            $logo_data = mysqli_fetch_assoc($sql_logo);
            $logo_url = $logo_data['img_url'];
        } else {
             $logo_url = "https://i.ibb.co/ySRhxqh/default.png";
        }
$givenTime = strtotime($row['buy_time']);
$currentTime = time(); 

$twentyMinutesInSeconds = 20 * 60; 

if (($givenTime + $twentyMinutesInSeconds) <= $currentTime) {
    $left = "00:00";
} else {
$timeLeftSeconds = ($givenTime + $twentyMinutesInSeconds) - $currentTime;

     $left = $timeLeftSeconds * 1000;



}
 if($left == "00:00"){
if($row['active_status'] == 2){
$sql3=mysqli_query($conn,"SELECT * FROM otp_server WHERE id='".$row['server_id']."'");
$server_data=mysqli_fetch_assoc($sql3);
$api_id = $server_data['api_id'];
$sql4=mysqli_query($conn,"SELECT * FROM api_detail WHERE id='".$api_id."'");
$api_data=mysqli_fetch_assoc($sql4);
$sql5=mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$user_id."'");
$user_data=mysqli_fetch_assoc($sql5);
if($row['sms_text'] == ""){
$api_url = $api_data['api_url'];
$api_key = $api_data['api_key'];
$number_id = $row['number_id'];
        $url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=setStatus&id={$number_id}&status=8";
        $ch = curl_init($url); 
        // curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $result = curl_exec($ch);
        curl_close($ch);
$add_balance = $user_data['balance'] + $row['service_price'];  
$cut_otp = $user_data['total_otp'] - 1;    
$sql5 = mysqli_query($conn,"UPDATE active_number SET active_status='1', status='3' WHERE id='".$row['id']."'");  
$sql6 = mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_otp='$cut_otp' WHERE user_id='".$user_id."'");
}else{
$sql5 = mysqli_query($conn,"UPDATE active_number SET active_status='1', status='1' WHERE id='".$row['id']."'");  

}
continue;      
}

}       
        array_push($final, array(

            'id' => $row['order_id'],
            'number' => $row['number'], // Corrected column name
            'amount' => $row['service_price'],
            'left_time' => $left,
            'app' => $row['service_name'],
            'sms' => $row['sms_text'],
            'service_id' => $row['service_id'],
            'server_id' => $row['server_id'], 
            'logo_url' => $logo_url,   
        ));

    }
    

$data = array(
'status' => '200',
'data' => $final
);

echo json_encode($data);
}
mysqli_close($conn);
}
}



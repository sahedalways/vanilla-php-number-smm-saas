<?php
session_start();
include  'include/config.php';
if(isset($_SESSION['token'])){
	header('location: dashboard');
    exit;
}
?>
<?php 
$page_title = "Fogot - ".$site_data['web_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/signin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> 
        <wc-toast id="tt" position="top-right"> </wc-toast>
</head>
<body>
    <div class="main-img-div">
        <img src="./images/hero-image.png" class="hero-img_" alt="Allsmsverify Hero Image">
    </div>
    <div class="form-area-div">
        <div class="logo-div-mini mb-4">
            <img class="logo-img-mini" src="./images/logo-png.png" alt="Allsmsverify logo">
            <h1 class="logo-text-mini">Allsmsverify</h1>
        </div>
        <div class="form-div">
            <h1 class="small-heading fw-600">Forgot Password?</h1>
            <p class="">No worries! Just enter your email and we'll send you a reset password link.</p>
                <div class="my-4">
                  <label for="exampleInputEmail1" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" placeholder="johndoe@gmail.com">
                </div>
                <button id="forgot" type="submit" class="btn-color mt-5 create-btn w-100">Send Recovery Email</button>
            <p class="small-text mt-3 text-center">Don't have an account? <a href="register">Sign Up</a></p>
        </div>
        </p>
    </div>

    <script src="./js/forgot_password.js"></script>
<?php include ('partial/custom_js.php'); ?>
</body>
</html>
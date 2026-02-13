<?php
session_start();
include  'include/config.php';
require_once __DIR__ . '/class/class.control.php';
if(isset($_SESSION['token'])){
	header('location: dashboard');
}
// include 'theam/' . THEAM . '/register.php';

?>
<?php 
$page_title = "Register- ".$site_data['web_name'];
?>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/signin.css">
    <link rel="stylesheet" href="./css/signup.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            <h1 class="small-heading fw-600 mb-4">Sign up</h1>
                <div class="row mb-3">
                    <div class="col">
                      <label for="first_name" class="form-label">First name</label>
                      <input type="text" class="form-control" id="first_name" aria-label="First name">
                       <?php
              if(isset($_GET['ref'])){
              echo '<input type="hidden" id="refer_id" value="' . $_GET['ref'] . '">';
             }else{
                echo '<input type="hidden" id="refer_id" value="">';               
             }
                ?>
                    </div>
                    <div class="col">
                      <label for="last_name" class="form-label">Last name</label>
                      <input type="text" class="form-control" id="last_name" aria-label="Last name">
                    </div>
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email address</label>
                  <input type="email" class="form-control" id="email">
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <div class="position-relative">
                    <input type="password" class="form-control" id="password">
                    <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                  </div>
                </div>
                  <center><div class="g-recaptcha" data-sitekey="<?php echo $site_data['captcha_public_key']; ?>"></div></center><br>
               <div class="mb-3 form-check">
  <input type="checkbox" class="form-check-input" id="exampleCheck1">
  <label class="form-check-label smaller-text" for="exampleCheck1">
    By clicking Create Account, I agree that I have read and accepted 
    the <a href="privacy-policy.html">Privacy Policy</a> and 
    <a href="tos.html">Terms of Service</a>.
  </label>
</div>

                </div>  
                <button id="register" type="submit" class="btn-color create-btn w-100">Create Account</button>      
            <p class="small-text mt-3 text-center">I have an account? <a href="login">Sign In</a></p>
        </div>
    </div>
<?php include ('partial/custom_js.php'); ?>
    <script src="./js/signin.js"></script>
<body>    
</html>
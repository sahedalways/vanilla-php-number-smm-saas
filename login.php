<?php
session_start();

include  'include/config.php';
// require_once __DIR__ . '/class/class.control.php';
if (isset($_SESSION['token'])) {
    header('location: dashboard');
    exit;
}
if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];

    $_SESSION['token'] = $token;

    header("Location: dashboard");
    exit;
}


require_once 'helpers/session.php';
guestOnly();


if (isset($_GET['msg'])) {
    $error_data = $_GET['msg'];
    if ($error_data == "not_found") {
        $msg1 = "Account Not Found Please Register In Website Then Login";
        $button_msg = 'Register Now';
        $button_url = "register";
    } else if ($error_data == "block") {
        $msg1 = "Your Account Blocked By Admin Please Contact Our Support Team";
        $button_msg = 'Contact Now';
        $button_url = $site_data['support_url'];
    } else {
        $msg1 = "You don’t have permission to access this page. Go Home!!";
        $button_msg = 'Back To Home';
        $button_url = "index";
    }
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

?>
<?php
$page_title = "Login - " . $site_data['web_name'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Allsmsverify</title>

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

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body>
    <div class="main-img-div">
        <img src="./images/hero-image.png" class="hero-img_" alt="Allsmsverify Hero Image">
    </div>
    <div class="form-area-div">
        <a href="/" class="logo-div-mini mb-x" style="text-decoration: none;">
            <img class="logo-img-mini" src="./images/logo-png.png" alt="Allsmsverify logo">
            <h1 class="logo-text-mini">Allsmsverify</h1>
        </a>
        <div class="form-div">
            <h1 class="small-heading fw-600 mb-4">Sign in</h1>

            <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" placeholder="Enter your email" value="customer@gmail.com">
                <div id="error-email" class="text-danger small mt-1"></div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control" id="password" placeholder="Enter your password" value="12345678">
                    <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                </div>
                <div id="error-password" class="text-danger small mt-1"></div>
            </div>

            <button id="login" type="button" class="btn-color create-btn w-100 mt-3">
                <span class="btn-text">Sign In</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>

            <p class="small-text mt-3 text-center">Don't have an account? <a href="register">Sign Up</a></p>
        </div>



        <p class="text-center small-text mt-4">Protected by the reCAPTCHA and subject to the Allsmsverify
            <br><a href="privacy-policy.html">Privacy Policy</a> and <a href="tos.html">Terms of Service</a>
        </p>
    </div>

    <script src="js/signin.js"></script>
    <?php include('partial/custom_js.php'); ?>
</body>

</html>

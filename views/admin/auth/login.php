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
    <title>Admin Login | Allsmsverify</title>

    <link rel="shortcut icon" href="<?php echo $WEBSITE_URL; ?>/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/admin_login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body>
    <div class="login-container">
        <div class="text-center">
            <div class="text-center px-4 py-5 bg-light shadow-sm rounded" style="max-width: 400px; margin: 40px auto;">
                <!-- Logo -->
                <img src="<?php echo $WEBSITE_URL; ?>/images/logo-png.png"
                    alt="Logo"
                    class="brand-logo"
                    style="width: 100px; height: auto; display: block; margin: 0 auto 8px auto;">

                <!-- Badge below the logo -->
                <div class="admin-badge px-3 py-1 rounded-pill text-white"
                    style="background-color: #4a90e2; font-size: 0.8rem; display: inline-block; margin-bottom: 12px;">
                    Secure Admin Access
                </div>

                <!-- Welcome Text -->
                <h2 class="fw-bold mb-1" style="font-size: 1.6rem; color: #333;">Welcome Back</h2>
                <p class="text-muted small mb-0" style="max-width: 300px; margin: 0 auto;">
                    Enter your credentials to securely access and manage the platform.
                </p>
            </div>
        </div>


        <form id="loginForm">
            <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Admin Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 0.75rem 0 0 0.75rem;">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email" class="form-control border-start-0" id="email"
                        placeholder="admin@example.com" style="border-radius: 0 0.75rem 0.75rem 0;">
                </div>
                <div id="error-email" class="text-danger x-small mt-1" style="font-size: 0.75rem;"></div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control" id="password" placeholder="••••••••">
                    <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                </div>
                <div id="error-password" class="text-danger x-small mt-1" style="font-size: 0.75rem;"></div>
            </div>

            <button id="login" type="button" class="btn btn-login w-100 mb-3">
                <span class="btn-text">Sign In to Dashboard</span>
                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
            </button>
        </form>

        <div class="text-center">
            <a href="/login" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Return to Customer Login
            </a>
        </div>
    </div>


    <script src="../../../js/admin_signin.js"></script>
</body>

</html>

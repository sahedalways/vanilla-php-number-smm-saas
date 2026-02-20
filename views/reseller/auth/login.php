<?php
session_start();
include 'include/config.php';
require_once 'helpers/session.php';
guestOnly();

// Detect subdomain
$host = $_SERVER['HTTP_HOST'];
$host = explode(':', $host)[0]; // remove port
$parts = explode('.', $host);
$resellerName = null;

if (count($parts) > 1 && $parts[0] !== 'www') {
    $subdomain = $parts[0];

    // Check if reseller exists
    $stmt = $conn->prepare("SELECT name, email FROM user_data WHERE username = ? AND type='reseller'");
    $stmt->bind_param("s", $subdomain);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $reseller = $result->fetch_assoc();
        $resellerName = $reseller['name']; // use in page
    } else {
        header("Location: /");
        exit;
    }
} else {
    header("Location: /");
    exit;
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// If already logged in as reseller
if (isset($_SESSION['reseller_token'])) {
    header("Location: /reseller/dashboard");
    exit;
}

$page_title = "Reseller Login - " . $resellerName;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="shortcut icon" href="/images/logo-png.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/reseller-login.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body>
    <div class="form-area-div">
        <a href="/" class="logo-div-mini mb-x" style="text-decoration: none;">
            <img class="logo-img-mini" src="/images/logo-png.png" alt="Foreign sms logo">
            <h1 class="logo-text-mini">Foreign sms</h1>
        </a>

        <div class="form-div">
            <h1 class="small-heading fw-600 mb-2">Reseller Login</h1>
            <?php if ($resellerName): ?>
                <p class="small-text mb-4 text-center">Welcome, <strong><?php echo htmlspecialchars($resellerName); ?></strong></p>
            <?php endif; ?>

            <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">

            <!-- Email / Username -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($reseller['email'] ?? ''); ?>" readonly>
                <div id="error-email" class="text-danger small mt-1"></div>
            </div>


            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control" id="password" placeholder="Enter your password">
                    <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                </div>
                <div id="error-password" class="text-danger small mt-1"></div>
            </div>

            <button id="login" type="button" class="btn-color create-btn w-100 mt-3">
                <span class="btn-text">Sign In</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>


        </div>

        <p class="text-center small-text mt-4">Protected by the reCAPTCHA and subject to the Foreign sms
            <br><a href="/privacy-policy.html">Privacy Policy</a> and <a href="/tos.html">Terms of Service</a>
        </p>
    </div>

    <script src="/js/reseller-login.js"></script>
</body>

</html>

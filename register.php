<?php
session_start();
include  'include/config.php';



require_once 'helpers/session.php';
guestOnly();


// Detect subdomain
$host = $_SERVER['HTTP_HOST'];
$host = explode(':', $host)[0];
$parts = explode('.', $host);
$resellerName = null;

if (count($parts) > 1 && $parts[0] !== 'www') {
  header("Location: /");
}


if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<?php
$page_title = "Register- " . $site_data['web_name'];
?>
<meta charset="UTF-8">
<title>Register | Foreign sms</title>
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

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

</head>

<body>
  <div class="main-img-div">
    <img src="./images/hero-image.png" class="hero-img_" alt="Foreign sms Hero Image">
  </div>
  <div class="form-div">
    <a href="/" class="logo-div-mini mb-x" style="text-decoration: none;">
      <img class="logo-img-mini" src="./images/logo-png.png" alt="Foreign sms logo">
      <h1 class="logo-text-mini">Foreign sms</h1>
    </a>

    <h1 class="small-heading fw-600 mb-4">Sign up</h1>
    <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">


    <!-- Full Name -->
    <div class="mb-3">
      <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="name" placeholder="Enter your full name" required>
      <div id="error-name" class="text-danger small mt-1"></div>
    </div>

    <!-- Email -->
    <div class="mb-3">
      <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
      <input type="email" class="form-control" id="email" placeholder="Enter your email" required>
      <div id="error-email" class="text-danger small mt-1"></div>
    </div>

    <!-- Phone Number -->
    <div class="mb-3">
      <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
      <input
        type="tel"
        class="form-control"
        id="phone"
        placeholder="Enter your phone number"
        required
        pattern="[0-9]{7,15}"
        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
      <div id="error-phone" class="text-danger small mt-1"></div>
    </div>


    <div class="mb-3">
      <label for="referral" class="form-label">Referral Username (Optional)</label>
      <input type="text" class="form-control" id="referral" placeholder="Enter referral username">
      <div id="error-referral" class="text-danger small mt-1"></div>
    </div>

    <!-- Password -->
    <div class="mb-3">
      <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
      <div class="position-relative">
        <input type="password" class="form-control" id="password" placeholder="Enter your password" required minlength="6">
        <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
      </div>
      <div id="error-password" class="text-danger small mt-1"></div>
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
      <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
      <div class="position-relative">
        <input type="password" class="form-control" id="confirm_password" placeholder="Confirm your password" required minlength="6">
        <i class="bi bi-eye-slash toggle-password" id="toggleConfirmPassword"></i>
      </div>
      <div id="error-confirm_password" class="text-danger small mt-1"></div>
    </div>

    <!-- reCAPTCHA -->
    <center>
      <div class="g-recaptcha" data-sitekey="<?php echo $site_data['captcha_public_key']; ?>"></div>
    </center>
    <br>

    <!-- Terms Checkbox -->
    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="terms_check" required>
      <label class="form-check-label smaller-text" for="terms_check">
        By clicking Create Account, I agree that I have read and accepted
        the <a href="privacy-policy.html">Privacy Policy</a> and
        <a href="tos.html">Terms of Service</a>.
      </label>
      <div id="error-terms" class="text-danger small mt-1"></div>
    </div>

    <!-- Submit Button -->
    <button id="register" type="button" class="btn-color create-btn w-100">
      <span class="btn-text">Create Account</span>
      <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
    </button>


    <p class="small-text mt-3 text-center">
      I already have an account? <a href="login">Sign In</a>
    </p>
  </div>

  </div>

  <script src="./js/signup.js"></script>

</body>

</html>

<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
// Redirect to dashboard if already logged in
function guestOnly($redirect = 'dashboard')
{
    if (isset($_SESSION['auth_token'])) {
        header("Location: /$redirect");
        exit;
    }
}

// Protect pages for authenticated users only
function authOnly($redirect = 'login')
{
    if (!isset($_SESSION['auth_token'])) {
        header("Location: /$redirect");
        exit;
    }
}

// Login helper
function loginUser($userId)
{
    $_SESSION['auth_token'] = bin2hex(random_bytes(32));
    $_SESSION['user_id'] = $userId;
}

// Logout helper
function logoutUser($redirect = 'login')
{
    session_unset();
    session_destroy();
    header("Location: /$redirect");
    exit;
}

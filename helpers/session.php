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
function guestOnly()
{
    if (isset($_SESSION['auth_token'])) {
        // Determine dashboard based on user type
        $userType = $_SESSION['user_type'] ?? 'customer';

        switch ($userType) {
            case 'admin':
                $redirectPage = '/views/admin/dashboard';
                break;
            case 'reseller':
                $redirectPage = '/views/reseller/dashboard';
                break;
            default:
                $redirectPage = '/views/customer/dashboard';
        }

        header("Location: $redirectPage");
        exit;
    }
}


// Protect pages for authenticated users only
function authOnly()
{
    if (!isset($_SESSION['auth_token'])) {

        $loginPage = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin')
            ? '/views/admin/auth/login'
            : "/login";

        header("Location: $loginPage");
        exit;
    }
}


// Login helper
function loginUser($user)
{
    // $user is the full user array from database
    $_SESSION['auth_token'] = bin2hex(random_bytes(32));
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['name']      = $user['name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['phone']     = $user['phone'];
    $_SESSION['type']      = $user['type'];
}

// Logout helper
function logoutUser($redirect = 'login')
{
    session_unset();
    session_destroy();
    header("Location: /$redirect");
    exit;
}

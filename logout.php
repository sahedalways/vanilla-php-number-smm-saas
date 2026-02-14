<?php
session_start();


$userType = $_SESSION['type'] ?? null;


session_destroy();
unset($_SESSION['auth_token']);


setcookie('remember_me', '', [
	'expires' => time() - 3600,
	'path' => '/',
	'domain' => $_SERVER['HTTP_HOST'],
	'secure' => true,
	'httponly' => true,
	'samesite' => 'Lax'
]);

// Conditional redirect
if ($userType == 'reseller') {
	header('Location: /views/reseller/auth/login');
}
if ($userType == 'admin') {
	header('Location: /views/admin/auth/login');
}
if ($userType == 'customer') {
	header('Location: /login');
}
exit;

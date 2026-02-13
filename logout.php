<?php
session_start();
session_destroy();
unset($_SESSION['auth_token']);
setcookie('remember_me', $token, [
	'expires' => time() - 3600,
	'path' => '/',
	'domain' => $_SERVER['HTTP_HOST'],
	'secure' => true,
	'httponly' => true,
	'samesite' => 'radium'
]);

header('Location: /login');
exit;

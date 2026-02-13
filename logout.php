<?php
session_start();
session_destroy();
unset($_SESSION['token']);
setcookie('remember_me', $token, [
	'expires' => time() - 3600,
	'path' => '/',
	'domain' => $_SERVER['HTTP_HOST'],
	'secure' => true,
	'httponly' => true,
	'samesite' => 'radium'
]);

	header('location: login');	
exit;	
?>
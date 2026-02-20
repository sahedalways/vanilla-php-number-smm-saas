<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|ico|svg|pdf|ttf|woff|woff2)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];


$request = $request_uri;


if (strpos($request, '?') !== false) {
    $request = substr($request, 0, strpos($request, '?'));
}


$base = dirname($script_name);
if ($base == '/' || $base == '\\' || $base == '.') {
    $base = '';
}


if (!empty($base) && strpos($request, $base) === 0) {
    $request = substr($request, strlen($base));
}

$uri = trim($request, '/');


if ($uri === '') {
    include 'index.php';
} elseif (file_exists($uri . '.php')) {
    include $uri . '.php';
} else {
    // 404 পেজ
    http_response_code(404);
    include '404.php'; // আপনার 404 পেজ
}
?>
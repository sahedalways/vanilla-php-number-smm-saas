<?php

if (preg_match('/\.(?:png|jpg|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if ($uri === '') {
    include 'index.php';
} elseif (file_exists($uri . '.php')) {
    include $uri . '.php';
} else {
    http_response_code(404);
    echo "Page not found";
}

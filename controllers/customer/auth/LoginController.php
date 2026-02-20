<?php

session_start();
header('Content-Type: application/json');
include 'include/config.php';
require_once __DIR__ . '/../../../helpers/session.php';




ini_set('display_errors', 1);
error_reporting(E_ALL);

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}


// get POST data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$csrf_token = $_POST['csrf_token'] ?? '';


// validate inputs
if (!$email || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

// fetch user from database
$stmt = $conn->prepare("SELECT id, name, email, phone,balance, type, password FROM user_data WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Account not found.']);
    exit;
}


if ($user['type'] !== 'customer') {
    echo json_encode(['status' => 'error', 'message' => 'You are not authorized to access the customer dashboard.']);
    exit;
}



// verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Incorrect email or password.']);
    exit;
}


loginUser($user);

echo json_encode(['status' => 'success', 'message' => 'Login successful']);
exit;

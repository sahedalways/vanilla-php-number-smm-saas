<?php

session_start();
header('Content-Type: application/json');
include 'include/config.php';
require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../helpers/session.php';
$username = generateUsername();



ini_set('display_errors', 1);
error_reporting(E_ALL);

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// sanitize inputs
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';


if (!$name || !$email || !$phone || !$password || !$confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

if (!preg_match('/^[0-9]{7,15}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number']);
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM user_data WHERE email = ?");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    exit;
}



$stmt = $conn->prepare("SELECT id FROM user_data WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number already exists']);
    exit;
}



$hash = password_hash($password, PASSWORD_DEFAULT);
$type = 'customer';


$subdomain = null;



// insert user
$stmt = $conn->prepare("
    INSERT INTO user_data (username, name, email, phone, password, type, subdomain, register_date)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

$stmt->bind_param("sssssss", $username, $name, $email, $phone, $hash, $type, $subdomain);

if ($stmt->execute()) {
    $customerId = $stmt->insert_id;



    echo json_encode([
        'status' => 'success',
        'message' => 'Account created successfully',
        'customer_id' => $customerId
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $stmt->error]);
}

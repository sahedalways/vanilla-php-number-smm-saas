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
$referral = trim($_POST['referral'] ?? '');


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


$reseller_id = null;
if ($referral) {
    $stmt = $conn->prepare("SELECT id FROM user_data WHERE username = ? AND type='reseller'");
    $stmt->bind_param("s", $referral);
    $stmt->execute();
    $result = $stmt->get_result();
    $referrer = $result->fetch_assoc();
    if (!$referrer) {
        echo json_encode(['status' => 'error', 'message' => 'Referral username does not exist']);
        exit;
    }
    $reseller_id = $referrer['id'];
}




$stmt = $conn->prepare("SELECT id FROM user_data WHERE phone = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number already exists']);
    exit;
}




// hash password
$hash = password_hash($password, PASSWORD_DEFAULT);
$type = 'customer';



// insert user
$stmt = $conn->prepare("
    INSERT INTO user_data (username, name, email, phone, password, type, register_date)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

// bind parameters
$stmt->bind_param("ssssss", $username, $name, $email, $phone, $hash, $type);


if ($stmt->execute()) {
    $userId = $conn->insert_id;

    $stmt = $conn->prepare("SELECT id, name, email, phone, balance, type FROM user_data WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();



    if ($reseller_id) {
        $stmt2 = $conn->prepare("
        INSERT INTO reseller_customers (reseller_id, customer_id, added_at)
        VALUES (?, ?, NOW())
    ");
        $stmt2->bind_param("ii", $reseller_id, $userId);
        $stmt2->execute();
    }

    loginUser($user);


    echo json_encode(['status' => 'success', 'message' => 'Account created successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $stmt->error]);
}

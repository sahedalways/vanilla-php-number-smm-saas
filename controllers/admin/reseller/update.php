<?php

session_start();
header('Content-Type: application/json');
include 'include/config.php';
require_once __DIR__ . '/../../../helpers/functions.php';
require_once __DIR__ . '/../../../helpers/session.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// sanitize inputs
$id = $_POST['id'] ?? '';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!$id || !$name || !$email || !$phone) {
    echo json_encode(['status' => 'error', 'message' => 'ID, Name, Email, and Phone are required']);
    exit;
}

if ($password || $confirm_password) {
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters']);
        exit;
    }
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

if (!preg_match('/^[0-9]{7,15}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number']);
    exit;
}

// Check if email exists for other users
$stmt = $conn->prepare("SELECT id FROM user_data WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    exit;
}

// Check if phone exists for other users
$stmt = $conn->prepare("SELECT id FROM user_data WHERE phone = ? AND id != ?");
$stmt->bind_param("si", $phone, $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number already exists']);
    exit;
}

// Build update query
$fields = "name = ?, email = ?, phone = ?";
$params = [$name, $email, $phone];
$types = "sss";

// If password is provided, hash it and add to update
if ($password) {
    $fields .= ", password = ?";
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $params[] = $hash;
    $types .= "s";
}

$fields .= " WHERE id = ?";
$params[] = $id;
$types .= "i";

// Prepare dynamic query
$stmt = $conn->prepare("UPDATE user_data SET $fields");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
    exit;
}

// Bind params dynamically
$stmt->bind_param($types, ...$params);

// Execute
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Reseller updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $stmt->error]);
}

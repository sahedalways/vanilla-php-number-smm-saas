<?php
session_start();
header('Content-Type: application/json');
include 'include/config.php';
require_once __DIR__ . '/../../../helpers/session.php';

$reseller_id = $_SESSION['user_id'] ?? null;

if (!$reseller_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Sanitize inputs
$bank_name = trim($_POST['bank_name'] ?? '');
$account_name = trim($_POST['account_name'] ?? '');
$account_number = trim($_POST['account_number'] ?? '');
$swift_code = trim($_POST['swift_code'] ?? '');
$amount = trim($_POST['amount'] ?? '');

// Validation
if (!$bank_name || !$account_name || !$account_number || !$amount) {
    echo json_encode(['status' => 'error', 'message' => 'All fields except SWIFT code are required']);
    exit;
}

if (!is_numeric($amount) || floatval($amount) <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid withdraw amount']);
    exit;
}

if (!preg_match('/^\d{10}$/', $account_number)) {
    echo json_encode(['status' => 'error', 'message' => 'Account number must be 10 digits']);
    exit;
}

// Check reseller balance
$stmt = $conn->prepare("SELECT balance FROM user_data WHERE id = ?");
$stmt->bind_param("i", $reseller_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();

if (floatval($balance) < floatval($amount)) {
    echo json_encode(['status' => 'error', 'message' => 'Insufficient balance']);
    exit;
}

// Insert withdraw request
$stmt = $conn->prepare("
    INSERT INTO reseller_withdraw_requests
        (reseller_id, bank_name, account_name, account_number, swift_code, amount, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
");

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("issssd", $reseller_id, $bank_name, $account_name, $account_number, $swift_code, $amount);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Withdraw request submitted successfully',
        'request_id' => $stmt->insert_id
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create request: ' . $stmt->error]);
}

$stmt->close();

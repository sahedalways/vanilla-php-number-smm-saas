<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../helpers/session.php';
require_once __DIR__ . '/../../../include/config.php';

authOnly(); // only logged-in reseller

$resellerId = $_SESSION['user_id'] ?? null;

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Get inputs
$bankId         = intval($_POST['bank_id'] ?? 0);
$bank_name      = trim($_POST['bank_name'] ?? '');
$account_name   = trim($_POST['account_name'] ?? '');
$account_number = trim($_POST['account_number'] ?? '');
$swift_code     = trim($_POST['swift_code'] ?? '');

// Validation
$errors = [];

if (!$bank_name)      $errors['bank_name'] = 'Bank name is required';
if (!$account_name)   $errors['account_name'] = 'Account name is required';
if (!$account_number) $errors['account_number'] = 'Account number is required';
elseif (!preg_match('/^\d{10}$/', $account_number)) $errors['account_number'] = 'Account number must be 10 digits';

if ($errors) {
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

// Check if record exists
$checkStmt = $conn->prepare("
    SELECT id FROM reseller_bank_infos
    WHERE id = ? AND reseller_id = ?
");
$checkStmt->bind_param("ii", $bankId, $resellerId);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    // UPDATE existing record
    $stmt = $conn->prepare("
        UPDATE reseller_bank_infos
        SET bank_name = ?,
            account_name = ?,
            account_number = ?,
            swift_code = ?,
            updated_at = NOW()
        WHERE id = ? AND reseller_id = ?
    ");
    $stmt->bind_param(
        "ssssii",
        $bank_name,
        $account_name,
        $account_number,
        $swift_code,
        $bankId,
        $resellerId
    );
    $action = 'updated';
} else {
    // INSERT new record
    $stmt = $conn->prepare("
        INSERT INTO reseller_bank_infos
            (reseller_id, bank_name, account_name, account_number, swift_code)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "issss",
        $resellerId,
        $bank_name,
        $account_name,
        $account_number,
        $swift_code
    );
    $action = 'created';
}

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => "Bank account $action successfully"
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $stmt->error
    ]);
}

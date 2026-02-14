<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../helpers/session.php';
require_once __DIR__ . '/../../../include/config.php';

authOnly();

$id = intval($_POST['id']);
$csrf_token = $_POST['csrf_token'] ?? '';

if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    exit(json_encode(['status' => 'error', 'message' => 'Invalid CSRF']));
}

// Get request info along with reseller balance
$stmt = $conn->prepare("
    SELECT r.reseller_id, r.amount, u.balance
    FROM reseller_withdraw_requests r
    JOIN user_data u ON r.reseller_id = u.id
    WHERE r.id = ? AND r.status = 'pending'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($reseller_id, $amount, $balance);
if (!$stmt->fetch()) {
    $stmt->close();
    exit(json_encode(['status' => 'error', 'message' => 'Request not found']));
}
$stmt->close();

if ($balance < $amount) {
    // Not enough balance – reject request
    $stmt = $conn->prepare("UPDATE reseller_withdraw_requests SET status='rejected', updated_at=NOW() WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    exit(json_encode(['status' => 'error', 'message' => 'Insufficient balance – request rejected']));
}

// Enough balance – approve request
$stmt = $conn->prepare("UPDATE user_data SET balance = balance - ? WHERE id=?");
$stmt->bind_param("di", $amount, $reseller_id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("UPDATE reseller_withdraw_requests SET status='approved', updated_at=NOW() WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

echo json_encode(['status' => 'success', 'message' => 'Withdrawal approved']);

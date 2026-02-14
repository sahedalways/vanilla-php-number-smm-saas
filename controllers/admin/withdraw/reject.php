<?php
session_start();
require_once '../../include/config.php';
require_once '../../helpers/session.php';

authOnly();


if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$id = intval($_POST['id'] ?? 0);
$csrf = $_POST['csrf_token'] ?? '';

if ($csrf !== ($_SESSION['csrf_token'] ?? '')) exit(json_encode(['status' => 'error', 'message' => 'Invalid CSRF']));

// Check if request exists and is pending
$stmt = $conn->prepare("SELECT id FROM reseller_withdraw_requests WHERE id=? AND status='pending'");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) exit(json_encode(['status' => 'error', 'message' => 'Request not found or already processed']));
$stmt->close();

// Update status to rejected
$stmt = $conn->prepare("UPDATE reseller_withdraw_requests SET status='rejected', updated_at=NOW() WHERE id=?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Withdrawal request rejected']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to reject request']);
}

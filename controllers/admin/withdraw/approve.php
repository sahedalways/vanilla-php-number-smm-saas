<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../helpers/session.php';
require_once __DIR__ . '/../../../include/config.php';

authOnly();


$id = intval($_POST['id']);
$csrf_token = $_POST['csrf_token'] ?? '';

if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) exit(json_encode(['status' => 'error', 'message' => 'Invalid CSRF']));

// Get request info
$stmt = $conn->prepare("SELECT reseller_id, amount FROM reseller_withdraw_requests WHERE id=? AND status='pending'");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($reseller_id, $amount);
if (!$stmt->fetch()) exit(json_encode(['status' => 'error', 'message' => 'Request not found']));
$stmt->close();

// Deduct balance
$stmt = $conn->prepare("UPDATE user_data SET balance = balance - ? WHERE id=?");
$stmt->bind_param("di", $amount, $reseller_id);
$stmt->execute();
$stmt->close();

// Update request status
$stmt = $conn->prepare("UPDATE reseller_withdraw_requests SET status='approved', updated_at=NOW() WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

echo json_encode(['status' => 'success', 'message' => 'Withdrawal approved']);

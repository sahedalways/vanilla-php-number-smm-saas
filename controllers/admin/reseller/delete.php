<?php
session_start();
header('Content-Type: application/json');
include 'include/config.php';
require_once __DIR__ . '/../../../helpers/session.php';

authOnly();

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

$id = $_POST['id'] ?? '';

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid reseller ID']);
    exit;
}

// Optional: Only allow admin
if ($_SESSION['type'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Delete reseller
$stmt = $conn->prepare("DELETE FROM user_data WHERE id = ? AND type = 'reseller'");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Reseller deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Delete failed: ' . $stmt->error]);
}

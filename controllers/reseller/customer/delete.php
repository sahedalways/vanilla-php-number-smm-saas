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
    echo json_encode(['status' => 'error', 'message' => 'Invalid Customer ID']);
    exit;
}

// Only allow reseller
if ($_SESSION['type'] !== 'reseller') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id = intval($id);

try {
    // Start transaction
    $conn->begin_transaction();

    // 1. Delete from reseller_customers
    $stmt1 = $conn->prepare("DELETE FROM reseller_customers WHERE customer_id = ? AND reseller_id = ?");
    $stmt1->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt1->execute();
    $stmt1->close();

    // 2. Delete from user_data
    $stmt2 = $conn->prepare("DELETE FROM user_data WHERE id = ? AND type = 'customer'");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    // Commit transaction
    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Customer deleted successfully']);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Delete failed: ' . $e->getMessage()]);
}

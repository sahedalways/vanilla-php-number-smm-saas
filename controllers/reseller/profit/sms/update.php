<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../helpers/session.php';
require_once __DIR__ . '/../../../../include/config.php';

authOnly();

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

$profit = trim($_POST['profit_percentage'] ?? '');

// Validate profit
if ($profit === '') {
    echo json_encode(['status' => 'error', 'message' => 'Profit percentage is required']);
    exit;
}

if (!is_numeric($profit) || $profit < 0 || $profit > 100) {
    echo json_encode(['status' => 'error', 'message' => 'Enter a valid number between 0 and 100']);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

try {
    // Check if a row already exists for this reseller
    $stmtCheck = $conn->prepare("SELECT id FROM reseller_sms_profit_settings WHERE user_id = ? LIMIT 1");
    $stmtCheck->bind_param("i", $userId);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['id'];
        $stmt = $conn->prepare("UPDATE reseller_sms_profit_settings SET profit_percentage = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("di", $profit, $id);
        $action = 'updated';
    } else {
        $stmt = $conn->prepare("INSERT INTO reseller_sms_profit_settings (user_id, profit_percentage) VALUES (?, ?)");
        $stmt->bind_param("id", $userId, $profit);
        $action = 'saved';
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => "Profit percentage $action successfully"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
    $stmtCheck->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()]);
}

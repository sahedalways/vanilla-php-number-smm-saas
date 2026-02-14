<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../helpers/session.php';
require_once __DIR__ . '/../../../include/config.php';

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

// Check if a row already exists
$result = $conn->query("SELECT id FROM profit_settings LIMIT 1");

if ($result && $result->num_rows > 0) {
    // Update existing profit
    $stmt = $conn->prepare("UPDATE profit_settings SET profit_percentage = ?, updated_at = NOW() WHERE id = ?");
    $row = $result->fetch_assoc();
    $id = $row['id'];
    $stmt->bind_param("di", $profit, $id);
    $action = 'updated';
} else {
    // Insert new profit
    $stmt = $conn->prepare("INSERT INTO profit_settings (profit_percentage) VALUES (?)");
    $stmt->bind_param("d", $profit);
    $action = 'saved';
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => "Profit percentage $action successfully"]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

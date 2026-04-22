<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$conn = getDB();
$result = $conn->query("SELECT id, full_name, email, phone, created_at FROM users ORDER BY created_at DESC");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['status' => 'success', 'users' => $users]);
$conn->close();
?>
<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$conn = getDB();
$result = $conn->query("SELECT * FROM inquiries ORDER BY created_at DESC");
$inquiries = [];
while ($row = $result->fetch_assoc()) {
    $inquiries[] = $row;
}

echo json_encode(['status' => 'success', 'inquiries' => $inquiries]);
$conn->close();
?>
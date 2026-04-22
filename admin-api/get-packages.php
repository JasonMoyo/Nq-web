<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$conn = getDB();
$result = $conn->query("SELECT * FROM package_bookings ORDER BY created_at DESC");
$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}

echo json_encode(['status' => 'success', 'packages' => $packages]);
$conn->close();
?>
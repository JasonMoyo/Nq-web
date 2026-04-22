<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$conn = getDB();
$result = $conn->query("SELECT * FROM service_bookings ORDER BY created_at DESC");
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

echo json_encode(['status' => 'success', 'bookings' => $bookings]);
$conn->close();
?>
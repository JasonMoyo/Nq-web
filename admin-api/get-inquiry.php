<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$conn = getDB();
$stmt = $conn->prepare("SELECT * FROM inquiries WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($inquiry = $result->fetch_assoc()) {
    echo json_encode(['status' => 'success', 'inquiry' => $inquiry]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Inquiry not found']);
}

$stmt->close();
$conn->close();
?>
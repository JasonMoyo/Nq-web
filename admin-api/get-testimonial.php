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
$stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($testimonial = $result->fetch_assoc()) {
    echo json_encode(['status' => 'success', 'testimonial' => $testimonial]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Testimonial not found']);
}

$stmt->close();
$conn->close();
?>
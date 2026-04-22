<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Don't allow deleting yourself
if ($id == $_SESSION['user_id']) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete your own account']);
    exit();
}

$conn = getDB();
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete user']);
}

$stmt->close();
$conn->close();
?>
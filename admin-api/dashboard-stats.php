<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$conn = getDB();

// Get total bookings
$result = $conn->query("SELECT COUNT(*) as count FROM service_bookings");
$total_bookings = $result->fetch_assoc()['count'];

// Get total package bookings
$result = $conn->query("SELECT COUNT(*) as count FROM package_bookings");
$total_packages = $result->fetch_assoc()['count'];

// Get total inquiries (new ones)
$result = $conn->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'new'");
$total_inquiries = $result->fetch_assoc()['count'];

// Get total testimonials
$result = $conn->query("SELECT COUNT(*) as count FROM testimonials");
$total_testimonials = $result->fetch_assoc()['count'];

// Get total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $result->fetch_assoc()['count'];

// Get recent bookings (last 5)
$recent_bookings = [];
$result = $conn->query("SELECT * FROM service_bookings ORDER BY created_at DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recent_bookings[] = $row;
}

// Get recent inquiries (last 5)
$recent_inquiries = [];
$result = $conn->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recent_inquiries[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => [
        'total_bookings' => $total_bookings,
        'total_packages' => $total_packages,
        'total_inquiries' => $total_inquiries,
        'total_testimonials' => $total_testimonials,
        'total_users' => $total_users,
        'recent_bookings' => $recent_bookings,
        'recent_inquiries' => $recent_inquiries
    ]
]);

$conn->close();
?>
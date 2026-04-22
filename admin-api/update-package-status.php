<?php
session_start();
require_once '../config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

$conn = getDB();

// Get package booking details
$stmt = $conn->prepare("SELECT * FROM package_bookings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();

// Update status
$stmt = $conn->prepare("UPDATE package_bookings SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    // Send email notification
    if ($package && !empty($package['email'])) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USERNAME');
            $mail->Password   = getenv('SMTP_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            
            $mail->setFrom('no-reply@nqobileq.com', 'NqobileQ');
            $mail->addAddress($package['email']);
            $mail->addAddress(OWNER_EMAIL);
            
            $statusMessage = $status == 'confirmed' ? 'Confirmed' : 'Cancelled';
            $statusColor = $status == 'confirmed' ? '#28a745' : '#dc3545';
            
            $mail->isHTML(true);
            $mail->Subject = "Package Booking {$statusMessage} - NqobileQ";
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #00b8a9; color: white; padding: 20px; text-align: center; }
                    .status { background: {$statusColor}; color: white; padding: 10px; text-align: center; border-radius: 5px; }
                    .details { background: #f5f5f5; padding: 20px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>NqobileQ Package Booking</h2>
                    </div>
                    <div class='status'>
                        <h3>Package {$statusMessage}</h3>
                    </div>
                    <div class='details'>
                        <h3>Package Details:</h3>
                        <p><strong>Booking ID:</strong> #{$package['id']}</p>
                        <p><strong>Name:</strong> {$package['name']}</p>
                        <p><strong>Package:</strong> {$package['package_name']}</p>
                        <p><strong>Status:</strong> {$statusMessage}</p>
                        " . ($status == 'confirmed' ? "<p><strong>Welcome to NqobileQ!</strong> Your package benefits are now active.</p>" : "") . "
                    </div>
                    <div class='footer'>
                        <p>Thank you for choosing NqobileQ</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Package status email error: {$mail->ErrorInfo}");
        }
    }
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update']);
}

$stmt->close();
$conn->close();
?>
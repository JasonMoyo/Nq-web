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

// Get booking details before updating
$stmt = $conn->prepare("SELECT * FROM service_bookings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// Update status
$stmt = $conn->prepare("UPDATE service_bookings SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    // Send email notification to customer
    if ($booking && !empty($booking['email'])) {
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
            $mail->addAddress($booking['email']);
            $mail->addAddress(OWNER_EMAIL);
            
            $statusMessage = $status == 'confirmed' ? 'Confirmed' : 'Cancelled';
            $statusColor = $status == 'confirmed' ? '#28a745' : '#dc3545';
            
            $mail->isHTML(true);
            $mail->Subject = "Booking {$statusMessage} - NqobileQ";
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #00b8a9; color: white; padding: 20px; text-align: center; }
                    .status { background: {$statusColor}; color: white; padding: 10px; text-align: center; border-radius: 5px; }
                    .details { background: #f5f5f5; padding: 20px; margin-top: 20px; }
                    .footer { text-align: center; margin-top: 20px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>NqobileQ Service Booking</h2>
                    </div>
                    <div class='status'>
                        <h3>Booking {$statusMessage}</h3>
                    </div>
                    <div class='details'>
                        <h3>Booking Details:</h3>
                        <p><strong>Booking ID:</strong> #{$booking['id']}</p>
                        <p><strong>Name:</strong> {$booking['name']}</p>
                        <p><strong>Service:</strong> {$booking['service_type']}</p>
                        <p><strong>Preferred Date:</strong> {$booking['preferred_date']}</p>
                        <p><strong>Status:</strong> {$statusMessage}</p>
                        " . ($status == 'confirmed' ? "<p><strong>Next Steps:</strong> Our team will contact you shortly to confirm the details.</p>" : "<p><strong>Note:</strong> If you have any questions, please contact us.</p>") . "
                    </div>
                    <div class='footer'>
                        <p>Thank you for choosing NqobileQ</p>
                        <p><a href='https://wa.me/27782280408'>Contact us on WhatsApp</a> | Call: +27782280408</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Booking status email error: {$mail->ErrorInfo}");
        }
    }
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update']);
}

$stmt->close();
$conn->close();
?>
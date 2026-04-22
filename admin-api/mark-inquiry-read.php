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

$conn = getDB();

// Get inquiry details
$stmt = $conn->prepare("SELECT * FROM inquiries WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$inquiry = $stmt->get_result()->fetch_assoc();

// Update status
$stmt = $conn->prepare("UPDATE inquiries SET status = 'read' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Send email notification that inquiry was received
    if ($inquiry && !empty($inquiry['email'])) {
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
            $mail->addAddress($inquiry['email']);
            $mail->addAddress(OWNER_EMAIL);
            
            $mail->isHTML(true);
            $mail->Subject = "We've Received Your Inquiry - NqobileQ";
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #00b8a9; color: white; padding: 20px; text-align: center; }
                    .content { background: #f5f5f5; padding: 20px; margin-top: 20px; }
                    .message-box { background: white; padding: 15px; border-left: 4px solid #00b8a9; margin: 15px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>NqobileQ</h2>
                        <p>Your Inquiry Has Been Received</p>
                    </div>
                    <div class='content'>
                        <h3>Hello {$inquiry['name']},</h3>
                        <p>Thank you for reaching out to NqobileQ. We have received your inquiry and our team will respond within 24 hours.</p>
                        <div class='message-box'>
                            <h4>Your Message:</h4>
                            <p>{$inquiry['message']}</p>
                        </div>
                        <p>We'll get back to you shortly!</p>
                    </div>
                    <div class='footer'>
                        <p>Need immediate assistance? <a href='https://wa.me/27782280408'>WhatsApp us</a> or call +27782280408</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Inquiry response email error: {$mail->ErrorInfo}");
        }
    }
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update']);
}

$stmt->close();
$conn->close();
?>
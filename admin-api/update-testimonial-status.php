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

// Get testimonial details
$stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$testimonial = $stmt->get_result()->fetch_assoc();

// Update status
$stmt = $conn->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    // Send email notification only for approved testimonials
    if ($testimonial && !empty($testimonial['email']) && $status == 'approved') {
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
            $mail->addAddress($testimonial['email']);
            $mail->addAddress(OWNER_EMAIL);
            
            $stars = str_repeat('★', $testimonial['rating']) . str_repeat('☆', 5 - $testimonial['rating']);
            
            $mail->isHTML(true);
            $mail->Subject = "Your Review Has Been Published - NqobileQ";
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #00b8a9; color: white; padding: 20px; text-align: center; }
                    .content { background: #f5f5f5; padding: 20px; margin-top: 20px; }
                    .review-box { background: white; padding: 15px; border-radius: 10px; margin: 15px 0; }
                    .stars { color: #ffc107; font-size: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Thank You for Your Review!</h2>
                    </div>
                    <div class='content'>
                        <h3>Hello {$testimonial['name']},</h3>
                        <p>Thank you for sharing your experience with NqobileQ. Your review has been approved and is now live on our website!</p>
                        <div class='review-box'>
                            <div class='stars'>{$stars}</div>
                            <p><em>\"{$testimonial['message']}\"</em></p>
                        </div>
                        <p>We truly appreciate your feedback and support!</p>
                    </div>
                    <div class='footer'>
                        <p>Visit our website to see your review: <a href='http://localhost/Docker-Webs-main-Copy/index.php#review'>View Reviews</a></p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Testimonial approval email error: {$mail->ErrorInfo}");
        }
    }
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update']);
}

$stmt->close();
$conn->close();
?>
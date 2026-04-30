<?php
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $conn = getDB();

    $name        = $conn->real_escape_string($_POST['name'] ?? '');
    $email       = $conn->real_escape_string($_POST['email'] ?? '');
    $phone       = $conn->real_escape_string($_POST['phone'] ?? '');
    $service     = $conn->real_escape_string($_POST['service_type'] ?? '');
    $preferred_date = $conn->real_escape_string($_POST['preferred_date'] ?? '');
    $message     = $conn->real_escape_string($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($service)) {
        die("Error: Required fields are missing.");
    }

    $sql = "INSERT INTO service_bookings (name, email, phone, service_type, preferred_date, message) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $email, $phone, $service, $preferred_date, $message);

    if ($stmt->execute()) {

        // Get the newly created booking ID
        $booking_id = $conn->insert_id;

        // Send email notification
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USERNAME');
            $mail->Password   = getenv('SMTP_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('no-reply@nqobileq.com', 'NqobileQ Website');
            $mail->addAddress(OWNER_EMAIL);
            $mail->addAddress($email); // Send confirmation to customer too

            $mail->isHTML(true);
            $mail->Subject = 'New Service Booking - NqobileQ';
            $mail->Body    = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #00b8a9; color: white; padding: 20px; text-align: center; }
                    .details { background: #f5f5f5; padding: 20px; margin-top: 20px; }
                    .detail-row { margin-bottom: 10px; }
                    .label { font-weight: bold; color: #333; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>New Service Booking Confirmation</h2>
                    </div>
                    <div class='details'>
                        <div class='detail-row'><span class='label'>Name:</span> $name</div>
                        <div class='detail-row'><span class='label'>Email:</span> $email</div>
                        <div class='detail-row'><span class='label'>Phone:</span> $phone</div>
                        <div class='detail-row'><span class='label'>Service:</span> $service</div>
                        <div class='detail-row'><span class='label'>Preferred Date:</span> $preferred_date</div>
                        <div class='detail-row'><span class='label'>Message:</span> $message</div>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
        }

        // ============ REDIRECT TO STRIPE PAYMENT ============
        // Amount in cents: $5.00 USD = 500 cents
        // You can change this amount based on service type
        $amount_cents = 500; // $5.00 USD
        
        // Redirect to Stripe checkout page
        header("Location: stripe-checkout.php?booking_id=" . $booking_id . "&amount=" . $amount_cents . "&service=" . urlencode($service));
        exit;

    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    echo "Invalid request method.";
}
?>
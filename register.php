<?php
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize form data
    $full_name = mysqli_real_escape_string(getDB(), $_POST['full_name'] ?? '');
    $email = mysqli_real_escape_string(getDB(), $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string(getDB(), $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($full_name) || empty($email) || empty($password)) {
        die("Error: Required fields are missing.");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $conn = getDB();
    
    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        die("Error: Email already registered. Please login.");
    }
    
    // IMPORTANT: Added is_admin field with default value 0 (regular user)
    $sql = "INSERT INTO users (full_name, email, phone, password, is_admin) 
            VALUES (?, ?, ?, ?, 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $full_name;
        $_SESSION['is_admin'] = false; // Regular user, not admin
        
        // Send welcome email
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
            $mail->addAddress($email);
            $mail->addAddress(OWNER_EMAIL);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to NqobileQ - Registration Successful';
            $mail->Body    = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #00b8a9; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; }
                    .button { background: #00b8a9; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Welcome to NqobileQ, $full_name!</h2>
                    </div>
                    <div class='content'>
                        <p>Thank you for registering with NqobileQ. Your account has been created successfully.</p>
                        <p>You can now:</p>
                        <ul>
                            <li>Book services online</li>
                            <li>Subscribe to packages</li>
                            <li>Track your bookings</li>
                            <li>Receive exclusive offers</li>
                        </ul>
                        <p style='text-align: center;'>
                            <a href='http://localhost/nqobileq/index.php#services' class='button'>Browse Our Services</a>
                        </p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Welcome email error: {$mail->ErrorInfo}");
        }
        
        // Redirect to registration success page
        header("Location: registration-success.php");
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
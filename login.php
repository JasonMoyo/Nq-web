<?php
require_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize form data
    $email = mysqli_real_escape_string(getDB(), $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($email) || empty($password)) {
        die("Error: Email and password are required.");
    }

    // Check user in database
    $conn = getDB();
    
    // IMPORTANT: Select is_admin column
    $sql = "SELECT id, full_name, email, password, is_admin FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Verify password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_name'] = $row['full_name'];
            
            // Check if user is admin
            $_SESSION['is_admin'] = isset($row['is_admin']) && $row['is_admin'] == 1;
            
            // Debug: You can uncomment this to see what's happening
            // echo "is_admin value: " . $row['is_admin'] . "<br>";
            // echo "Session is_admin: " . ($_SESSION['is_admin'] ? 'true' : 'false') . "<br>";
            // exit;
            
            // Redirect based on user type
            if ($_SESSION['is_admin']) {
                header("Location: admin/index.php");
                exit;
            } else {
                header("Location: welcome.php");
                exit;
            }
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Email not found.";
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    echo "Invalid request method.";
}
?>
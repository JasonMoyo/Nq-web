<?php
// Enable error reporting for debugging (disable in production)
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Load environment variables from .env file
$envFile = __DIR__ . '/.env';

if (file_exists($envFile)) {
    $dotenv = file_get_contents($envFile);
    $lines = explode("\n", $dotenv);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Database configuration - Use environment variables for AWS
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'nqobileq_db');

// Owner contact information
define('OWNER_PHONE', getenv('OWNER_PHONE') ?: '+27782280408');
define('OWNER_EMAIL', getenv('OWNER_EMAIL') ?: 'thabani070801@gmail.com');

// Site URL - Important for AWS
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost');

// Create connection
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        // Log error instead of dying
        error_log("Connection failed: " . $conn->connect_error);
        return null;
    }
    
    $conn->set_charset("utf8");
    return $conn;
}

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
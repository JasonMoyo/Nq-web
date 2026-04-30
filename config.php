<?php
// Enable error reporting for debugging (disable in production)
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Load environment variables from .env file - DIRECT APPROACH
$envFile = __DIR__ . '/.env';
$env_vars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
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
            $env_vars[$key] = $value;
        }
    }
}

// Helper function to get env vars - MOVED BEFORE constants
function get_env($key, $default = null) {
    global $env_vars;
    
    // Check our loaded array first
    if (isset($env_vars[$key])) {
        return $env_vars[$key];
    }
    
    // Fallback to getenv
    $value = getenv($key);
    if ($value !== false && $value !== null) {
        return $value;
    }
    
    // Fallback to $_ENV
    if (isset($_ENV[$key]) && $_ENV[$key] !== null) {
        return $_ENV[$key];
    }
    
    return $default;
}

// Database configuration
define('DB_HOST', get_env('DB_HOST', 'localhost'));
define('DB_USER', get_env('DB_USER', 'root'));
define('DB_PASS', get_env('DB_PASSWORD', ''));
define('DB_NAME', get_env('DB_NAME', 'nqobileq_db'));

// Stripe Configuration
define('STRIPE_PUBLISHABLE_KEY', get_env('STRIPE_PUBLISHABLE_KEY', ''));
define('STRIPE_SECRET_KEY', get_env('STRIPE_SECRET_KEY', ''));

// Owner contact information
define('OWNER_PHONE', get_env('OWNER_PHONE', '+27782280408'));
define('OWNER_EMAIL', get_env('OWNER_EMAIL', 'thabani070801@gmail.com'));

// Site URL
define('SITE_URL', get_env('SITE_URL', 'http://localhost'));

// Initialize Stripe if keys are present
if (defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY && !empty(STRIPE_SECRET_KEY) && file_exists(__DIR__ . '/vendor/autoload.php')) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    } catch (Exception $e) {
        error_log("Stripe initialization failed: " . $e->getMessage());
    }
}

// Create connection
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
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
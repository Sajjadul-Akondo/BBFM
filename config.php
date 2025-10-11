<?php
/**
 * Configuration settings for the Best Buy For Me website
 */

// Database configuration
define('DB_TYPE', 'sqlite'); // Use SQLite for easier development
define('DB_PATH', __DIR__ . '/database.sqlite'); // SQLite database file path

// Website configuration
define('SITE_NAME', 'Best Buy For Me');
define('SITE_URL', 'http://localhost:5000');
define('ADMIN_EMAIL', 'admin@bestbuyforme.com');

// Session settings
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

// Email settings
define('EMAIL_FROM', 'noreply@bestbuyforme.com');
define('EMAIL_NAME', 'Best Buy For Me');

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('HASH_COST', 10); // For password hashing

// Cart settings
define('CART_COOKIE_LIFETIME', 60*60*24*7); // 7 days in seconds

// File paths
define('UPLOADS_DIR', __DIR__ . '/uploads');

/**
 * Error reporting settings
 * Set to false in production
 */
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

/**
 * Initialize session with secure settings
 */
function init_session() {
    $session_name = 'bbfm_session';
    $secure = false; // Set to true if using HTTPS
    $httponly = true;
    
    // Force session to use cookies
    if (ini_set('session.use_only_cookies', 1) === false) {
        error_log("Cannot set session to use only cookies");
    }
    
    // Get session cookie parameters
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params(
        $cookieParams["lifetime"],
        $cookieParams["path"],
        $cookieParams["domain"],
        $secure,
        $httponly
    );
    
    // Set the session name
    session_name($session_name);
    
    // Start the session
    session_start();
    
    // Regenerate session ID to prevent session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > SESSION_TIMEOUT) {
        // Session is older than 30 minutes, regenerate ID
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Initialize session
init_session();

// Custom error handler
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    if (DEBUG_MODE) {
        echo "Error: [$errno] $errstr - $errfile:$errline";
    } else {
        error_log("Error: [$errno] $errstr - $errfile:$errline");
    }
    return true;
}

// Set custom error handler
set_error_handler("custom_error_handler");

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to validate CSRF token
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to redirect with message
function redirect($url, $message = '', $message_type = 'info') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $message_type;
    }
    header('Location: ' . $url);
    exit;
}
?>

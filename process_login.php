<?php
/**
 * Process login form
 */

// Include necessary files
require_once 'config.php';
require_once 'db_connect.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate csrf token if implemented
    // if (!validate_csrf_token($_POST['csrf_token'])) {
    //     $response['message'] = 'Invalid request. Please try again.';
    //     die(json_encode($response));
    // }
    
    // Get form data
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $userType = isset($_POST['user_type']) ? sanitize_input($_POST['user_type']) : '';
    $rememberMe = isset($_POST['remember']) ? true : false;
    
    // For debugging
    error_log("Login attempt - Email: $email, User Type: $userType");
    
    // Validate form data
    if (empty($email) || empty($password) || empty($userType)) {
        $_SESSION['login_error'] = 'All fields are required.';
        header('Location: login.php');
        exit;
    }
    
    try {
        // Check if user exists
        $query = "SELECT id, username, email, password, user_type, fullname 
                  FROM users 
                  WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // User found
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if user type matches
            if ($user['user_type'] !== $userType) {
                error_log("User type mismatch - Expected: $userType, Found: {$user['user_type']}");
                $_SESSION['login_error'] = "Incorrect user type. You're registered as a {$user['user_type']}.";
                header('Location: login.php');
                exit;
            }
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];
                
                error_log("Login successful for user: {$user['username']} as {$user['user_type']}");
                
                // Set cookie if remember me is checked
                if ($rememberMe) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 30*24*60*60, '/', '', false, true);
                    
                    // Store token in database for later verification
                    // Check if user_tokens table exists, if not create it
                    $checkTokensTable = "SELECT name FROM sqlite_master WHERE type='table' AND name='user_tokens'";
                    $stmt = $db->prepare($checkTokensTable);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() == 0) {
                        // Create the table if it doesn't exist
                        $createTokensTable = "CREATE TABLE IF NOT EXISTS user_tokens (
                            id INTEGER PRIMARY KEY AUTOINCREMENT,
                            user_id INTEGER NOT NULL,
                            token TEXT NOT NULL,
                            expiry TIMESTAMP NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                        )";
                        $db->exec($createTokensTable);
                    }
                    
                    $query = "INSERT INTO user_tokens (user_id, token, expiry) 
                              VALUES (:user_id, :token, :expiry)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $user['id']);
                    $stmt->bindParam(':token', $token);
                    $expiry = date('Y-m-d H:i:s', time() + 30*24*60*60);
                    $stmt->bindParam(':expiry', $expiry);
                    $stmt->execute();
                }
                
                // Redirect based on user type
                switch ($user['user_type']) {
                    case 'admin':
                        $redirect = 'admin/dashboard.php';
                        break;
                    case 'seller':
                        $redirect = 'seller/dashboard.php';
                        break;
                    case 'customer':
                    default:
                        $redirect = 'index.html';
                        break;
                }
                
                // Set success message
                $_SESSION['success_message'] = 'Login successful. Welcome back, ' . $user['fullname'] . '!';
                header('Location: ' . $redirect);
                exit;
            } else {
                // Invalid password
                error_log("Invalid password for user: $email");
                $_SESSION['login_error'] = 'Invalid email or password.';
                header('Location: login.php');
                exit;
            }
        } else {
            // User not found
            error_log("User not found with email: $email");
            $_SESSION['login_error'] = 'Invalid email or password.';
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            $_SESSION['login_error'] = 'Database error: ' . $e->getMessage();
        } else {
            $_SESSION['login_error'] = 'An error occurred during login. Please try again later.';
            error_log('Login error: ' . $e->getMessage());
        }
        header('Location: login.php');
        exit;
    }
} else {
    // If not POST request, redirect to login page
    header('Location: login.php');
    exit;
}
?>

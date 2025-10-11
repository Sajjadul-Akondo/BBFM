<?php
/**
 * Password reset functionality
 */

// Include necessary files
require_once 'config.php';
require_once 'db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get and sanitize email
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_error'] = 'Please enter a valid email address.';
        header('Location: forgetpassword.html');
        exit;
    }
    
    try {
        // Check if email exists in the database
        $query = "SELECT id, username, fullname FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // User exists, generate a reset token
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $user['id'];
            $username = $user['username'];
            $fullname = $user['fullname'];
            
            // Generate unique token
            $token = bin2hex(random_bytes(32));
            
            // Set expiry time (1 hour from now)
            $expiry = date('Y-m-d H:i:s', time() + 3600);
            
            // Delete any existing tokens for this user
            $query = "DELETE FROM reset_tokens WHERE user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            // Store the token in the database
            $query = "INSERT INTO reset_tokens (user_id, token, expiry) VALUES (:user_id, :token, :expiry)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expiry', $expiry);
            $stmt->execute();
            
            // Create reset link
            $reset_link = SITE_URL . '/reset_confirm.php?token=' . $token;
            
            // Send reset email (in a real app, you would use a library like PHPMailer)
            // For this example, we'll just simulate sending an email
            
            $to = $email;
            $subject = "Password Reset - " . SITE_NAME;
            
            $message = "
            <html>
            <head>
                <title>Password Reset</title>
            </head>
            <body>
                <h2>Hello $fullname,</h2>
                <p>We received a request to reset your password for your account at " . SITE_NAME . ".</p>
                <p>To reset your password, please click on the link below:</p>
                <p><a href='$reset_link'>Reset Your Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request a password reset, you can ignore this email and your password will remain unchanged.</p>
                <p>Thank you,<br>The " . SITE_NAME . " Team</p>
            </body>
            </html>
            ";
            
            // Set content-type header for sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . EMAIL_NAME . ' <' . EMAIL_FROM . '>' . "\r\n";
            
            // Send email (commented out for this example)
            // mail($to, $subject, $message, $headers);
            
            // For demonstration purposes
            error_log("Password reset email would be sent to $email with token $token");
            
            // Set success message
            $_SESSION['reset_success'] = "A password reset link has been sent to your email address. Please check your inbox.";
            header('Location: forgetpassword.html');
            exit;
        } else {
            // Email not found
            $_SESSION['reset_error'] = "We couldn't find an account with that email address.";
            header('Location: forgetpassword.html');
            exit;
        }
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            $_SESSION['reset_error'] = 'Database error: ' . $e->getMessage();
        } else {
            $_SESSION['reset_error'] = 'An error occurred. Please try again later.';
            error_log('Password reset error: ' . $e->getMessage());
        }
        header('Location: forgetpassword.html');
        exit;
    }
} elseif (isset($_GET['token'])) {
    // This is the token verification section
    $token = sanitize_input($_GET['token']);
    
    try {
        // Check if token exists and is valid
        $query = "SELECT user_id, expiry FROM reset_tokens WHERE token = :token LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $expiry = strtotime($result['expiry']);
            
            // Check if token has expired
            if (time() > $expiry) {
                $_SESSION['reset_error'] = 'This password reset link has expired. Please request a new one.';
                header('Location: forgetpassword.html');
                exit;
            }
            
            // Token is valid, show password reset form
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_user_id'] = $result['user_id'];
            
            // In a real app, you would render a new password form here
            // For now, we'll just redirect to a simulated new password page
            header('Location: reset_new_password.html');
            exit;
        } else {
            // Invalid token
            $_SESSION['reset_error'] = 'Invalid password reset link. Please request a new one.';
            header('Location: forgetpassword.html');
            exit;
        }
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            $_SESSION['reset_error'] = 'Database error: ' . $e->getMessage();
        } else {
            $_SESSION['reset_error'] = 'An error occurred. Please try again later.';
            error_log('Token verification error: ' . $e->getMessage());
        }
        header('Location: forgetpassword.html');
        exit;
    }
} else {
    // If not POST request or no token, redirect to forgot password page
    header('Location: forgetpassword.html');
    exit;
}
?>

<?php
// This code would be in reset_new_password.php in a real app
// It would handle the form for setting a new password

// Handle new password submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    
    // Check if the token is in the session
    if (!isset($_SESSION['reset_token']) || !isset($_SESSION['reset_user_id'])) {
        $_SESSION['reset_error'] = 'Invalid password reset session. Please try again.';
        header('Location: forgetpassword.html');
        exit;
    }
    
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_SESSION['reset_token'];
    $user_id = $_SESSION['reset_user_id'];
    
    // Validate passwords
    if (empty($new_password)) {
        $_SESSION['new_password_error'] = 'New password is required.';
        header('Location: reset_new_password.html');
        exit;
    }
    
    if (strlen($new_password) < PASSWORD_MIN_LENGTH) {
        $_SESSION['new_password_error'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
        header('Location: reset_new_password.html');
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['new_password_error'] = 'Passwords do not match.';
        header('Location: reset_new_password.html');
        exit;
    }
    
    try {
        // Verify token is still valid
        $query = "SELECT id FROM reset_tokens WHERE token = :token AND user_id = :user_id AND expiry > NOW() LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['reset_error'] = 'This password reset link has expired or is invalid. Please request a new one.';
            header('Location: forgetpassword.html');
            exit;
        }
        
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update user password
        $query = "UPDATE users SET password = :password WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Delete all reset tokens for this user
        $query = "DELETE FROM reset_tokens WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Clear reset session variables
        unset($_SESSION['reset_token']);
        unset($_SESSION['reset_user_id']);
        
        // Set success message
        $_SESSION['login_success'] = 'Your password has been reset successfully. You can now log in with your new password.';
        header('Location: login.php');
        exit;
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            $_SESSION['new_password_error'] = 'Database error: ' . $e->getMessage();
        } else {
            $_SESSION['new_password_error'] = 'An error occurred. Please try again later.';
            error_log('Password update error: ' . $e->getMessage());
        }
        header('Location: reset_new_password.html');
        exit;
    }
}
?>

<?php
/**
 * Process registration form
 */

// Include necessary files
require_once 'config.php';
require_once 'db_connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $fullname = isset($_POST['fullname']) ? sanitize_input($_POST['fullname']) : '';
    $gender = isset($_POST['gender']) ? sanitize_input($_POST['gender']) : '';
    $username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $user_type = isset($_POST['user_type']) ? sanitize_input($_POST['user_type']) : '';
    $agree = isset($_POST['agree']) ? true : false;
    
    // Validate form data
    $errors = [];
    
    if (empty($fullname)) {
        $errors[] = 'Full name is required.';
    }
    
    if (empty($gender) || !in_array($gender, ['male', 'female'])) {
        $errors[] = 'Please select your gender.';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = 'Username must be between 3 and 20 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($user_type) || !in_array($user_type, ['customer', 'seller'])) {
        $errors[] = 'Please select a valid user type.';
    }
    
    if (!$agree) {
        $errors[] = 'You must agree to the terms and conditions.';
    }
    
    // Check if there are any errors
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_data'] = [
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email,
            'user_type' => $user_type,
            'gender' => $gender
        ];
        header('Location: register.html');
        exit;
    }
    
    try {
        // Check if username already exists
        $query = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['register_errors'] = ['Username already exists. Please choose a different one.'];
            $_SESSION['register_data'] = [
                'fullname' => $fullname,
                'email' => $email,
                'user_type' => $user_type,
                'gender' => $gender
            ];
            header('Location: register.html');
            exit;
        }
        
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['register_errors'] = ['Email already exists. Please use a different one or try to login.'];
            $_SESSION['register_data'] = [
                'fullname' => $fullname,
                'username' => $username,
                'user_type' => $user_type,
                'gender' => $gender
            ];
            header('Location: register.html');
            exit;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        $query = "INSERT INTO users (fullname, username, email, password, gender, user_type) 
                  VALUES (:fullname, :username, :email, :password, :gender, :user_type)";
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':user_type', $user_type);
        
        $stmt->execute();
        
        // Get the newly created user ID
        $user_id = $db->lastInsertId();
        
        // Create session for the new user
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['fullname'] = $fullname;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['email'] = $email;
        
        // Set success message
        $_SESSION['success_message'] = 'Registration successful! Welcome to Best Buy For Me, ' . $fullname . '!';
        
        // Redirect to appropriate page
        if ($user_type === 'seller') {
            header('Location: seller/dashboard.php');
        } else {
            header('Location: index.html');
        }
        exit;
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            $_SESSION['register_errors'] = ['Database error: ' . $e->getMessage()];
        } else {
            $_SESSION['register_errors'] = ['An error occurred during registration. Please try again later.'];
            error_log('Registration error: ' . $e->getMessage());
        }
        
        $_SESSION['register_data'] = [
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email,
            'user_type' => $user_type,
            'gender' => $gender
        ];
        
        header('Location: register.html');
        exit;
    }
} else {
    // If not POST request, redirect to registration page
    header('Location: register.html');
    exit;
}
?>

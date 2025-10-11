<?php
/**
 * Handle cart actions (AJAX)
 */

// Include necessary files
require_once 'config.php';
require_once 'db_connect.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get JSON data from request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if data was successfully decoded
if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit;
}

// Get action from data
$action = isset($data['action']) ? $data['action'] : '';

// Handle different actions
switch ($action) {
    case 'checkout':
        handleCheckout($data, $db);
        break;
    case 'save_cart':
        handleSaveCart($data, $db);
        break;
    case 'get_cart':
        handleGetCart($db);
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}

/**
 * Handle checkout process
 * 
 * @param array $data Request data
 * @param PDO $db Database connection
 */
function handleCheckout($data, $db) {
    // Check if user is logged in
    if (!is_logged_in()) {
        echo json_encode([
            'success' => false,
            'login_required' => true,
            'message' => 'Please log in to complete your purchase'
        ]);
        exit;
    }
    
    // Get cart items from request
    $cart = isset($data['cart']) ? $data['cart'] : [];
    
    // Validate cart
    if (empty($cart)) {
        echo json_encode([
            'success' => false,
            'message' => 'Your cart is empty'
        ]);
        exit;
    }
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Create order
        $user_id = $_SESSION['user_id'];
        $total_amount = calculateTotal($cart);
        $shipping_address = 'Sample Address'; // In a real app, this would come from the form
        $payment_method = 'Credit Card'; // In a real app, this would come from the form
        
        // Insert order
        $query = "INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) 
                  VALUES (:user_id, :total_amount, :shipping_address, :payment_method)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':shipping_address', $shipping_address);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->execute();
        
        // Get new order ID
        $order_id = $db->lastInsertId();
        
        // Insert order items
        foreach ($cart as $item) {
            $name = $item['name'];
            $price = $item['price'];
            $quantity = $item['quantity'];
            
            // Get product ID if available
            $product_id = null;
            $query = "SELECT id FROM products WHERE name = :name LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $product_id = $result['id'];
                
                // Update product stock (in a real application)
                // $query = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
                // $stmt = $db->prepare($query);
                // $stmt->bindParam(':quantity', $quantity);
                // $stmt->bindParam(':id', $product_id);
                // $stmt->execute();
            }
            
            // Insert order item
            $query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                      VALUES (:order_id, :product_id, :product_name, :quantity, :price)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':product_name', $name);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':price', $price);
            $stmt->execute();
        }
        
        // Commit transaction
        $db->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'message' => 'Order placed successfully'
        ]);
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $db->rollBack();
        
        if (DEBUG_MODE) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        } else {
            error_log('Checkout error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while processing your order. Please try again later.'
            ]);
        }
    }
}

/**
 * Handle saving cart to database for logged in users
 * 
 * @param array $data Request data
 * @param PDO $db Database connection
 */
function handleSaveCart($data, $db) {
    // Check if user is logged in
    if (!is_logged_in()) {
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in'
        ]);
        exit;
    }
    
    // Get cart from request
    $cart = isset($data['cart']) ? $data['cart'] : [];
    
    try {
        $user_id = $_SESSION['user_id'];
        
        // Delete existing saved cart
        $query = "DELETE FROM saved_carts WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Save new cart if not empty
        if (!empty($cart)) {
            $cart_json = json_encode($cart);
            
            $query = "INSERT INTO saved_carts (user_id, cart_data) VALUES (:user_id, :cart_data)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':cart_data', $cart_json);
            $stmt->execute();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cart saved successfully'
        ]);
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        } else {
            error_log('Save cart error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while saving your cart. Please try again later.'
            ]);
        }
    }
}

/**
 * Handle retrieving saved cart for logged in users
 * 
 * @param PDO $db Database connection
 */
function handleGetCart($db) {
    // Check if user is logged in
    if (!is_logged_in()) {
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in'
        ]);
        exit;
    }
    
    try {
        $user_id = $_SESSION['user_id'];
        
        // Get saved cart
        $query = "SELECT cart_data FROM saved_carts WHERE user_id = :user_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $cart = json_decode($result['cart_data'], true);
            
            echo json_encode([
                'success' => true,
                'cart' => $cart
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'cart' => []
            ]);
        }
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        } else {
            error_log('Get cart error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while retrieving your cart. Please try again later.'
            ]);
        }
    }
}

/**
 * Calculate total amount from cart items
 * 
 * @param array $cart Cart items
 * @return float Total amount
 */
function calculateTotal($cart) {
    $total = 0;
    
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}
?>

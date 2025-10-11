<?php
/**
 * Database connection class for Best Buy For Me
 */

// Include configuration
require_once 'config.php';

class Database {
    private $db_path = DB_PATH;
    private $conn;
    
    /**
     * Get database connection
     * @return PDO Database connection
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Create the database file if it doesn't exist
            if (!file_exists($this->db_path)) {
                touch($this->db_path);
                chmod($this->db_path, 0666); // Set appropriate permissions
            }
            
            $this->conn = new PDO("sqlite:" . $this->db_path);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Enable foreign keys for SQLite
            $this->conn->exec("PRAGMA foreign_keys = ON;");
        } catch(PDOException $exception) {
            if (DEBUG_MODE) {
                echo "Connection error: " . $exception->getMessage();
            } else {
                error_log("Connection error: " . $exception->getMessage());
                echo "Unable to connect to the database. Please try again later.";
            }
            die();
        }
        
        return $this->conn;
    }
    
    /**
     * Create tables if they don't exist
     */
    public function setupDatabase() {
        $conn = $this->getConnection();
        
        try {
            // Create users table
            $usersTable = "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fullname TEXT NOT NULL,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                gender TEXT NOT NULL CHECK (gender IN ('male', 'female', 'other')),
                user_type TEXT NOT NULL CHECK (user_type IN ('customer', 'seller', 'admin')) DEFAULT 'customer',
                address TEXT,
                phone TEXT,
                profile_image TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->exec($usersTable);
            
            // Create categories table
            $categoriesTable = "CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT,
                parent_id INTEGER,
                image_url TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
            )";
            $conn->exec($categoriesTable);
            
            // Create products table
            $productsTable = "CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                category TEXT NOT NULL,
                image_url TEXT,
                stock_quantity INTEGER NOT NULL DEFAULT 0,
                seller_id INTEGER,
                rating REAL DEFAULT 0,
                review_count INTEGER DEFAULT 0,
                status TEXT CHECK (status IN ('active', 'inactive', 'out_of_stock')) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL
            )";
            $conn->exec($productsTable);
            
            // Create orders table
            $ordersTable = "CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                total_amount REAL NOT NULL,
                status TEXT CHECK (status IN ('pending', 'processing', 'shipped', 'delivered', 'cancelled')) DEFAULT 'pending',
                shipping_address TEXT NOT NULL,
                billing_address TEXT NOT NULL,
                payment_method TEXT NOT NULL,
                tracking_number TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $conn->exec($ordersTable);
            
            // Create order_items table
            $orderItemsTable = "CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER,
                variant_id INTEGER,
                quantity INTEGER NOT NULL,
                price REAL NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
            )";
            $conn->exec($orderItemsTable);
            
            // Create product_variants table
            $variantsTable = "CREATE TABLE IF NOT EXISTS product_variants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                size TEXT,
                color TEXT,
                price_adjustment REAL DEFAULT 0.00,
                stock_quantity INTEGER NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )";
            $conn->exec($variantsTable);
            
            // Create reviews table
            $reviewsTable = "CREATE TABLE IF NOT EXISTS reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
                title TEXT,
                comment TEXT,
                status TEXT DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $conn->exec($reviewsTable);
            
            // Create password_resets table
            $resetTokensTable = "CREATE TABLE IF NOT EXISTS password_resets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL,
                token TEXT NOT NULL,
                expiry TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $conn->exec($resetTokensTable);
            
            // Insert default admin if not exists
            $checkAdmin = "SELECT id FROM users WHERE email = :email AND user_type = 'admin' LIMIT 1";
            $stmt = $conn->prepare($checkAdmin);
            $stmt->bindParam(':email', $adminEmail);
            $adminEmail = 'admin@bestbuyforme.com';
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $insertAdmin = "INSERT INTO users (fullname, username, email, password, gender, user_type) 
                                VALUES (:fullname, :username, :email, :password, :gender, :user_type)";
                $stmt = $conn->prepare($insertAdmin);
                
                $adminName = 'System Administrator';
                $adminUsername = 'admin';
                $adminEmail = 'admin@bestbuyforme.com';
                $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $adminGender = 'male';
                $adminType = 'admin';
                
                $stmt->bindParam(':fullname', $adminName);
                $stmt->bindParam(':username', $adminUsername);
                $stmt->bindParam(':email', $adminEmail);
                $stmt->bindParam(':password', $adminPassword);
                $stmt->bindParam(':gender', $adminGender);
                $stmt->bindParam(':user_type', $adminType);
                
                $stmt->execute();
            }
            
            return true;
        } catch(PDOException $exception) {
            if (DEBUG_MODE) {
                echo "Setup error: " . $exception->getMessage();
            } else {
                error_log("Setup error: " . $exception->getMessage());
                echo "An error occurred during database setup. Please try again later.";
            }
            return false;
        }
    }
}

// Initialize database
$database = new Database();
$db = $database->getConnection();

// Setup tables on first run
if (!isset($_SESSION['db_setup_done'])) {
    if ($database->setupDatabase()) {
        $_SESSION['db_setup_done'] = true;
    }
}
?>

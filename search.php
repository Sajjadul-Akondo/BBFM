<?php
/**
 * Search products functionality
 */

// Include necessary files
require_once 'config.php';
require_once 'db_connect.php';

// Initialize variables
$search_query = '';
$category = '';
$price_min = null;
$price_max = null;
$results = [];
$total_results = 0;
$error = '';
$has_results = false;

// Get search parameters
if (isset($_GET['query'])) {
    $search_query = sanitize_input($_GET['query']);
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = sanitize_input($_GET['category']);
}

if (isset($_GET['price'])) {
    $price_range = sanitize_input($_GET['price']);
    
    // Parse price range
    if ($price_range === '0-100') {
        $price_min = 0;
        $price_max = 100;
    } else if ($price_range === '100-500') {
        $price_min = 100;
        $price_max = 500;
    } else if ($price_range === '500-1000') {
        $price_min = 500;
        $price_max = 1000;
    } else if ($price_range === '1000+') {
        $price_min = 1000;
        $price_max = PHP_INT_MAX;
    }
}

// Perform search if query is provided
if (!empty($search_query) || !empty($category) || $price_min !== null) {
    try {
        // Start building query
        $query = "SELECT * FROM products WHERE 1=1";
        $params = [];
        
        // Add search conditions
        if (!empty($search_query)) {
            $query .= " AND (name LIKE :search OR description LIKE :search)";
            $search_param = "%{$search_query}%";
            $params[':search'] = $search_param;
        }
        
        // Add category filter
        if (!empty($category)) {
            $query .= " AND category = :category";
            $params[':category'] = $category;
        }
        
        // Add price filter
        if ($price_min !== null && $price_max !== null) {
            $query .= " AND price BETWEEN :price_min AND :price_max";
            $params[':price_min'] = $price_min;
            $params[':price_max'] = $price_max;
        }
        
        // Add order by
        $query .= " ORDER BY name ASC";
        
        // Prepare and execute query
        $stmt = $db->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        // Get results
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_results = count($results);
        $has_results = ($total_results > 0);
        
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            $error = 'Database error: ' . $e->getMessage();
        } else {
            $error = 'An error occurred during search. Please try again later.';
            error_log('Search error: ' . $e->getMessage());
        }
    }
}

// If we're here for AJAX request, return JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => empty($error),
        'error' => $error,
        'total_results' => $total_results,
        'results' => $results
    ]);
    exit;
}

// Alternatively, create a dummy product list for development
// This would normally come from the database
if (count($results) === 0 && !empty($search_query)) {
    $dummy_products = [
        [
            'id' => 1,
            'name' => 'Samsung 55" Smart TV',
            'description' => 'High quality Samsung smart TV with 4K resolution',
            'price' => 699.99,
            'category' => 'electronics',
            'image_url' => 'https://m.media-amazon.com/images/I/71uTeFCiruL._AC_UF1000,1000_QL80_.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Apple iPhone 13',
            'description' => 'Latest iPhone model with advanced camera system',
            'price' => 799.99,
            'category' => 'electronics',
            'image_url' => 'https://m.media-amazon.com/images/I/61VuVU94RnL._AC_UF894,1000_QL80_.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Sony WH-1000XM4',
            'description' => 'Noise cancelling headphones with exceptional sound quality',
            'price' => 349.99,
            'category' => 'electronics',
            'image_url' => 'https://m.media-amazon.com/images/I/51u3JdZhOKL._AC_UF1000,1000_QL80_.jpg'
        ]
    ];
    
    // Simple search in the dummy products
    foreach ($dummy_products as $product) {
        if (stripos($product['name'], $search_query) !== false || 
            stripos($product['description'], $search_query) !== false) {
            
            // Apply category filter if set
            if (!empty($category) && $product['category'] !== $category) {
                continue;
            }
            
            // Apply price filter if set
            if ($price_min !== null && $price_max !== null &&
                ($product['price'] < $price_min || $product['price'] > $price_max)) {
                continue;
            }
            
            $results[] = $product;
        }
    }
    
    $total_results = count($results);
    $has_results = ($total_results > 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Best Buy For Me</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="store.css">
    <link rel="icon" href="assets/logo.svg" type="image/svg+xml">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <nav>
            <div class="logo"><img src="assets/logo.svg" alt="Best Buy For Me Logo" height="60"></div>
            <div class="navbar">
                <a href="index.html">Home</a>
                <a href="store.html">Shop</a>
                <a href="services.html">Services</a>
                <a href="contact.html">Contact</a>
                <button id="drkthm"><i class='bx bxs-moon'></i></button>
                <button id="upin">Login</button>
            </div>
            <div class="menu-toggle">
                <i class='bx bx-menu'></i>
            </div>
        </nav>
    </header>
    
    <main>
        <section class="search-results-header">
            <h1>Search Results</h1>
            <p>
                <?php if (!empty($search_query)): ?>
                    Showing results for: <strong><?php echo htmlspecialchars($search_query); ?></strong>
                <?php else: ?>
                    Showing all products
                <?php endif; ?>
                
                <?php if (!empty($category)): ?>
                    in category: <strong><?php echo htmlspecialchars($category); ?></strong>
                <?php endif; ?>
                
                <?php if ($price_min !== null && $price_max !== null): ?>
                    with price range: <strong>$<?php echo $price_min; ?> - $<?php echo ($price_max === PHP_INT_MAX ? '∞' : $price_max); ?></strong>
                <?php endif; ?>
            </p>
        </section>
        
        <!-- Search and Filter -->
        <section class="search-filter">
            <form id="searchForm" action="search.php" method="get">
                <div class="search-container">
                    <input type="text" name="query" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit"><i class='bx bx-search'></i></button>
                </div>
                <div class="filter-container">
                    <select name="category" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="electronics" <?php echo ($category === 'electronics') ? 'selected' : ''; ?>>Electronics</option>
                        <option value="fashion" <?php echo ($category === 'fashion') ? 'selected' : ''; ?>>Fashion</option>
                        <option value="books" <?php echo ($category === 'books') ? 'selected' : ''; ?>>Books</option>
                        <option value="home" <?php echo ($category === 'home') ? 'selected' : ''; ?>>Home Appliances</option>
                    </select>
                    <select name="price" id="priceFilter">
                        <option value="">All Prices</option>
                        <option value="0-100" <?php echo ($price_min === 0 && $price_max === 100) ? 'selected' : ''; ?>>$0 - $100</option>
                        <option value="100-500" <?php echo ($price_min === 100 && $price_max === 500) ? 'selected' : ''; ?>>$100 - $500</option>
                        <option value="500-1000" <?php echo ($price_min === 500 && $price_max === 1000) ? 'selected' : ''; ?>>$500 - $1000</option>
                        <option value="1000+" <?php echo ($price_min === 1000) ? 'selected' : ''; ?>>$1000+</option>
                    </select>
                    <button type="submit" id="filterButton">Filter</button>
                </div>
            </form>
        </section>
        
        <!-- Search Results -->
        <section class="search-results">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php elseif ($has_results): ?>
                <div class="results-count">
                    <p>Found <?php echo $total_results; ?> result<?php echo ($total_results !== 1) ? 's' : ''; ?></p>
                </div>
                
                <div class="results-grid">
                    <?php foreach ($results as $product): ?>
                        <div class="product-card" data-category="<?php echo htmlspecialchars($product['category']); ?>" data-price="<?php echo $product['price']; ?>">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                            <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <button class="buybtn" onclick="addToCart('<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>, 1)">Add to Cart</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class='bx bx-search-alt' style='font-size: 48px;'></i>
                    <h2>No results found</h2>
                    <p>We couldn't find any products matching your search criteria.</p>
                    <p>Try different keywords or browse our <a href="store.html">product catalog</a>.</p>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Cart Sidebar -->
        <div id="cartSidebar" class="cart-sidebar">
            <div class="close-cart" onclick="toggleCart()">✖</div>
            <h2>Your Cart</h2>
            <div id="cartItems"></div>
            <div id="cartTotal">Total: $0.00</div>
            <button class="checkout-btn" id="checkout-btn">Checkout</button>
        </div>
        
        <!-- Back to Top Button -->
        <button id="backToTop" title="Back to top"><i class='bx bxs-up-arrow-alt'></i></button>
        
        <!-- Cart Notification -->
        <div id="cartNotification" class="notification">
            <span>Item added to cart!</span>
        </div>
    </main>
    
    <footer>
        <div class="information">
            <div class="aboutus">
                <h2>About Us</h2>
                <p>Best Buy For Me is an online store that provides a wide range of products and services. We aim to
                    provide the best quality products and services to our customers.</p>
            </div>
            <div class="footerlinks">
                <h2>Quick Links</h2>
                <a href="index.html">Home</a>
                <a href="store.html">Shop</a>
                <a href="services.html">Services</a>
                <a href="contact.html">Contact</a>
            </div>
            <div class="socialmedia">
                <h2>Follow Us</h2>
                <a href="#"><i class='bx bxl-facebook' style='color:#4267B2'></i></a>
                <a href="#"><i class='bx bxl-twitter' style='color:#1DA1F2'></i></a>
                <a href="#"><i class='bx bxl-instagram' style='color:#E1306C'></i></a>
            </div>
        </div>
        <div class="copywrite">
            <p>© 2025 Best Buy For Me. All Rights Reserved.</p>
        </div>
    </footer>
    
    <script src="main.js"></script>
    <script src="cart.js"></script>
</body>
</html>

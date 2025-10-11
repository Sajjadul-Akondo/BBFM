<?php
/**
 * Product detail page
 */

// Include necessary files
require_once 'config.php';
require_once 'db_connect.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$related_products = [];

// For debugging
error_log("Accessing product.php with id: " . $product_id);

// Validate product ID
if ($product_id <= 0) {
    // Don't redirect, just show a message
    $product_id = 1; // Default to first product
    error_log("Invalid product ID, defaulting to ID 1");
}

try {
    // Get product details
    $query = "SELECT * FROM products WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get related products
        $query = "SELECT * FROM products WHERE category = :category AND id != :id LIMIT 4";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category', $product['category']);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        
        $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Product not found, but don't redirect - use demo data instead
        error_log("Product not found in database with ID: $product_id, using demo data");
        $product = null; // Will trigger demo data generation below
    }
} catch (PDOException $e) {
    if (DEBUG_MODE) {
        echo "Database error: " . $e->getMessage();
    } else {
        error_log("Product detail error: " . $e->getMessage());
    }
}

// If database is not yet set up or in development mode, create mock product data
if (!$product) {
    $category = '';
    $product_data = [];
    
    // Create demo products based on ID
    switch($product_id) {
        case 1:
            $product = [
                'id' => 1,
                'name' => 'Samsung 55" Smart TV',
                'description' => 'Experience stunning 4K resolution and vibrant colors with this Samsung Smart TV. Featuring built-in streaming apps, voice control, and a sleek design that complements any living space. The crystal-clear display brings your favorite movies and shows to life, while the smart functionality lets you access all your entertainment in one place. With multiple HDMI ports and wireless connectivity, it\'s easy to connect all your devices.',
                'price' => 699.99,
                'category' => 'electronics',
                'image_url' => 'https://images.unsplash.com/photo-1593784991095-a205069470b6?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80',
                'stock_quantity' => 15
            ];
            $category = 'electronics';
            break;
        case 2:
            $product = [
                'id' => 2,
                'name' => 'Apple iPhone 13',
                'description' => 'The latest iPhone model with an advanced camera system, powerful A15 Bionic chip, and stunning Super Retina XDR display. Take incredible photos in low light, enjoy all-day battery life, and experience the power of 5G connectivity. The ceramic shield front cover provides 4x better drop protection, and the phone is water resistant up to 6 meters for 30 minutes. Available in multiple colors to match your style.',
                'price' => 799.99,
                'category' => 'electronics',
                'image_url' => 'https://images.unsplash.com/photo-1592286927505-1def25115cf8?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80',
                'stock_quantity' => 25
            ];
            $category = 'electronics';
            break;
        case 3:
            $product = [
                'id' => 3,
                'name' => 'Sony WH-1000XM4',
                'description' => 'Industry-leading noise cancellation headphones with exceptional sound quality. Enjoy a premium listening experience with Edge-AI that restores details in your music, and up to 30 hours of battery life. The speak-to-chat technology automatically reduces volume during conversations, and the touch sensor controls let you pause, play, and skip tracks with simple taps. The multi-point connection allows you to pair with two Bluetooth devices simultaneously.',
                'price' => 349.99,
                'category' => 'electronics',
                'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80',
                'stock_quantity' => 40
            ];
            $category = 'electronics';
            break;
        case 4:
            $product = [
                'id' => 4,
                'name' => 'Nike Air Max 270',
                'description' => 'The Nike Air Max 270 delivers a plush, responsive ride with the first-ever Max Air unit designed specifically for Nike Sportswear. The sleek, running-inspired design includes a breathable mesh upper, supportive overlays, and a stretchy inner sleeve for a snug fit. The large Max Air unit in the heel provides exceptional cushioning, while the foam forefoot adds flexibility and comfort for all-day wear.',
                'price' => 150.00,
                'category' => 'fashion',
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80',
                'stock_quantity' => 30
            ];
            $category = 'fashion';
            break;
        case 5:
            $product = [
                'id' => 5,
                'name' => 'Coffee Maker Deluxe',
                'description' => 'Start your morning right with this programmable coffee maker that brews up to 12 cups of delicious coffee. Set it up the night before with the 24-hour programmable timer, and wake up to freshly brewed coffee. The adjustable brew strength control lets you customize your coffee from regular to bold, while the 1-4 cup setting optimizes flavor when making smaller batches. Includes a reusable gold-tone filter and automatic shut-off for peace of mind.',
                'price' => 89.99,
                'category' => 'home',
                'image_url' => 'https://images.unsplash.com/photo-1510972527921-ce03766a1cf1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80',
                'stock_quantity' => 20
            ];
            $category = 'home';
            break;
        case 6:
            $product = [
                'id' => 6,
                'name' => 'Modern Desk Lamp',
                'description' => 'Illuminate your workspace with this sleek, modern desk lamp featuring adjustable brightness levels and a USB charging port. The flexible arm and rotating head allow you to direct light precisely where you need it, while the energy-efficient LED bulb provides bright, natural light that reduces eye strain. The touch-sensitive controls make it easy to adjust settings, and the compact base saves valuable desk space.',
                'price' => 49.99,
                'category' => 'home',
                'image_url' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80',
                'stock_quantity' => 35
            ];
            $category = 'home';
            break;
        default:
            // Default product if ID doesn't match any preset
            $product = [
                'id' => $product_id,
                'name' => 'Product ' . $product_id,
                'description' => 'This is a detailed description for product ' . $product_id . '. It features multiple paragraphs that highlight the product\'s features, benefits, and specifications. Customers can learn everything they need to know about the product before making a purchase decision.',
                'price' => rand(50, 1000) / 10,
                'category' => 'electronics',
                'image_url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80',
                'stock_quantity' => rand(5, 50)
            ];
            $category = 'electronics';
            break;
    }
    
    // Create related products
    $demo_products = [
        [
            'id' => 1,
            'name' => 'Samsung 55" Smart TV',
            'description' => 'High quality Samsung smart TV with 4K resolution',
            'price' => 699.99,
            'category' => 'electronics',
            'image_url' => 'https://images.unsplash.com/photo-1593784991095-a205069470b6?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80'
        ],
        [
            'id' => 2,
            'name' => 'Apple iPhone 13',
            'description' => 'Latest iPhone model with advanced camera system',
            'price' => 799.99,
            'category' => 'electronics',
            'image_url' => 'https://images.unsplash.com/photo-1592286927505-1def25115cf8?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80'
        ],
        [
            'id' => 3,
            'name' => 'Sony WH-1000XM4',
            'description' => 'Noise cancelling headphones with exceptional sound quality',
            'price' => 349.99,
            'category' => 'electronics',
            'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80'
        ],
        [
            'id' => 4,
            'name' => 'Nike Air Max 270',
            'description' => 'Comfortable and stylish athletic shoes',
            'price' => 150.00,
            'category' => 'fashion',
            'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80'
        ],
        [
            'id' => 5,
            'name' => 'Coffee Maker Deluxe',
            'description' => 'Programmable coffee maker for perfect morning brew',
            'price' => 89.99,
            'category' => 'home',
            'image_url' => 'https://images.unsplash.com/photo-1510972527921-ce03766a1cf1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80'
        ],
        [
            'id' => 6,
            'name' => 'Modern Desk Lamp',
            'description' => 'Adjustable desk lamp with multiple brightness levels',
            'price' => 49.99,
            'category' => 'home',
            'image_url' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80'
        ]
    ];
    
    // Find related products that match the category
    foreach ($demo_products as $demo_product) {
        if ($demo_product['category'] == $category && $demo_product['id'] != $product_id) {
            $related_products[] = $demo_product;
        }
        
        // Limit to 4 related products
        if (count($related_products) >= 4) {
            break;
        }
    }
}

// Get average rating (mock data for now)
$rating = rand(35, 50) / 10; // Random rating between 3.5 and 5.0
$review_count = rand(10, 100); // Random number of reviews
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Best Buy For Me</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="store.css">
    <link rel="stylesheet" href="product.css">
    <link rel="icon" href="assets/logo.svg" type="image/svg+xml">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <nav>
            <div class="logo"><img src="assets/logo.svg" alt="Best Buy For Me Logo" height="60"></div>
            <div class="navbar">
                <a href="index.html">Home</a>
                <a href="store.html" class="active">Shop</a>
                <a href="services.html">Services</a>
                <a href="contact.html">Contact</a>
                <button id="drkthm"><i class='bx bxs-moon'></i></button>
                <button id="upin" onclick="window.location.href='login.php'">Login</button>
                <button id="cart-icon" onclick="toggleCart()">
                    <i class='bx bx-cart'></i>
                    <span id="cartCount" class="cart-count">0</span>
                </button>
            </div>
            <div class="menu-toggle">
                <i class='bx bx-menu'></i>
            </div>
        </nav>
    </header>
    
    <main>
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="index.html">Home</a> &gt;
            <a href="store.html">Shop</a> &gt;
            <a href="store.html?category=<?php echo htmlspecialchars($product['category']); ?>"><?php echo ucfirst(htmlspecialchars($product['category'])); ?></a> &gt;
            <span class="current"><?php echo htmlspecialchars($product['name']); ?></span>
        </div>
        
        <!-- Product Detail Section -->
        <section class="product-detail">
            <div class="product-images">
                <div class="main-image">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="thumbnail-images">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Thumbnail 1" class="active">
                    <!-- More thumbnails would be here in a real product -->
                </div>
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-rating">
                    <?php
                    // Display star rating
                    $full_stars = floor($rating);
                    $half_star = $rating - $full_stars >= 0.5;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                    
                    for ($i = 0; $i < $full_stars; $i++) {
                        echo '<i class="bx bxs-star"></i>';
                    }
                    
                    if ($half_star) {
                        echo '<i class="bx bxs-star-half"></i>';
                    }
                    
                    for ($i = 0; $i < $empty_stars; $i++) {
                        echo '<i class="bx bx-star"></i>';
                    }
                    ?>
                    <span class="rating-text"><?php echo $rating; ?> (<?php echo $review_count; ?> reviews)</span>
                </div>
                
                <div class="product-price">
                    <span class="current-price">$<?php echo number_format($product['price'], 2); ?></span>
                    <?php if (rand(0, 1) == 1): // Randomly show sale price for demo ?>
                    <span class="original-price">$<?php echo number_format($product['price'] * 1.2, 2); ?></span>
                    <span class="discount">Save 20%</span>
                    <?php endif; ?>
                </div>
                
                <div class="availability">
                    <?php if ($product['stock_quantity'] > 0): ?>
                    <span class="in-stock"><i class='bx bx-check'></i> In Stock</span>
                    <span class="stock-quantity"><?php echo $product['stock_quantity']; ?> available</span>
                    <?php else: ?>
                    <span class="out-of-stock"><i class='bx bx-x'></i> Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-description">
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <div class="product-variants">
                    <div class="variant-group">
                        <label>Color:</label>
                        <div class="color-options">
                            <span class="color-option active" style="background-color: #333;" data-color="Black"></span>
                            <span class="color-option" style="background-color: #f1f1f1;" data-color="White"></span>
                            <span class="color-option" style="background-color: #0057b8;" data-color="Blue"></span>
                            <span class="color-option" style="background-color: #d32f2f;" data-color="Red"></span>
                        </div>
                    </div>
                    
                    <div class="variant-group">
                        <label>Size:</label>
                        <div class="size-options">
                            <span class="size-option" data-size="S">S</span>
                            <span class="size-option active" data-size="M">M</span>
                            <span class="size-option" data-size="L">L</span>
                            <span class="size-option" data-size="XL">XL</span>
                        </div>
                    </div>
                </div>
                
                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn" onclick="updateQuantityInput(-1)">-</button>
                        <input type="number" id="productQuantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                        <button class="qty-btn" onclick="updateQuantityInput(1)">+</button>
                    </div>
                    
                    <button class="add-to-cart-btn" onclick="addToCartFromDetail('<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['price']; ?>)">
                        <i class='bx bx-cart-add'></i> Add to Cart
                    </button>
                    
                    <button class="wishlist-btn" onclick="toggleWishlist(this, <?php echo $product['id']; ?>)">
                        <i class='bx bx-heart'></i> <span>Add to Wishlist</span>
                    </button>
                </div>
                
                <div class="product-delivery-info">
                    <div class="delivery-option">
                        <i class='bx bxs-truck'></i>
                        <div>
                            <h4>Free Shipping</h4>
                            <p>On orders over $50</p>
                        </div>
                    </div>
                    <div class="delivery-option">
                        <i class='bx bxs-package'></i>
                        <div>
                            <h4>Easy Returns</h4>
                            <p>30 day return policy</p>
                        </div>
                    </div>
                </div>
                
                <div class="shipping-info">
                    <p><i class='bx bxs-truck'></i> Free shipping on orders over $50</p>
                    <p><i class='bx bx-package'></i> 30-day easy returns</p>
                </div>
                
                <div class="social-share">
                    <span>Share: </span>
                    <a href="#" class="social-icon"><i class='bx bxl-facebook'></i></a>
                    <a href="#" class="social-icon"><i class='bx bxl-twitter'></i></a>
                    <a href="#" class="social-icon"><i class='bx bxl-pinterest'></i></a>
                    <a href="#" class="social-icon"><i class='bx bxl-instagram'></i></a>
                </div>
            </div>
        </section>
        
        <!-- Product Tabs Section -->
        <section class="product-tabs">
            <div class="tabs">
                <button class="tab-btn active" onclick="openTab(event, 'details')">Details</button>
                <button class="tab-btn" onclick="openTab(event, 'specifications')">Specifications</button>
                <button class="tab-btn" onclick="openTab(event, 'reviews')">Reviews</button>
            </div>
            
            <div id="details" class="tab-content active">
                <h3>Product Details</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <ul class="feature-list">
                    <li><i class='bx bx-check'></i> Premium quality materials</li>
                    <li><i class='bx bx-check'></i> Durable construction</li>
                    <li><i class='bx bx-check'></i> Modern design</li>
                    <li><i class='bx bx-check'></i> Easy to use</li>
                    <li><i class='bx bx-check'></i> Energy efficient</li>
                </ul>
            </div>
            
            <div id="specifications" class="tab-content">
                <h3>Technical Specifications</h3>
                <table class="specs-table">
                    <tr>
                        <th>Dimensions</th>
                        <td>12.5 x 8.3 x 2.4 inches</td>
                    </tr>
                    <tr>
                        <th>Weight</th>
                        <td>2.5 pounds</td>
                    </tr>
                    <tr>
                        <th>Material</th>
                        <td>Aluminum and plastic</td>
                    </tr>
                    <tr>
                        <th>Color Options</th>
                        <td>Black, Silver, Blue</td>
                    </tr>
                    <tr>
                        <th>Warranty</th>
                        <td>1 year limited warranty</td>
                    </tr>
                    <tr>
                        <th>Model Number</th>
                        <td>BBS-<?php echo $product['id']; ?>-2025</td>
                    </tr>
                </table>
            </div>
            
            <div id="reviews" class="tab-content">
                <h3>Customer Reviews</h3>
                <div class="review-summary">
                    <div class="overall-rating">
                        <span class="big-rating"><?php echo $rating; ?></span>
                        <div class="stars">
                            <?php
                            // Display star rating again
                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '<i class="bx bxs-star"></i>';
                            }
                            
                            if ($half_star) {
                                echo '<i class="bx bxs-star-half"></i>';
                            }
                            
                            for ($i = 0; $i < $empty_stars; $i++) {
                                echo '<i class="bx bx-star"></i>';
                            }
                            ?>
                        </div>
                        <span class="review-count"><?php echo $review_count; ?> reviews</span>
                    </div>
                    <div class="rating-bars">
                        <?php
                        // Generate mock rating distribution
                        $five_star = rand(50, 80);
                        $four_star = rand(10, 30);
                        $three_star = rand(5, 15);
                        $two_star = rand(1, 5);
                        $one_star = rand(0, 3);
                        
                        // Ensure percentages add up to 100
                        $total = $five_star + $four_star + $three_star + $two_star + $one_star;
                        $five_star = round($five_star * 100 / $total);
                        $four_star = round($four_star * 100 / $total);
                        $three_star = round($three_star * 100 / $total);
                        $two_star = round($two_star * 100 / $total);
                        $one_star = 100 - $five_star - $four_star - $three_star - $two_star;
                        ?>
                        <div class="rating-bar">
                            <span class="stars">5 <i class='bx bxs-star'></i></span>
                            <div class="bar"><div class="fill" style="width: <?php echo $five_star; ?>%"></div></div>
                            <span class="percentage"><?php echo $five_star; ?>%</span>
                        </div>
                        <div class="rating-bar">
                            <span class="stars">4 <i class='bx bxs-star'></i></span>
                            <div class="bar"><div class="fill" style="width: <?php echo $four_star; ?>%"></div></div>
                            <span class="percentage"><?php echo $four_star; ?>%</span>
                        </div>
                        <div class="rating-bar">
                            <span class="stars">3 <i class='bx bxs-star'></i></span>
                            <div class="bar"><div class="fill" style="width: <?php echo $three_star; ?>%"></div></div>
                            <span class="percentage"><?php echo $three_star; ?>%</span>
                        </div>
                        <div class="rating-bar">
                            <span class="stars">2 <i class='bx bxs-star'></i></span>
                            <div class="bar"><div class="fill" style="width: <?php echo $two_star; ?>%"></div></div>
                            <span class="percentage"><?php echo $two_star; ?>%</span>
                        </div>
                        <div class="rating-bar">
                            <span class="stars">1 <i class='bx bxs-star'></i></span>
                            <div class="bar"><div class="fill" style="width: <?php echo $one_star; ?>%"></div></div>
                            <span class="percentage"><?php echo $one_star; ?>%</span>
                        </div>
                    </div>
                </div>
                
                <div class="review-list">
                    <?php
                    // Generate mock reviews
                    $reviewers = ['John D.', 'Sarah M.', 'Michael P.', 'Emily R.', 'David K.'];
                    $review_dates = ['May 2, 2025', 'April 28, 2025', 'April 15, 2025', 'March 30, 2025', 'March 22, 2025'];
                    $review_titles = ['Great product!', 'Exceeded expectations', 'Worth every penny', 'Highly recommend', 'Very satisfied'];
                    $review_texts = [
                        'This product is exactly what I was looking for. The quality is excellent and it arrived quickly. Would definitely purchase again.',
                        'I\'ve been using this for a few weeks now and I\'m very happy with it. It works perfectly and looks great too.',
                        'Fantastic product at a reasonable price. The setup was easy and the performance has been flawless so far.',
                        'I bought this as a gift and the recipient absolutely loves it. The design is sleek and modern, and the functionality is top-notch.',
                        'After researching many options, I decided on this one and have no regrets. It\'s reliable, well-made, and does exactly what it claims to do.'
                    ];
                    
                    // Display 3 reviews
                    for ($i = 0; $i < 3; $i++) {
                        $review_rating = rand(4, 5);
                        echo '<div class="review-item">';
                        echo '<div class="review-header">';
                        echo '<div class="reviewer-info">';
                        echo '<span class="reviewer-name">' . $reviewers[$i] . '</span>';
                        echo '<span class="review-date">' . $review_dates[$i] . '</span>';
                        echo '</div>';
                        echo '<div class="review-rating">';
                        for ($j = 0; $j < 5; $j++) {
                            if ($j < $review_rating) {
                                echo '<i class="bx bxs-star"></i>';
                            } else {
                                echo '<i class="bx bx-star"></i>';
                            }
                        }
                        echo '</div>';
                        echo '</div>';
                        echo '<h4 class="review-title">' . $review_titles[$i] . '</h4>';
                        echo '<p class="review-text">' . $review_texts[$i] . '</p>';
                        echo '</div>';
                    }
                    ?>
                    
                    <button class="more-reviews-btn">Load More Reviews</button>
                </div>
                
                <div class="write-review">
                    <h3>Write a Review</h3>
                    <form id="reviewForm">
                        <div class="rating-select">
                            <span>Your Rating:</span>
                            <div class="star-rating">
                                <i class='bx bx-star' data-rating="1"></i>
                                <i class='bx bx-star' data-rating="2"></i>
                                <i class='bx bx-star' data-rating="3"></i>
                                <i class='bx bx-star' data-rating="4"></i>
                                <i class='bx bx-star' data-rating="5"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="review-title">Review Title</label>
                            <input type="text" id="review-title" placeholder="Give your review a title">
                        </div>
                        <div class="form-group">
                            <label for="review-text">Your Review</label>
                            <textarea id="review-text" rows="5" placeholder="Write your review here..."></textarea>
                        </div>
                        <button type="submit" class="submit-review-btn">Submit Review</button>
                    </form>
                </div>
            </div>
        </section>
        
        <!-- Related Products Section -->
        <section class="related-products">
            <h2>Related Products</h2>
            <div class="related-items-grid">
                <?php foreach ($related_products as $related): ?>
                <div class="item">
                    <a href="product.php?id=<?php echo $related['id']; ?>">
                        <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                        <p>$<?php echo number_format($related['price'], 2); ?></p>
                    </a>
                    <button class="buybtn" onclick="addToCart('<?php echo htmlspecialchars(addslashes($related['name'])); ?>', <?php echo $related['price']; ?>, 1)">Add to Cart</button>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        
        <!-- Recently Viewed Section -->
        <section class="recently-viewed">
            <h2>Recently Viewed</h2>
            <div class="recent-items-grid">
                <!-- This would be populated dynamically based on user browsing history -->
                <div class="item">
                    <a href="product.php?id=3">
                        <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80" alt="Sony WH-1000XM4">
                        <h3>Sony WH-1000XM4</h3>
                        <p>$349.99</p>
                    </a>
                </div>
                <div class="item">
                    <a href="product.php?id=2">
                        <img src="https://images.unsplash.com/photo-1592286927505-1def25115cf8?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80" alt="Apple iPhone 13">
                        <h3>Apple iPhone 13</h3>
                        <p>$799.99</p>
                    </a>
                </div>
            </div>
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
    <script>
        // Product page specific JavaScript
        function openTab(evt, tabName) {
            // Hide all tab content
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all tab buttons
            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => btn.classList.remove('active'));
            
            // Show the selected tab content and mark button as active
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
        
        // Update quantity input
        function updateQuantityInput(change) {
            const input = document.getElementById('productQuantity');
            let newValue = parseInt(input.value) + change;
            
            // Ensure value is between min and max
            if (newValue < parseInt(input.min)) {
                newValue = parseInt(input.min);
            } else if (input.max && newValue > parseInt(input.max)) {
                newValue = parseInt(input.max);
            }
            
            input.value = newValue;
        }
        
        // Add to cart from product detail page
        function addToCartFromDetail(name, price) {
            const quantity = parseInt(document.getElementById('productQuantity').value);
            
            // Get selected color and size
            const selectedColor = document.querySelector('.color-option.active')?.getAttribute('data-color') || 'Default';
            const selectedSize = document.querySelector('.size-option.active')?.getAttribute('data-size') || 'One Size';
            
            // Add product with variants to cart
            const variantInfo = `${name} - ${selectedColor}, ${selectedSize}`;
            addToCart(variantInfo, price, quantity);
            
            // Show notification
            const notification = document.createElement('div');
            notification.className = 'add-to-cart-notification';
            notification.innerHTML = `
                <i class='bx bx-check-circle'></i>
                <div>
                    <p>Added to cart!</p>
                    <p class="product-name">${variantInfo}</p>
                </div>
                <button onclick="this.parentElement.remove()">&times;</button>
            `;
            document.body.appendChild(notification);
            
            // Auto remove notification after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        
        // Quantity input functionality
        function updateQuantityInput(change) {
            const input = document.getElementById('productQuantity');
            let value = parseInt(input.value) + change;
            
            // Ensure value is within min and max range
            const min = parseInt(input.getAttribute('min') || 1);
            const max = parseInt(input.getAttribute('max') || 99);
            
            value = Math.max(min, Math.min(value, max));
            input.value = value;
        }
        
        // Toggle wishlist functionality
        function toggleWishlist(button, productId) {
            const icon = button.querySelector('i');
            const text = button.querySelector('span');
            
            if (icon.classList.contains('bx-heart')) {
                // Add to wishlist
                icon.classList.remove('bx-heart');
                icon.classList.add('bxs-heart');
                text.textContent = 'Added to Wishlist';
                button.classList.add('in-wishlist');
                
                // Could store in localStorage or send to server
                const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
                if (!wishlist.includes(productId)) {
                    wishlist.push(productId);
                    localStorage.setItem('wishlist', JSON.stringify(wishlist));
                }
            } else {
                // Remove from wishlist
                icon.classList.remove('bxs-heart');
                icon.classList.add('bx-heart');
                text.textContent = 'Add to Wishlist';
                button.classList.remove('in-wishlist');
                
                // Remove from localStorage
                const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
                const index = wishlist.indexOf(productId);
                if (index > -1) {
                    wishlist.splice(index, 1);
                    localStorage.setItem('wishlist', JSON.stringify(wishlist));
                }
            }
        }
        
        // Tab functionality
        function openTab(evt, tabName) {
            // Hide all tab content
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Remove active class from all tab buttons
            const tabButtons = document.getElementsByClassName('tab-btn');
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }
            
            // Show the specific tab content and add active class to the button
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
        
        // Color and size selection functionality
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.color-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
        
        document.querySelectorAll('.size-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.size-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
        
        // Star rating functionality for review form
        document.querySelectorAll('.star-rating i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                
                // Reset all stars
                document.querySelectorAll('.star-rating i').forEach(s => {
                    s.classList.remove('bxs-star');
                    s.classList.add('bx-star');
                });
                
                // Fill stars up to the selected rating
                document.querySelectorAll(`.star-rating i[data-rating="${rating}"], .star-rating i[data-rating="${rating}"] ~ i`).forEach(s => {
                    s.classList.remove('bxs-star');
                    s.classList.add('bx-star');
                });
                
                document.querySelectorAll(`.star-rating i[data-rating="${rating}"], .star-rating i[data-rating<="${rating}"]`).forEach(s => {
                    s.classList.remove('bx-star');
                    s.classList.add('bxs-star');
                });
            });
        });
        
        // Review form submission
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your review! It will be published after moderation.');
            this.reset();
            document.querySelectorAll('.star-rating i').forEach(s => {
                s.classList.remove('bxs-star');
                s.classList.add('bx-star');
            });
        });
        
        // More reviews button
        document.querySelector('.more-reviews-btn').addEventListener('click', function() {
            alert('Loading more reviews feature coming soon!');
        });
    </script>
</body>
</html>
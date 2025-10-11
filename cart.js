// Cart functionality

// Cart state
let cart = [];

// Load cart from localStorage on page load
document.addEventListener('DOMContentLoaded', () => {
    loadCart();
    updateCartDisplay();
});

// Toggle cart sidebar
function toggleCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    if (cartSidebar) {
        cartSidebar.classList.toggle('open');
    }
}

// Add item to cart
function addToCart(itemName, price, quantity = 1) {
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(item => item.name === itemName);
    
    if (existingItemIndex !== -1) {
        // Update quantity if item exists
        cart[existingItemIndex].quantity += quantity;
    } else {
        // Add new item to cart
        cart.push({
            name: itemName,
            price: price,
            quantity: quantity
        });
    }
    
    // Save cart to localStorage
    saveCart();
    
    // Update cart display
    updateCartDisplay();
    
    // Show notification
    showNotification();
}

// Remove item from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    saveCart();
    updateCartDisplay();
}

// Update item quantity
function updateQuantity(index, change) {
    const newQty = cart[index].quantity + change;
    
    if (newQty <= 0) {
        // Remove item if quantity is 0 or less
        removeFromCart(index);
    } else {
        // Update quantity
        cart[index].quantity = newQty;
        saveCart();
        updateCartDisplay();
    }
}

// Save cart to localStorage
function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

// Load cart from localStorage
function loadCart() {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
    }
    updateCartCount();
}

// Update cart count badge
function updateCartCount() {
    const cartCountElements = document.querySelectorAll('.cart-count');
    if (cartCountElements.length > 0) {
        const itemCount = cart.reduce((total, item) => total + item.quantity, 0);
        
        cartCountElements.forEach(element => {
            element.textContent = itemCount;
            
            // Show/hide the badge based on count
            if (itemCount > 0) {
                element.style.display = 'flex';
            } else {
                element.style.display = 'none';
            }
        });
    }
}

// Update cart display in sidebar
function updateCartDisplay() {
    const cartItemsElement = document.getElementById('cartItems');
    const cartTotalElement = document.getElementById('cartTotal');
    
    if (!cartItemsElement || !cartTotalElement) return;
    
    // Clear current items
    cartItemsElement.innerHTML = '';
    
    // Calculate total
    let total = 0;
    
    if (cart.length === 0) {
        // Show empty cart message
        cartItemsElement.innerHTML = '<p class="empty-cart-message">Your cart is empty</p>';
    } else {
        // Add each item to the cart display
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            const cartItemElement = document.createElement('div');
            cartItemElement.className = 'cart-item';
            cartItemElement.innerHTML = `
                <div class="cart-item-info">
                    <div class="cart-item-title">${item.name}</div>
                    <div class="cart-item-price">$${item.price.toFixed(2)}</div>
                </div>
                <div class="cart-item-actions">
                    <button class="qty-btn" onclick="updateQuantity(${index}, -1)">-</button>
                    <span class="item-qty">${item.quantity}</span>
                    <button class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                    <button class="remove-item" onclick="removeFromCart(${index})">
                        <i class='bx bx-trash'></i>
                    </button>
                </div>
            `;
            
            cartItemsElement.appendChild(cartItemElement);
        });
    }
    
    // Update total
    cartTotalElement.textContent = `Total: $${total.toFixed(2)}`;
    
    // Setup checkout button
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = cart.length === 0;
        checkoutBtn.onclick = handleCheckout;
    }
}

// Show notification when item is added to cart
function showNotification() {
    const notification = document.getElementById('cartNotification');
    if (notification) {
        notification.classList.add('show');
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }
}

// Handle checkout process
function handleCheckout() {
    if (cart.length === 0) {
        alert('Your cart is empty.');
        return;
    }
    
    // Redirect to checkout page or process the order
    // Check if user is logged in
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'checkout',
            cart: cart
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear cart after successful checkout
            cart = [];
            saveCart();
            updateCartDisplay();
            
            // Show success message or redirect
            alert('Order placed successfully! Thank you for your purchase.');
        } else if (data.login_required) {
            // Redirect to login page
            window.location.href = 'login.html';
        } else {
            // Show error message
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error during checkout:', error);
        alert('There was a problem processing your order. Please try again.');
    });
}

// Clear cart completely
function clearCart() {
    cart = [];
    saveCart();
    updateCartDisplay();
}

// Initialize checkout button
document.addEventListener('DOMContentLoaded', function() {
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', handleCheckout);
    }
});

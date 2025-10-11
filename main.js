// DOM Elements
const menuToggle = document.querySelector('.menu-toggle');
const navbar = document.querySelector('.navbar');
const darkThemeBtn = document.getElementById('drkthm');
const backToTopBtn = document.getElementById('backToTop');
const loginBtn = document.getElementById('upin');
const newsletterForm = document.getElementById('newsletterForm');
const shopBtn = document.getElementById('shopBtn');
const showMoreBtn = document.getElementById('showBtn');

// Toggle mobile menu
menuToggle?.addEventListener('click', () => {
    navbar.classList.toggle('show');
});

// Toggle dark theme
darkThemeBtn?.addEventListener('click', () => {
    document.body.classList.toggle('dark-theme');
    
    // Change icon based on theme
    const moonIcon = darkThemeBtn.querySelector('i');
    if (document.body.classList.contains('dark-theme')) {
        localStorage.setItem('theme', 'dark');
        moonIcon.classList.remove('bxs-moon');
        moonIcon.classList.add('bxs-sun');
    } else {
        localStorage.setItem('theme', 'light');
        moonIcon.classList.remove('bxs-sun');
        moonIcon.classList.add('bxs-moon');
    }
});

// Check for saved theme preference
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        
        // Update moon icon to sun icon
        if (darkThemeBtn) {
            const moonIcon = darkThemeBtn.querySelector('i');
            if (moonIcon) {
                moonIcon.classList.remove('bxs-moon');
                moonIcon.classList.add('bxs-sun');
            }
        }
    }
    
    // Initialize back to top button
    window.addEventListener('scroll', toggleBackToTopButton);
    
    // Initialize category buttons if they exist
    const categoryBtns = document.querySelectorAll('.category-btn');
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            categoryBtns.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update deals based on selected category
            const category = this.dataset.category;
            filterDealsByCategory(category);
        });
    });
    
    // Initialize filter button if it exists
    const filterButton = document.getElementById('filterButton');
    if (filterButton) {
        filterButton.addEventListener('click', applyFilters);
    }
    
    // For services page - service buttons
    const serviceButtons = document.querySelectorAll('.service-btn');
    serviceButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Service details coming soon!');
        });
    });
    
    // For services page - plan buttons
    const planButtons = document.querySelectorAll('.plan-btn');
    planButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            alert('Membership plan subscription coming soon!');
        });
    });
    
    // For contact page - Live chat
    const liveChatBtn = document.getElementById('liveChatBtn');
    if (liveChatBtn) {
        liveChatBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleLiveChat();
        });
    }
    
    // Contact form submission
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });
    }
});

// Back to top button
function toggleBackToTopButton() {
    if (window.scrollY > 300) {
        backToTopBtn.classList.add('visible');
    } else {
        backToTopBtn.classList.remove('visible');
    }
}

backToTopBtn?.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Login button
loginBtn?.addEventListener('click', () => {
    window.location.href = 'login.php';
});

// Newsletter form
newsletterForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const emailInput = newsletterForm.querySelector('input[type="email"]');
    if (emailInput.value) {
        alert('Thank you for subscribing to our newsletter!');
        emailInput.value = '';
    }
});

// Shop button
shopBtn?.addEventListener('click', () => {
    window.location.href = 'store.html';
});

// Show more products button
showMoreBtn?.addEventListener('click', () => {
    window.location.href = 'store.html';
});

// Slider functions for store page
let currentSlide = 0;

function showSlide(n) {
    const slides = document.querySelector('.slides');
    if (!slides) return;
    
    currentSlide = n;
    slides.style.transform = `translateX(-${currentSlide * 33.333}%)`;
}

function nextSlide() {
    const slides = document.querySelector('.slides');
    if (!slides) return;
    
    currentSlide = (currentSlide + 1) % 3;
    showSlide(currentSlide);
}

function prevSlide() {
    const slides = document.querySelector('.slides');
    if (!slides) return;
    
    currentSlide = (currentSlide - 1 + 3) % 3;
    showSlide(currentSlide);
}

// Auto slide
if (document.querySelector('.hero-slider')) {
    setInterval(nextSlide, 5000);
}

// Horizontal scrolling for product sections
function scrollRight(className) {
    const container = document.querySelector(`.${className}`);
    if (container) {
        container.scrollBy({
            left: 330,
            behavior: 'smooth'
        });
    }
}

function scrollLeft(className) {
    const container = document.querySelector(`.${className}`);
    if (container) {
        container.scrollBy({
            left: -330,
            behavior: 'smooth'
        });
    }
}

// Filter deals by category
function filterDealsByCategory(category) {
    const dealItems = document.querySelectorAll('.deal-item');
    if (!dealItems.length) return;
    
    dealItems.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Apply product filters
function applyFilters() {
    const categoryFilter = document.getElementById('categoryFilter').value;
    const priceFilter = document.getElementById('priceFilter').value;
    
    const products = document.querySelectorAll('.item, .deal-item');
    products.forEach(product => {
        let showItem = true;
        
        // Apply category filter
        if (categoryFilter && product.dataset.category !== categoryFilter) {
            showItem = false;
        }
        
        // Apply price filter
        if (priceFilter && showItem) {
            const price = parseFloat(product.dataset.price);
            if (priceFilter === '0-100' && (price < 0 || price > 100)) {
                showItem = false;
            } else if (priceFilter === '100-500' && (price < 100 || price > 500)) {
                showItem = false;
            } else if (priceFilter === '500-1000' && (price < 500 || price > 1000)) {
                showItem = false;
            } else if (priceFilter === '1000+' && price < 1000) {
                showItem = false;
            }
        }
        
        // Show or hide product
        product.style.display = showItem ? 'block' : 'none';
    });
}

// Live chat functions
function toggleLiveChat() {
    const chatWidget = document.getElementById('liveChatWidget');
    if (chatWidget) {
        chatWidget.classList.toggle('open');
    }
}

function sendChatMessage() {
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    
    if (chatInput && chatInput.value.trim() !== '') {
        // Add user message
        const userMessage = document.createElement('div');
        userMessage.className = 'message user';
        userMessage.innerHTML = `<span class="message-content">${chatInput.value}</span>`;
        chatMessages.appendChild(userMessage);
        
        // Clear input
        const messageText = chatInput.value;
        chatInput.value = '';
        
        // Simulate agent response
        setTimeout(() => {
            const agentMessage = document.createElement('div');
            agentMessage.className = 'message agent';
            
            let response = "Thank you for your message. An agent will respond shortly.";
            
            // Some basic automated responses
            if (messageText.toLowerCase().includes('hello') || 
                messageText.toLowerCase().includes('hi')) {
                response = "Hello! How can I help you today?";
            } else if (messageText.toLowerCase().includes('return')) {
                response = "Our return policy allows returns within 30 days of purchase. Would you like more details?";
            } else if (messageText.toLowerCase().includes('shipping')) {
                response = "We offer free shipping on orders over $50. Standard shipping takes 3-5 business days.";
            } else if (messageText.toLowerCase().includes('payment')) {
                response = "We accept all major credit cards, PayPal, and Apple Pay.";
            }
            
            agentMessage.innerHTML = `<span class="message-content">${response}</span>`;
            chatMessages.appendChild(agentMessage);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 1000);
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

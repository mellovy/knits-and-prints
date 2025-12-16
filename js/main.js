// Global variables
let currentLightboxIndex = 0;
let currentProductId = null;

// Open lightbox
function openLightbox(productId) {
    const index = products.findIndex(p => p.id == productId);
    if (index === -1) return;
    
    currentLightboxIndex = index;
    currentProductId = productId;
    updateLightbox();
    
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close lightbox
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Change lightbox image
function changeLightbox(direction) {
    currentLightboxIndex += direction;
    
    if (currentLightboxIndex < 0) {
        currentLightboxIndex = products.length - 1;
    } else if (currentLightboxIndex >= products.length) {
        currentLightboxIndex = 0;
    }
    
    currentProductId = products[currentLightboxIndex].id;
    updateLightbox();
}

// Update lightbox content
function updateLightbox() {
    const product = products[currentLightboxIndex];
    
    document.getElementById('lightbox-image').src = '../uploads/products/' + product.image;
    document.getElementById('lightbox-name').textContent = product.name;
    document.getElementById('lightbox-price').textContent = '₱' + parseFloat(product.price).toFixed(2);
    document.getElementById('lightbox-stock').textContent = 'Stock: ' + product.stock;
}

// Add to cart from lightbox
function addToCartFromLightbox() {
    if (!currentProductId) return;
    
    fetch('process/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + currentProductId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            updateCartCount();
            
            // Show success message
            showNotification('Added to cart! 🛒');
            
            // Close lightbox
            closeLightbox();
        } else {
            alert(data.message || 'Failed to add to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4caf50;
        color: white;
        padding: 15px 25px;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

// Update cart count
function updateCartCount() {
    fetch('process/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.count;
                if (data.count > 0) {
                    cartCount.style.display = 'flex';
                } else {
                    cartCount.style.display = 'none';
                }
            }
        });
}

// Update cart item quantity
function updateQuantity(productId, change) {
    fetch('process/update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&action=update&change=' + change
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update quantity');
        }
    });
}

// Remove item from cart
function removeFromCart(productId) {
    if (!confirm('Remove this item from cart?')) return;
    
    fetch('process/update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&action=remove'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to remove item');
        }
    });
}

// Payment method selection
function selectPaymentMethod(method) {
    document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    event.target.closest('.payment-option').classList.add('selected');
    
    // Show payment details
    document.querySelectorAll('.payment-details').forEach(detail => {
        detail.style.display = 'none';
    });
    
    const detailsId = method + '-details';
    const details = document.getElementById(detailsId);
    if (details) {
        details.style.display = 'block';
    }
}

// Keyboard navigation for lightbox
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('lightbox');
    if (lightbox && lightbox.classList.contains('active')) {
        if (e.key === 'ArrowLeft') {
            changeLightbox(-1);
        } else if (e.key === 'ArrowRight') {
            changeLightbox(1);
        } else if (e.key === 'Escape') {
            closeLightbox();
        }
    }
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
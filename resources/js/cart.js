import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

// Configure toastr
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 3000,
    extendedTimeOut: 1000,
    preventDuplicates: true,
    newestOnTop: true,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut'
};

// Format price to currency
function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

// Update cart total
function updateCartTotal() {
    const cartItems = document.querySelectorAll('.cart-item');
    let total = 0;

    cartItems.forEach(item => {
        const priceText = item.querySelector('.text-indigo-600').textContent;
        const price = parseFloat(priceText.replace('$', '').replace(',', ''));
        total += price;
    });

    const cartTotal = document.getElementById('cart-total');
    if (cartTotal) {
        cartTotal.textContent = formatPrice(total);
    }

    // If no items left, show empty cart message
    if (cartItems.length === 0) {
        const cartContainer = document.getElementById('cart-container');
        if (cartContainer) {
            cartContainer.innerHTML = `
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-xl text-gray-500 mb-4">Your cart is empty</p>
                    <a href="/productlisting" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Browse Products
                    </a>
                </div>
            `;
        }
    }
}

// Add to cart function that works for both products and bundles
export function addToCart(type, id) {
    // Disable the button to prevent double clicks
    const button = event.target;
    button.disabled = true;
    
    // Add loading state to button
    const originalText = button.textContent;
    button.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Adding...</span>';
    
    // Send AJAX request to add item to cart
    fetch(`/cart/add/${type}/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification
            toastr.success(data.message, 'Success');
            
            // Optional: Update cart count in navigation if you have one
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = parseInt(cartCount.textContent || '0') + 1;
            }
        } else {
            // Show error notification
            toastr.error(data.message || 'Failed to add item to cart', 'Error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('An error occurred while adding item to cart', 'Error');
    })
    .finally(() => {
        // Re-enable button and restore original text
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

export function removeFromCart(cartItemId) {
    // Show confirmation dialog
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    // Find the cart item element
    const cartItem = document.getElementById(`cart-item-${cartItemId}`);
    if (!cartItem) return;

    // Add loading state
    cartItem.style.opacity = '0.5';
    cartItem.style.pointerEvents = 'none';

    // Send AJAX request to remove item
    fetch(`/cart/remove/${cartItemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animate item removal
            cartItem.style.transition = 'all 0.3s ease-out';
            cartItem.style.transform = 'translateX(100%)';
            cartItem.style.opacity = '0';

            setTimeout(() => {
                cartItem.remove();
                updateCartTotal();
                
                // Show success notification with undo option
                toastr.success(
                    data.message,
                    'Success', 
                    {
                        timeOut: 5000,
                        closeButton: true,
                        progressBar: true,
                        onclick: function() {
                            // TODO: Implement undo functionality if needed
                        }
                    }
                );

                // Update cart count in navigation if exists
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    const currentCount = parseInt(cartCount.textContent || '0');
                    cartCount.textContent = Math.max(0, currentCount - 1);
                }
            }, 300);
        } else {
            // Restore item state and show error
            cartItem.style.opacity = '1';
            cartItem.style.pointerEvents = 'auto';
            toastr.error(data.message || 'Failed to remove item from cart', 'Error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Restore item state
        cartItem.style.opacity = '1';
        cartItem.style.pointerEvents = 'auto';
        toastr.error('An error occurred while removing item from cart', 'Error');
    });
}

// Make functions available globally
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;

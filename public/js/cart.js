function addToCart(productId) {
    // Disable the button to prevent double clicks
    const button = event.target;
    button.disabled = true;
    
    // Send AJAX request to add item to cart
    fetch(`/cart/add/${productId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Item added to cart successfully!');
            
            // Optional: Update cart count in navigation if you have one
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = parseInt(cartCount.textContent || '0') + 1;
            }
        } else {
            alert(data.message || 'Failed to add item to cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding item to cart');
    })
    .finally(() => {
        // Re-enable the button
        button.disabled = false;
    });
}

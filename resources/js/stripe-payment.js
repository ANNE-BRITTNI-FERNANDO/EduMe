// Initialize Stripe with your publishable key
const stripe = Stripe(stripeKey);

// Handle the checkout process
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkout-form');
    
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                // Create a payment intent on the server
                const response = await fetch('/checkout/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Confirm the payment with Stripe
                const result = await stripe.confirmCardPayment(data.clientSecret, {
                    payment_method: {
                        card: elements.getElement('card'),
                        billing_details: {
                            name: document.getElementById('card-holder-name').value
                        }
                    }
                });
                
                if (result.error) {
                    throw new Error(result.error.message);
                }
                
                // Payment successful - redirect to success page
                window.location.href = '/payment/success';
                
            } catch (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
            }
        });
    }
});

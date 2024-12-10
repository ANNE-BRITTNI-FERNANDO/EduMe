<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="max-w-md mx-auto">
                        <h2 class="text-2xl font-bold mb-4">Checkout</h2>
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold mb-2">Order Summary</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                @php
                                    $total = 0;
                                    foreach($cartItems as $item) {
                                        $total += $item->price;
                                    }
                                @endphp
                                @if($cartItems->isEmpty())
                                    <p class="text-gray-500">Your cart is empty</p>
                                @else
                                    @foreach($cartItems as $item)
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="flex-grow">
                                                <div class="font-medium">{{ $item->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $item->item_type === 'product' ? 'Product' : 'Bundle' }}
                                                    @if($item->item_type === 'product' && $item->product)
                                                        by {{ $item->product->user->name }}
                                                    @elseif($item->item_type === 'bundle' && $item->bundle)
                                                        by {{ $item->bundle->user->name }}
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="ml-4">${{ number_format($item->price, 2) }}</span>
                                        </div>
                                    @endforeach
                                    
                                    <div class="border-t pt-2 mt-2">
                                        <div class="flex justify-between items-center font-bold">
                                            <span>Total</span>
                                            <span>${{ number_format($total, 2) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if(!$cartItems->isEmpty())
                            <div class="flex justify-center mb-6">
                                <button type="button" onclick="showPaymentMethod('card')"
                                    class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                    Pay with Card
                                </button>
                            </div>

                            <!-- Card Payment Form -->
                            <form id="payment-form" class="mt-4">
                                <div id="payment-element" class="mb-6">
                                    <!-- Stripe Elements will be inserted here -->
                                </div>
                                <button
                                    id="submit-payment"
                                    class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Pay Now
                                </button>
                                <div id="payment-message" class="mt-4 text-red-500 hidden"></div>
                            </form>
                        @else
                            <div class="text-center">
                                <a href="{{ route('productlisting') }}" 
                                   class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                    Browse Products
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @if(!$cartItems->isEmpty())
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ config('services.stripe.key') }}');
        let elements;
        let paymentElement;

        function showPaymentMethod(method) {
            if (method === 'card' && !elements) {
                initialize(); // Initialize Stripe when card payment is selected
            }
        }

        // Initialize Stripe Elements on page load
        document.addEventListener('DOMContentLoaded', function() {
            initialize();
        });

        // Fetch client secret from server
        async function initialize() {
            try {
                const response = await fetch('{{ route('payment.intent') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        amount: {{ $total * 100 }} // Convert to cents
                    })
                });

                const { clientSecret } = await response.json();

                const appearance = {
                    theme: 'stripe',
                };

                elements = stripe.elements({ appearance, clientSecret });

                paymentElement = elements.create("payment");
                paymentElement.mount("#payment-element");
                
                document.querySelector("#submit-payment").disabled = false;
            } catch (e) {
                console.error('Error:', e);
                document.querySelector("#payment-message").textContent = "Failed to initialize payment. Please try again.";
                document.querySelector("#payment-message").classList.remove('hidden');
            }
        }

        // Handle form submission
        async function handleSubmit(e) {
            e.preventDefault();
            setLoading(true);

            try {
                const { error } = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        return_url: "{{ route('payment.success') }}",
                    },
                });

                if (error) {
                    const messageDiv = document.querySelector("#payment-message");
                    messageDiv.textContent = error.message;
                    messageDiv.classList.remove('hidden');
                }
            } catch (e) {
                console.error('Error:', e);
                const messageDiv = document.querySelector("#payment-message");
                messageDiv.textContent = "An unexpected error occurred.";
                messageDiv.classList.remove('hidden');
            }

            setLoading(false);
        }

        function setLoading(isLoading) {
            const submitButton = document.querySelector("#submit-payment");
            submitButton.disabled = isLoading;
            submitButton.textContent = isLoading ? "Processing..." : "Pay Now";
        }

        // Add event listeners
        document.querySelector("#payment-form").addEventListener("submit", handleSubmit);
    </script>
    @endif
    @endpush
</x-app-layout>

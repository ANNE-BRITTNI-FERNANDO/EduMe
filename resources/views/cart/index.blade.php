@push('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" id="cart-container">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">Your Cart</h2>
                        @if(!$items->isEmpty())
                            <a href="{{ route('productlisting') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Continue Shopping
                            </a>
                        @endif
                    </div>
                    
                    @if($items->isEmpty())
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-xl text-gray-500 mb-4">Your cart is empty</p>
                            <a href="{{ route('productlisting') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Browse Products
                            </a>
                        </div>
                    @else
                        <div class="space-y-4" id="cart-items">
                            @foreach($items as $item)
                                <div id="cart-item-{{ $item->id }}" 
                                     class="cart-item flex items-start space-x-4 p-4 border rounded-lg transition-all duration-300 ease-in-out hover:shadow-md">
                                    <div class="relative w-24 h-24 flex-shrink-0">
                                        @if($item->item_type === 'product' && $item->product && $item->product->image_path)
                                            <img src="{{ asset('storage/' . $item->product->image_path) }}" 
                                                 alt="{{ $item->name }}" 
                                                 class="w-full h-full object-cover rounded-md">
                                        @elseif($item->item_type === 'bundle' && $item->bundle && $item->bundle->image_path)
                                            <img src="{{ asset('storage/' . $item->bundle->image_path) }}" 
                                                 alt="{{ $item->name }}" 
                                                 class="w-full h-full object-cover rounded-md">
                                        @else
                                            <div class="w-full h-full bg-gray-200 rounded-md flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        
                                        <div class="absolute -top-2 -right-2 px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full">
                                            {{ ucfirst($item->item_type) }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-semibold">
                                                    <a href="{{ $item->item_type === 'product' ? route('product.show', $item->product->id) : route('bundles.show', $item->bundle->id) }}" 
                                                       class="text-gray-800 hover:text-indigo-600 transition-colors duration-200">
                                                        {{ $item->name }}
                                                    </a>
                                                </h3>
                                                <p class="text-gray-600 mt-1">
                                                    @if($item->item_type === 'product' && $item->product)
                                                        {{ Str::limit($item->product->description, 100) }}
                                                    @elseif($item->item_type === 'bundle' && $item->bundle)
                                                        {{ Str::limit($item->bundle->description, 100) }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-indigo-600">
                                                    ${{ number_format($item->price, 2) }}
                                                </p>
                                                <form action="{{ route('cart.remove', $item->id) }}" 
                                                      method="POST" 
                                                      class="mt-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-800 text-sm flex items-center justify-end w-full">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="mt-8 border-t pt-8">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Total Amount:</h3>
                                    <span class="text-2xl font-bold">${{ number_format($items->sum('price'), 2) }}</span>
                                </div>
                                
                                <div class="space-y-4">
                                    <a href="{{ route('checkout') }}" 
                                       class="w-full block text-center bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Proceed to Checkout
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set up CSRF token for AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Add click event listeners to remove buttons
    document.querySelectorAll('.cart-item form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const cartItemId = this.closest('.cart-item').id.replace('cart-item-', '');
            
            fetch(this.action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
            })
            .then(response => {
                if (response.ok) {
                    // Remove the cart item from the DOM
                    document.getElementById('cart-item-' + cartItemId).remove();
                    
                    // If cart is empty, refresh the page to show empty cart message
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        window.location.reload();
                    }
                } else {
                    console.error('Failed to remove item from cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>
@endpush

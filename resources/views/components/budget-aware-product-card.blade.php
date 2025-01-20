@props(['product', 'budgetTracking' => null])

<div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl transform hover:-translate-y-1">
    <div class="relative">
        @if($budgetTracking && $product->price <= $budgetTracking->remaining_amount)
            <div class="absolute top-2 right-2 z-10">
                <span class="px-2 py-1 bg-green-500 text-white text-xs rounded-full shadow-lg">
                    Within Budget
                </span>
            </div>
        @endif
        
        <!-- Product Image -->
        <div class="relative h-64 bg-blue-100 dark:bg-gray-700">
            @if($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" 
                     alt="{{ $product->name }}" 
                     class="w-full h-full object-cover object-center">
            @else
                <div class="flex items-center justify-center h-full">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </div>

        <!-- Product Info -->
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $product->name }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $product->description }}</p>
            
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                        LKR {{ number_format($product->price, 2) }}
                    </p>
                    @if($budgetTracking)
                        @if($product->price <= $budgetTracking->remaining_amount)
                            <p class="text-xs text-green-600 dark:text-green-400">
                                {{ number_format(($product->price / $budgetTracking->remaining_amount) * 100, 1) }}% of remaining budget
                            </p>
                        @else
                            <p class="text-xs text-red-600 dark:text-red-400">
                                Exceeds budget by LKR {{ number_format($product->price - $budgetTracking->remaining_amount, 2) }}
                            </p>
                        @endif
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    @if($budgetTracking && $product->price > $budgetTracking->remaining_amount)
                        <button onclick="createPriceAlert({{ $product->id }}, {{ $budgetTracking->remaining_amount }})"
                                class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white text-sm rounded-lg transition duration-300">
                            Set Alert
                        </button>
                    @endif
                    <a href="{{ route('cart.add', $product->id) }}"
                       class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition duration-300">
                        Add to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function createPriceAlert(productId, targetPrice) {
        fetch('{{ route('buyer.budget.alert') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: productId,
                target_price: targetPrice
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                toastr.success('Price alert created successfully');
            } else {
                toastr.error('Failed to create price alert');
            }
        })
        .catch(error => {
            toastr.error('An error occurred');
        });
    }
</script>

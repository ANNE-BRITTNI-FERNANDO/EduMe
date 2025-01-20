@push('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">Your Cart</h2>
                        <a href="{{ route('productlisting') }}" class="text-indigo-600 hover:text-indigo-800">
                            Continue Shopping
                        </a>
                    </div>

                    @if($items->isEmpty())
                        <div class="flex flex-col items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
                            <div class="max-w-md w-full space-y-8 text-center">
                                <!-- Empty Cart Illustration -->
                                <div class="w-48 h-48 mx-auto">
                                    <svg class="w-full h-full text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>

                                <!-- Empty Cart Message -->
                                <div class="mt-6">
                                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight">
                                        Your cart is empty
                                    </h2>
                                    <p class="mt-3 text-base text-gray-500 dark:text-gray-400">
                                        Looks like you haven't added anything to your cart yet.
                                        <br>Let's find something special for you!
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                                    <a href="{{ route('productlisting') }}" 
                                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                        </svg>
                                        Browse Products
                                    </a>
                                    <a href="{{ route('home') }}" 
                                       class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-full text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Return Home
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        @php
                            $subtotal = 0;
                            $sellerGroups = [];
                            
                            // Group items by seller and calculate subtotal
                            foreach($items as $item) {
                                $seller = null;
                                $price = 0;
                                
                                if($item->item_type === 'product' && $item->product) {
                                    $seller = $item->product->user;
                                    $price = $item->product->price;
                                } elseif($item->item_type === 'bundle' && $item->bundle) {
                                    $seller = $item->bundle->user;
                                    $price = $item->bundle->price;
                                }
                                
                                if($seller) {
                                    if(!isset($sellerGroups[$seller->id])) {
                                        $sellerGroups[$seller->id] = [
                                            'seller' => $seller,
                                            'items' => [],
                                            'subtotal' => 0
                                        ];
                                    }
                                    $sellerGroups[$seller->id]['items'][] = $item;
                                    $sellerGroups[$seller->id]['subtotal'] += $price;
                                    $subtotal += $price;
                                }
                            }
                        @endphp

                        <div class="space-y-6">
                            @foreach($sellerGroups as $sellerId => $group)
                                <div class="border-b pb-6 mb-6">
                                    <h3 class="text-lg font-semibold mb-4">Items from {{ $group['seller']->name }}</h3>
                                    @foreach($group['items'] as $item)
                                        <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    @if($item->item_type === 'product' && $item->product)
                                                        <img class="h-20 w-20 object-cover rounded" src="{{ asset('storage/' . $item->product->image_path) }}" alt="{{ $item->product->product_name }}">
                                                        <div class="ml-4">
                                                            <h2 class="text-lg font-bold text-gray-900">{{ $item->product->product_name }}</h2>
                                                            <p class="text-gray-500">Price: LKR {{ number_format($item->product->price, 2) }}</p>
                                                        </div>
                                                    @elseif($item->item_type === 'bundle' && $item->bundle)
                                                        <img class="h-20 w-20 object-cover rounded" src="{{ asset('storage/' . $item->bundle->bundle_image) }}" alt="{{ $item->bundle->bundle_name }}">
                                                        <div class="ml-4">
                                                            <h2 class="text-lg font-bold text-gray-900">{{ $item->bundle->bundle_name }}</h2>
                                                            <p class="text-gray-500">Price: LKR {{ number_format($item->bundle->price, 2) }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-4">
                                                    @if($item->item_type === 'product')
                                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            <div class="mt-8">
                                <form action="{{ route('stripe.checkout') }}" method="POST" class="space-y-4">
                                    @csrf
                                    
                                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                                        <h2 class="text-xl font-bold mb-4 text-gray-800">Delivery Details</h2>
                                        
                                        <div class="space-y-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                                                    <textarea 
                                                        name="delivery_address" 
                                                        id="delivery_address" 
                                                        rows="2"
                                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                        placeholder="Enter your delivery address"
                                                        required
                                                    >{{ auth()->user()->address }}</textarea>
                                                    <p id="address-error" class="mt-1 text-sm text-red-600 hidden">Please enter your delivery address</p>
                                                </div>

                                                <div>
                                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                                    <input 
                                                        type="tel" 
                                                        name="phone" 
                                                        id="phone"
                                                        value="{{ auth()->user()->phone }}"
                                                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                        placeholder="Enter your phone number"
                                                        required
                                                    >
                                                    <p id="phone-error" class="mt-1 text-sm text-red-600 hidden">Please enter your phone number</p>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                                    <select 
                                                        name="province" 
                                                        id="province"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    >
                                                        <option value="">Select Province</option>
                                                        <option value="Western">Western Province</option>
                                                        <option value="Central">Central Province</option>
                                                        <option value="Southern">Southern Province</option>
                                                        <option value="Northern">Northern Province</option>
                                                        <option value="Eastern">Eastern Province</option>
                                                        <option value="North Western">North Western Province</option>
                                                        <option value="North Central">North Central Province</option>
                                                        <option value="Uva">Uva Province</option>
                                                        <option value="Sabaragamuwa">Sabaragamuwa Province</option>
                                                    </select>
                                                    <p id="province-error" class="mt-1 text-sm text-red-600 hidden">Please select a province</p>
                                                </div>
                                                <div>
                                                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                                    <select 
                                                        name="location" 
                                                        id="location"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        disabled
                                                    >
                                                        <option value="">Select District</option>
                                                    </select>
                                                    <p id="district-error" class="mt-1 text-sm text-red-600 hidden">Please select a district</p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex justify-end space-x-4">
                                                <button 
                                                    type="button" 
                                                    id="updateDeliveryFeeBtn"
                                                    class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                                >
                                                    Calculate Delivery Fee
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-lg shadow-lg p-6">
                                        <h2 class="text-xl font-bold mb-4 text-gray-800">Order Summary</h2>
                                        <div class="space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Subtotal</span>
                                                <span class="font-semibold" id="subtotal" data-value="{{ $subtotal }}">LKR {{ number_format($subtotal, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Delivery Fee</span>
                                                <span class="font-semibold" id="delivery-fee" data-value="0">LKR 0.00</span>
                                            </div>
                                            <div class="border-t pt-3 flex justify-between">
                                                <span class="text-lg font-bold">Total</span>
                                                <span class="text-lg font-bold" id="total-amount" data-value="{{ $subtotal }}">LKR {{ number_format($subtotal, 2) }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-6">
                                            <button 
                                                type="submit" 
                                                class="w-full bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-lg font-semibold"
                                            >
                                                Proceed to Checkout
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('location');
            const updateDeliveryFeeBtn = document.getElementById('updateDeliveryFeeBtn');
            const subtotalElement = document.getElementById('subtotal');
            const deliveryFeeElement = document.getElementById('delivery-fee');
            const totalElement = document.getElementById('total-amount');
            
            // Function to format numbers
            function formatNumber(number) {
                if (isNaN(number)) return '0.00';
                return parseFloat(number).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Function to update total
            function updateTotal() {
                const subtotal = parseFloat(subtotalElement.dataset.value || 0);
                const deliveryFee = parseFloat(deliveryFeeElement.dataset.value || 0);
                const total = subtotal + deliveryFee;
                
                totalElement.textContent = 'LKR ' + formatNumber(total);
                totalElement.dataset.value = total;
            }
            
            // Add change event listener to province select
            provinceSelect.addEventListener('change', function() {
                const selectedProvince = this.value;
                
                // Reset district select
                districtSelect.innerHTML = '<option value="">Select District</option>';
                districtSelect.disabled = true;
                
                if (!selectedProvince) {
                    document.getElementById('province-error').classList.remove('hidden');
                    return;
                }
                
                document.getElementById('province-error').classList.add('hidden');
                
                // Fetch districts for selected province
                fetch(`/cart/get-districts/${selectedProvince}`)
                .then(response => response.json())
                .then(data => {
                    districtSelect.disabled = false;
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    
                    if (Array.isArray(data)) {
                        data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district;
                            option.textContent = district;
                            districtSelect.appendChild(option);
                        });
                    } else {
                        districtSelect.innerHTML = '<option value="">No districts available</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching districts:', error);
                    districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                });
            });

            // Update delivery fee when button is clicked
            updateDeliveryFeeBtn.addEventListener('click', function() {
                const province = provinceSelect.value;
                const location = districtSelect.value;
                
                if (!province || !location) {
                    if (!province) {
                        document.getElementById('province-error').classList.remove('hidden');
                    }
                    if (!location) {
                        document.getElementById('district-error').classList.remove('hidden');
                    }
                    return;
                }
                
                // Hide error messages
                document.getElementById('province-error').classList.add('hidden');
                document.getElementById('district-error').classList.add('hidden');
                
                // Show loading state
                const originalText = updateDeliveryFeeBtn.textContent;
                updateDeliveryFeeBtn.textContent = 'Calculating...';
                updateDeliveryFeeBtn.disabled = true;
                
                fetch('{{ route('cart.update.delivery') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        province: province,
                        location: location
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update delivery fee and total
                        deliveryFeeElement.textContent = 'LKR ' + data.delivery_fee;
                        deliveryFeeElement.dataset.value = parseFloat(data.delivery_fee);
                        updateTotal();
                        
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'text-green-600 mt-2';
                        successMessage.textContent = 'Delivery fee updated successfully';
                        updateDeliveryFeeBtn.parentNode.appendChild(successMessage);
                        
                        setTimeout(() => {
                            successMessage.remove();
                        }, 3000);
                    } else {
                        throw new Error(data.message || 'Failed to update delivery fee');
                    }
                })
                .catch(error => {
                    console.error('Error updating delivery fee:', error);
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'text-red-600 mt-2';
                    errorMessage.textContent = error.message || 'Failed to update delivery fee. Please try again.';
                    updateDeliveryFeeBtn.parentNode.appendChild(errorMessage);
                    
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 3000);
                })
                .finally(() => {
                    // Reset button state
                    updateDeliveryFeeBtn.textContent = originalText;
                    updateDeliveryFeeBtn.disabled = false;
                });
            });
        });
    </script>
    @endpush
</x-app-layout>

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
                        <div class="text-center py-12">
                            <p class="text-xl text-gray-500 mb-4">Your cart is empty</p>
                            <a href="{{ route('productlisting') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Browse Products
                            </a>
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
                                                        <a href="{{ route('cart.remove.product', ['id' => $item->product_id]) }}" 
                                                           class="text-red-500 hover:text-red-700 flex items-center">
                                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Remove
                                                        </a>
                                                    @else
                                                        <a href="{{ route('cart.remove.bundle', ['id' => $item->bundle_id]) }}" 
                                                           class="text-red-500 hover:text-red-700 flex items-center">
                                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Remove
                                                        </a>
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
                                                <span class="font-semibold" id="delivery-fee">LKR 0.00</span>
                                            </div>
                                            <div class="border-t pt-3 flex justify-between">
                                                <span class="text-lg font-bold">Total</span>
                                                <span class="text-lg font-bold" id="total-amount">LKR {{ number_format($subtotal, 2) }}</span>
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
            
            // Add change event listener to province select
            provinceSelect.addEventListener('change', function() {
                const selectedProvince = this.value;
                
                // Reset and disable district select if no province selected
                if (!selectedProvince) {
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    districtSelect.disabled = true;
                    return;
                }
                
                // Show loading state
                districtSelect.disabled = true;
                districtSelect.innerHTML = '<option value="">Loading districts...</option>';
                
                // Fetch districts for selected province
                fetch(`/cart/get-districts/${selectedProvince}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    districtSelect.disabled = false;
                    districtSelect.innerHTML = '<option value="">Select District</option>';
                    
                    if (data.success && data.districts) {
                        data.districts.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district;
                            option.textContent = district;
                            districtSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching districts:', error);
                    districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                });
            });

            // Function to format numbers
            function formatNumber(number) {
                if (isNaN(number)) return '0.00';
                return parseFloat(number).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

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
                
                fetch('/cart/update-delivery-fee', {
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
                        const subtotal = parseFloat(subtotalElement.dataset.value || 0);
                        const deliveryFee = parseFloat(data.delivery_fee);
                        const total = subtotal + deliveryFee;
                        
                        // Update the display values
                        deliveryFeeElement.textContent = 'LKR' + formatNumber(deliveryFee);
                        totalElement.textContent = 'LKR' + formatNumber(total);
                        
                        // Show success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'text-green-600 mt-2';
                        successMessage.textContent = 'Delivery fee updated successfully';
                        updateDeliveryFeeBtn.parentNode.appendChild(successMessage);
                        
                        // Remove success message after 3 seconds
                        setTimeout(() => {
                            successMessage.remove();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error updating delivery fee:', error);
                    // Show error message to user
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'text-red-600 mt-2';
                    errorMessage.textContent = 'Failed to update delivery fee. Please try again.';
                    updateDeliveryFeeBtn.parentNode.appendChild(errorMessage);
                    
                    // Remove error message after 3 seconds
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 3000);
                });
            });
        });
    </script>
    @endpush
</x-app-layout>

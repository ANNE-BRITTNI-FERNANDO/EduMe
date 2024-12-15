<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Seller Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Total Earnings -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Earnings</div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($totalEarned, 2) }}
                        </div>
                    </div>
                </div>

                <!-- Available Balance -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Available Balance</div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($availableBalance, 2) }}
                        </div>
                    </div>
                </div>

                <!-- Pending Balance -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pending Balance</div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($pendingBalance, 2) }}
                        </div>
                    </div>
                </div>

                <!-- Total Products -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Products</div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ $totalProducts }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Add Product -->
                <a href="{{ route('seller.products.create') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Add Product</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a new product to your store</p>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Create Bundle -->
                <a href="{{ route('seller.bundles.create') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create Bundle</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a new product bundle</p>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- View Earnings -->
                <a href="{{ route('seller.earnings.index') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">View Earnings</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check your earnings and payouts</p>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- View Bundles -->
                <a href="{{ route('seller.bundles.index') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">View Bundles</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your product bundles</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Orders</h2>
                    
                    @if($recentOrders->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">No orders yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Buyer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Items</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                #{{ $order->id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $order->user->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $order->user->email }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col space-y-2">
                                                    @foreach($order->items->where('seller_id', auth()->id()) as $item)
                                                        <div class="flex items-center space-x-2">
                                                            @php
                                                                $product = $item->item;
                                                                $imageUrl = null;
                                                                $itemName = 'Unknown Product';
                                                                
                                                                if ($product) {
                                                                    if ($product instanceof \App\Models\Product) {
                                                                        $imageUrl = $product->image_path ? Storage::url($product->image_path) : null;
                                                                        $itemName = $product->product_name;
                                                                    } elseif ($product instanceof \App\Models\Bundle) {
                                                                        $imageUrl = $product->bundle_image ? Storage::url($product->bundle_image) : null;
                                                                        $itemName = $product->bundle_name . ' (Bundle)';
                                                                    }
                                                                }
                                                            @endphp
                                                            
                                                            @if($imageUrl)
                                                                <img src="{{ $imageUrl }}" alt="{{ $itemName }}" class="w-8 h-8 object-cover rounded">
                                                            @else
                                                                <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center">
                                                                    <span class="text-gray-500 dark:text-gray-400 text-xs">No Image</span>
                                                                </div>
                                                            @endif
                                                            
                                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                                {{ $itemName }} (x{{ $item->quantity }})
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                LKR {{ number_format($order->items->where('seller_id', auth()->id())->sum(function($item) { 
                                                    return $item->price * $item->quantity; 
                                                }), 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($order->status === 'completed') bg-green-100 text-green-800
                                                    @elseif($order->status === 'processing') bg-yellow-100 text-yellow-800
                                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('seller.orders.show', $order) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

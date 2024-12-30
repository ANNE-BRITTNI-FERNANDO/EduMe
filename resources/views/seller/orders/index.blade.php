<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Orders</h2>
                <a href="{{ route('seller.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 ease-in-out">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Back to Dashboard</span>
                </a>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($orders->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No orders yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your orders will appear here when customers make purchases.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($orders as $order)
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    Order #{{ $order->id }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($order->status === 'Delivered_to_warehouse')
                                                        bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400
                                                    @elseif($order->status === 'Cancelled')
                                                        bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400
                                                    @else
                                                        bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400
                                                    @endif">
                                                    {{ str_replace('_', ' ', $order->status) }}
                                                </span>
                                            </div>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $order->created_at->format('M d, Y h:i A') }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="px-6 py-4">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <!-- Customer Info -->
                                                <div class="mb-4">
                                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Customer</h4>
                                                    <div class="flex items-center space-x-2">
                                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                                                {{ substr($order->user->name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $order->user->name }}</p>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->user->email }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Order Items -->
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Items</h4>
                                                    <div class="space-y-3">
                                                        @foreach($order->items->where('seller_id', auth()->id()) as $item)
                                                            <div class="flex items-center space-x-4">
                                                                @php
                                                                    $itemDetails = $item->item;
                                                                    $imageUrl = null;
                                                                    $itemName = null;
                                                                    
                                                                    if ($itemDetails) {
                                                                        if (get_class($itemDetails) === 'App\\Models\\Product') {
                                                                            $imageUrl = $itemDetails->image_path ? asset('storage/' . $itemDetails->image_path) : null;
                                                                            $itemName = $itemDetails->product_name;
                                                                        } elseif (get_class($itemDetails) === 'App\\Models\\Bundle') {
                                                                            $imageUrl = $itemDetails->bundle_image ? asset('storage/' . $itemDetails->bundle_image) : null;
                                                                            $itemName = $itemDetails->bundle_name;
                                                                        }
                                                                    }
                                                                @endphp

                                                                <div class="flex-shrink-0 w-12 h-12">
                                                                    @if($imageUrl)
                                                                        <img src="{{ $imageUrl }}" alt="{{ $itemName }}" class="w-full h-full object-cover rounded-lg">
                                                                    @else
                                                                        <div class="w-full h-full rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                                            <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                            </svg>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                                        {{ $itemName ?? 'Product Unavailable' }}
                                                                        @if(!$itemDetails)
                                                                            <span class="text-xs text-red-500">(Product may have been deleted)</span>
                                                                        @endif
                                                                    </p>
                                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                        Quantity: {{ $item->quantity }}
                                                                    </p>
                                                                </div>
                                                                
                                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                    LKR {{ number_format($item->price * $item->quantity, 2) }}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Actions -->
                                            <div class="ml-6">
                                                <a href="{{ route('seller.orders.show', $order->id) }}" 
                                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center space-x-4">
                    <div class="bg-white p-3 rounded-full shadow-sm">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
                        <p class="text-sm text-gray-500">{{ $order->created_at->format('F j, Y g:i A') }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.orders.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Orders
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Customer Information Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Details</h2>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $order->user->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $order->user->email }}</p>
                                    </div>
                                </div>
                                <div class="border-t border-gray-100 pt-4">
                                    <h3 class="text-sm font-medium text-gray-900 mb-2">Shipping Address</h3>
                                    <p class="text-sm text-gray-600">{{ $order->shipping_address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Status Card -->
                    <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Status</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Delivery Status</label>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            {{ $order->delivery_status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($order->delivery_status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                               'bg-yellow-100 text-yellow-800') }}">
                                            <span class="h-2 w-2 rounded-full mr-2
                                                {{ $order->delivery_status === 'completed' ? 'bg-green-400' : 
                                                   ($order->delivery_status === 'cancelled' ? 'bg-red-400' : 
                                                   'bg-yellow-400') }}"></span>
                                            {{ ucfirst($order->delivery_status) }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Payment Status</label>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <span class="h-2 w-2 rounded-full mr-2
                                                {{ $order->payment_status === 'paid' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items and Update Status -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h2>
                            <div class="space-y-6">
                                @foreach($order->items as $item)
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 h-24 w-24 bg-white rounded-lg overflow-hidden">
                                        @if($item->item->image_path)
                                            <img class="h-full w-full object-cover" 
                                                 src="{{ Storage::url($item->item->image_path) }}" 
                                                 alt="{{ $item->item->product_name }}">
                                        @else
                                            <div class="h-full w-full bg-gray-100 flex items-center justify-center">
                                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-6 flex-1">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-900">{{ $item->item->product_name }}</h3>
                                                <p class="text-sm text-gray-500 mt-1">{{ Str::limit($item->item->description, 100) }}</p>
                                                <p class="text-xs text-gray-500 mt-1">Category: {{ $item->item->category }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-gray-900">LKR {{ number_format($item->price, 2) }}</p>
                                                <p class="text-xs text-gray-500 mt-1">Qty: {{ $item->quantity }}</p>
                                                <p class="text-sm font-semibold text-indigo-600 mt-1">
                                                    LKR {{ number_format($item->price * $item->quantity, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <svg class="h-4 w-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Sold by: {{ $item->seller->name }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                <!-- Order Summary -->
                                <div class="border-t border-gray-200 pt-4 mt-6">
                                    <dl class="space-y-3">
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-600">Subtotal</dt>
                                            <dd class="text-sm font-medium text-gray-900">LKR {{ number_format($order->total_amount - $order->delivery_fee, 2) }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-600">Delivery Fee</dt>
                                            <dd class="text-sm font-medium text-gray-900">LKR {{ number_format($order->delivery_fee, 2) }}</dd>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 pt-3">
                                            <dt class="text-base font-medium text-gray-900">Total Amount</dt>
                                            <dd class="text-base font-bold text-indigo-600">LKR {{ number_format($order->total_amount, 2) }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Status Form -->
                    <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Update Order Status</h2>
                            <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">Delivery Status</label>
                                        <select name="status" id="status" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="pending" {{ $order->delivery_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="processing" {{ $order->delivery_status === 'processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="shipped" {{ $order->delivery_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                            <option value="completed" {{ $order->delivery_status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $order->delivery_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Update Status
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Order Details #{{ $order->id }}</h2>
                        <a href="{{ route('admin.orders.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Back to Orders</a>
                    </div>

                    <!-- Order Status -->
                    <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">Order Status</h3>
                        <div class="flex items-center space-x-4">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                {{ $order->delivery_status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $order->delivery_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $order->delivery_status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $order->delivery_status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($order->delivery_status) }}
                            </span>
                            <span class="text-gray-600">Created: {{ $order->created_at->sriLankaFormat() }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Customer Information</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Name:</span> {{ $order->user->name }}</p>
                                <p><span class="font-medium">Email:</span> {{ $order->user->email }}</p>
                                <p><span class="font-medium">Phone:</span> {{ $order->phone }}</p>
                            </div>
                        </div>

                        <!-- Shipping Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Shipping Information</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Address:</span> {{ $order->shipping_address }}</p>
                                <p><span class="font-medium">City:</span> {{ $order->buyer_city }}</p>
                                <p><span class="font-medium">Province:</span> {{ $order->buyer_province }}</p>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Payment Method:</span> {{ ucfirst($order->payment_method) }}</p>
                                <p><span class="font-medium">Payment Status:</span> 
                                    <span class="px-2 py-1 rounded-full text-sm
                                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </p>
                                @if($order->payment_method === 'bank_transfer')
                                    <p><span class="font-medium">Bank Transfer Details:</span><br>
                                        <span class="text-sm">{{ $order->bank_transfer_details }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                            <div class="space-y-2">
                                <p><span class="font-medium">Subtotal:</span> ₹{{ number_format($order->subtotal, 2) }}</p>
                                <p><span class="font-medium">Delivery Fee:</span> ₹{{ number_format($order->delivery_fee, 2) }}</p>
                                <p class="text-lg font-bold"><span class="font-medium">Total:</span> ₹{{ number_format($order->total_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                        <div class="bg-gray-50 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center">
                                                    @if($item->item_type === 'product' && $item->item->image_path)
                                                        <div class="flex-shrink-0 h-10 w-10 mr-3">
                                                            <img class="h-10 w-10 rounded object-cover" src="{{ Storage::url($item->item->image_path) }}" alt="{{ $item->item->product_name }}">
                                                        </div>
                                                    @endif
                                                    <div>
                                                        @if($item->item_type === 'product')
                                                            {{ $item->item->product_name }}
                                                        @else
                                                            {{ $item->item->bundle_name }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">{{ $item->seller->name }}</td>
                                            <td class="px-6 py-4">₹{{ number_format($item->price, 2) }}</td>
                                            <td class="px-6 py-4">{{ $item->quantity }}</td>
                                            <td class="px-6 py-4">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Delivery Tracking -->
                    @if($order->deliveryTracking->count() > 0)
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Delivery Tracking</h3>
                            <div class="space-y-4">
                                @foreach($order->deliveryTracking->sortByDesc('created_at') as $tracking)
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="font-semibold">{{ ucfirst($tracking->status) }}</p>
                                                <p class="text-sm text-gray-600">{{ $tracking->description }}</p>
                                                @if($tracking->location)
                                                    <p class="text-sm text-gray-600">Location: {{ $tracking->location }}</p>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-500">{{ $tracking->created_at->sriLankaFormat() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

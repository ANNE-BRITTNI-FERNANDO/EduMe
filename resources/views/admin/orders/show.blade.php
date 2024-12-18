@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">Order #{{ $order->id }}</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        @if($order->status === 'completed') bg-green-100 text-green-800
                        @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Order Details -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Order Details</h3>
                        <dl class="grid grid-cols-1 gap-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->user->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                                <dd class="mt-1 text-sm text-gray-900">₹{{ number_format($order->total_amount, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Order Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('M d, Y H:i A') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Update Status -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Update Status</h3>
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Order Status</label>
                                    <select name="status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($item->item && $item->item_type === 'App\\Models\\Product' && $item->item->image_path)
                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $item->item->image_path) }}" alt="">
                                                @elseif($item->item && $item->item_type === 'App\\Models\\Bundle' && $item->item->bundle_image)
                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $item->item->bundle_image) }}" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <span class="text-xs text-gray-500">No Image</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    @if($item->item)
                                                        @if($item->item_type === 'App\\Models\\Product')
                                                            {{ $item->item->product_name }}
                                                        @elseif($item->item_type === 'App\\Models\\Bundle')
                                                            {{ $item->item->bundle_name }}
                                                        @endif
                                                    @else
                                                        Unknown Item
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $item->item_type === 'App\\Models\\Product' ? 'Product' : 'Bundle' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if($item->item && $item->item_type === 'App\\Models\\Product')
                                                {{ optional($item->item->seller)->name ?? 'Unknown Seller' }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        x{{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ₹{{ number_format($item->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ₹{{ number_format($item->price * $item->quantity, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Summary -->
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">₹{{ number_format($order->items->sum(function($item) {
                                    return $item->price * $item->quantity;
                                }), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Delivery Fee</span>
                                <span class="font-medium">₹{{ number_format($order->delivery_fee, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-medium pt-2 border-t">
                                <span>Total</span>
                                <span>₹{{ number_format($order->items->sum(function($item) {
                                    return $item->price * $item->quantity;
                                }) + $order->delivery_fee, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Back to Orders
            </a>
        </div>
    </div>
</div>
@endsection

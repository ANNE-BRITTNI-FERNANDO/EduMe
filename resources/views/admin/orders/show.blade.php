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
                                <dd class="mt-1 text-sm text-gray-900">LKR {{ number_format($order->total_amount, 2) }}</dd>
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
                        <ul role="list" class="divide-y divide-gray-200">
                            @foreach($order->items as $item)
                            <li class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-16 h-16">
                                            @if($item->item_type === 'App\\Models\\Product' && $item->item)
                                                @if($item->item->image_path)
                                                    <img class="w-full h-full object-cover rounded" src="{{ Storage::url($item->item->image_path) }}" alt="{{ $item->item->product_name }}">
                                                @else
                                                    <div class="w-full h-full bg-gray-200 rounded flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            @elseif($item->item_type === 'App\\Models\\Bundle')
                                                <div class="w-full h-full bg-indigo-100 rounded flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                @if($item->item_type === 'App\\Models\\Product' && $item->item)
                                                    {{ $item->item->product_name }}
                                                @elseif($item->item_type === 'App\\Models\\Bundle' && $item->item)
                                                    {{ $item->item->bundle_name }}
                                                @else
                                                    Unknown Item
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Quantity: {{ $item->quantity }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Price: LKR {{ number_format($item->price, 2) }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Seller: {{ $item->seller->name }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900">
                                            Subtotal: LKR {{ number_format($item->price * $item->quantity, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
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

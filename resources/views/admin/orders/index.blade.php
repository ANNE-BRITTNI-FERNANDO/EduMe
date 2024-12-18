<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Orders</h1>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-gray-500 text-sm">Total Orders</h3>
                <p class="text-2xl font-semibold">{{ $totalOrders }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-gray-500 text-sm">Pending Orders</h3>
                <p class="text-2xl font-semibold">{{ $pendingOrders }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-gray-500 text-sm">Completed Orders</h3>
                <p class="text-2xl font-semibold">{{ $completedOrders }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="text-gray-500 text-sm">Total Revenue</h3>
                <p class="text-2xl font-semibold">LKR {{ number_format($totalRevenue) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="seller_id" class="block text-sm font-medium text-gray-700">Seller</label>
                    <select name="seller_id" id="seller_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Order ID or Customer">
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Filter
                        </button>
                        @if(request()->hasAny(['seller_id', 'status', 'date_from', 'date_to', 'search']))
                            <a href="{{ route('admin.orders.index') }}" class="ml-3 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Order ID
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Items
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            #{{ $order->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $order->user->name }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                @foreach($order->items as $item)
                                    <div class="flex items-center space-x-3">
                                        @if($item->item && $item->item->image_path)
                                            <img src="{{ Storage::url($item->item->image_path) }}" 
                                                alt="Product image" 
                                                class="w-10 h-10 object-cover rounded-md">
                                        @else
                                            <div class="w-10 h-10 bg-gray-200 rounded-md flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-gray-900">
                                                @if($item->item)
                                                    @if($item->item_type === 'App\\Models\\Product')
                                                        {{ $item->item->product_name }}
                                                    @elseif($item->item_type === 'App\\Models\\Bundle')
                                                        {{ $item->item->bundle_name }}
                                                    @endif
                                                    <div class="text-xs text-gray-500">
                                                        Quantity: {{ $item->quantity }} × LKR {{ number_format($item->price, 0) }}
                                                    </div>
                                                @else
                                                    <span class="text-gray-500">Product no longer available</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Seller: {{ $item->seller->name }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $subtotal = $order->items->sum(function($item) {
                                    return $item->price * $item->quantity;
                                });
                            @endphp
                            <div class="flex flex-col">
                                <div class="font-medium">
                                    LKR {{ number_format($subtotal, 0) }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($order->delivery_status === 'completed') bg-green-100 text-green-800
                                @elseif($order->delivery_status === 'processing') bg-blue-100 text-blue-800
                                @elseif($order->delivery_status === 'cancelled') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($order->delivery_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $order->created_at->format('M d, Y H:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-900">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Order Status Tabs -->
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ request('status') === null ? 'border-indigo-600 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                        onclick="window.location='{{ route('seller.orders.index') }}'">
                                    All Orders
                                </button>
                            </li>
                            @foreach(['pending', 'processing', 'completed', 'cancelled'] as $status)
                                <li class="mr-2" role="presentation">
                                    <button class="inline-block p-4 border-b-2 rounded-t-lg {{ request('status') === $status ? 'border-indigo-600 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                            onclick="window.location='{{ route('seller.orders.index', ['status' => $status]) }}'">
                                        {{ ucfirst($status) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Orders Table -->
                    <div class="overflow-x-auto relative">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Order ID</th>
                                    <th scope="col" class="py-3 px-6">Customer</th>
                                    <th scope="col" class="py-3 px-6">Products</th>
                                    <th scope="col" class="py-3 px-6">Total</th>
                                    <th scope="col" class="py-3 px-6">Status</th>
                                    <th scope="col" class="py-3 px-6">Date</th>
                                    <th scope="col" class="py-3 px-6">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="py-4 px-6">#{{ $order->id }}</td>
                                        <td class="py-4 px-6">{{ $order->user->name }}</td>
                                        <td class="py-4 px-6">
                                            @foreach($order->items as $item)
                                                <div class="mb-1">{{ $item->product->name }} (x{{ $item->quantity }})</div>
                                            @endforeach
                                        </td>
                                        <td class="py-4 px-6">${{ number_format($order->total_amount, 2) }}</td>
                                        <td class="py-4 px-6">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                @if($order->delivery_status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($order->delivery_status === 'processing') bg-blue-100 text-blue-800
                                                @elseif($order->delivery_status === 'completed') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($order->delivery_status) }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">{{ $order->created_at->format('M d, Y') }}</td>
                                        <td class="py-4 px-6">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('seller.orders.show', $order) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">View</a>
                                                
                                                @if($order->delivery_status === 'pending')
                                                    <form action="{{ route('seller.orders.update-status', $order) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="processing">
                                                        <button type="submit" class="text-blue-600 hover:text-blue-900">
                                                            Process
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($order->delivery_status === 'processing')
                                                    <form action="{{ route('seller.orders.update-status', $order) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="text-green-600 hover:text-green-900">
                                                            Complete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-4 px-6 text-center">No orders found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

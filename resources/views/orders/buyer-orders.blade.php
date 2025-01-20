<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Orders') }}
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
                                        onclick="window.location='{{ route('orders.index') }}'">
                                    All Orders
                                </button>
                            </li>
                            @foreach(['pending', 'processing', 'completed', 'cancelled'] as $status)
                                <li class="mr-2" role="presentation">
                                    <button class="inline-block p-4 border-b-2 rounded-t-lg {{ request('status') === $status ? 'border-indigo-600 text-indigo-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                            onclick="window.location='{{ route('orders.index', ['status' => $status]) }}'">
                                        {{ ucfirst($status) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Orders List -->
                    <div class="space-y-4">
                        @forelse($orders as $order)
                            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200
                                @if($order->delivery_status === 'pending') border-l-4 border-l-yellow-400
                                @elseif($order->delivery_status === 'processing') border-l-4 border-l-blue-400
                                @elseif($order->delivery_status === 'completed') border-l-4 border-l-green-400
                                @else border-l-4 border-l-red-400
                                @endif">
                                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                            Order #{{ $order->id }}
                                        </h3>
                                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                                            Placed on {{ $order->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                                        @if($order->delivery_status === 'pending') bg-yellow-200 text-yellow-900 border border-yellow-300
                                        @elseif($order->delivery_status === 'processing') bg-blue-200 text-blue-900 border border-blue-300
                                        @elseif($order->delivery_status === 'completed') bg-green-200 text-green-900 border border-green-300
                                        @else bg-red-200 text-red-900 border border-red-300
                                        @endif">
                                        {{ ucfirst($order->delivery_status) }}
                                    </span>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-700">
                                    <dl>
                                        <!-- Order Items -->
                                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Items
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                                                <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                                                    @foreach($order->items as $item)
                                                        <li class="py-2">
                                                            <div class="flex justify-between">
                                                                <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                                                                <span>${{ number_format($item->price * $item->quantity, 2) }}</span>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </dd>
                                        </div>

                                        <!-- Total -->
                                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Total
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                                                ${{ number_format($order->total, 2) }}
                                            </dd>
                                        </div>

                                        <!-- Delivery Status -->
                                        @if($order->delivery_status === 'processing' || $order->delivery_status === 'completed')
                                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                    Delivery Status
                                                </dt>
                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                                                    {{ $order->delivery_status ?? 'Processing' }}
                                                </dd>
                                            </div>
                                        @endif

                                        <!-- Actions -->
                                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Actions
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-300 sm:mt-0 sm:col-span-2">
                                                <div class="flex space-x-4">
                                                    <a href="{{ route('orders.show', $order) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                                    
                                                    @if($order->delivery_status === 'completed')
                                                        <a href="{{ route('orders.download-invoice', $order) }}" 
                                                           class="text-green-600 hover:text-green-900">Download Invoice</a>
                                                    @endif

                                                    @if($order->delivery_status === 'pending')
                                                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                                    onclick="return confirm('Are you sure you want to cancel this order?')">
                                                                Cancel Order
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400">You haven't placed any orders yet.</p>
                                <a href="{{ route('productlisting') }}" 
                                   class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Browse Products
                                </a>
                            </div>
                        @endforelse
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

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Purchases') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($orders->isEmpty())
                        <div class="text-center py-8">
                            <div class="mb-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No purchases yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Start exploring our products to make your first purchase.</p>
                            <div class="mt-6">
                                <a href="{{ route('productlisting') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                    </svg>
                                    Browse Products
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order Details</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Seller</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach($orders as $order)
                                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            Order #{{ $order->order_number }}
                                                        </div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                                            {{ $order->created_at->format('M d, Y') }}
                                                        </div>
                                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                            LKR {{ number_format($order->total_amount, 2) }}
                                                        </div>
                                                        <div class="mt-2 space-y-1">
                                                            @foreach($order->items as $item)
                                                                <div class="text-sm text-gray-600 dark:text-gray-300">
                                                                    {{ $item->name }} - <span class="font-medium">LKR {{ number_format($item->price, 2) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-300">
                                                    @foreach($order->items->groupBy('seller_id') as $sellerId => $items)
                                                        @if($items->first()->seller)
                                                            {{ $items->first()->seller->name }}
                                                        @else
                                                            <span class="text-gray-500 dark:text-gray-400">Seller not available</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1.5 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm
                                                    @if($order->status === 'confirmed') bg-green-500 text-white dark:bg-green-600
                                                    @elseif($order->status === 'cancelled') bg-red-500 text-white dark:bg-red-600
                                                    @elseif($order->status === 'completed') bg-blue-500 text-white dark:bg-blue-600
                                                    @else bg-yellow-500 text-white dark:bg-yellow-600
                                                    @endif">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('orders.show', $order->id) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold">
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
                    @endif
                    <div class="mt-8 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100">Need Help With Your Order?</h3>
                        <p class="mt-2 text-blue-700 dark:text-blue-200">If you haven't received your order within 7 days of placing it, please contact our support team:</p>
                        <div class="mt-4 space-y-2 text-blue-700 dark:text-blue-200">
                            <p><strong>Phone:</strong> +94 112 345 678 (Mon-Fri, 9 AM - 5 PM)</p>
                            <p><strong>Email:</strong> <a href="mailto:support@edume.lk" class="underline">support@edume.lk</a></p>
                            <p><strong>WhatsApp:</strong> +94 77 234 5678</p>
                        </div>
                        <p class="mt-4 text-sm text-blue-600 dark:text-blue-300">Please have your order number ready when contacting us.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

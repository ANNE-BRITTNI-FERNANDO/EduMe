<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Delivery Verification for Payout #{{ $payout->id }}
        </h4>
        <div class="text-sm text-gray-500">
            Seller: {{ $payout->user->name }}
        </div>
    </div>

    <div class="bg-white dark:bg-gray-700 shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tracking Info</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                @foreach($orderItems as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                        #{{ $item->order->id ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-gray-300">
                            {{ $item->getItemNameAttribute() }}
                        </div>
                        <div class="text-sm text-gray-500">Quantity: {{ $item->quantity ?? 0 }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($item->order->delivery_status === 'delivered') bg-green-100 text-green-800 
                            @elseif($item->order->delivery_status === 'warehouse') bg-blue-100 text-blue-800
                            @elseif($item->order->delivery_status === 'disputed') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($item->order->delivery_status ?? 'pending') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                        @if($item->order->delivery_status)
                            <div class="text-sm">
                                <div>Status: {{ ucfirst($item->order->delivery_status) }}</div>
                                <div>Updated: {{ $item->order->updated_at->format('M d, Y H:i') }}</div>
                            </div>
                        @else
                            <span class="text-gray-500">No tracking info</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

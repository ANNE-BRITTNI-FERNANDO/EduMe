<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Orders for Payout Request #{{ $payout->id }}
        </h4>
        <div class="text-sm text-gray-500">
            Total Amount: LKR {{ number_format($payout->amount, 2) }}
        </div>
    </div>

    @foreach($orderItems as $status => $items)
    <div class="bg-white dark:bg-gray-700 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                {{ ucfirst($status) }} Orders
            </h3>
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                @if($status === 'delivered') bg-green-100 text-green-800 
                @elseif($status === 'warehouse') bg-yellow-100 text-yellow-800
                @elseif($status === 'disputed') bg-red-100 text-red-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ count($items) }} orders
            </span>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-600">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                            #{{ $item->order->id ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-gray-300">
                                {{ $item->item_name }}
                                @if(strtolower($item->item_type) === 'bundle' && $item->item->courses)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Includes:
                                        <ul class="list-disc list-inside">
                                            @foreach($item->item->courses as $course)
                                                <li>{{ $course->title }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">Quantity: {{ $item->quantity ?? 0 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                            LKR {{ number_format(($item->price ?? 0) * ($item->quantity ?? 0), 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                            {{ $item->created_at ? $item->created_at->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($item->order->delivery_status === 'delivered') bg-green-100 text-green-800 
                                @elseif($item->order->delivery_status === 'warehouse') bg-yellow-100 text-yellow-800
                                @elseif($item->order->delivery_status === 'disputed') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($item->order->delivery_status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>

<div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
            Delivery Status
        </h3>
    </div>
    <div class="border-t border-gray-200 dark:border-gray-700">
        <div class="px-4 py-5">
            <!-- Progress Tracker -->
            <div class="flex items-center justify-between mb-8">
                <div class="w-full">
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <!-- Order Placed -->
                            <div class="flex flex-col items-center relative">
                                <div class="rounded-full w-10 h-10 flex items-center justify-center
                                    {{ $order->delivery_status != 'pending' ? 'bg-green-500' : 'bg-gray-300' }}">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="text-xs mt-2">Order Placed</div>
                                <div class="text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</div>
                            </div>

                            <!-- Warehouse Confirmed -->
                            <div class="flex flex-col items-center relative">
                                <div class="rounded-full w-10 h-10 flex items-center justify-center
                                    {{ in_array($order->delivery_status, ['warehouse_confirmed', 'dispatched', 'delivered']) ? 'bg-green-500' : 'bg-gray-300' }}">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="text-xs mt-2">At Warehouse</div>
                                @if($order->warehouse_confirmed_at)
                                    <div class="text-xs text-gray-500">{{ $order->warehouse_confirmed_at->format('M d, Y') }}</div>
                                @endif
                            </div>

                            <!-- Dispatched -->
                            <div class="flex flex-col items-center relative">
                                <div class="rounded-full w-10 h-10 flex items-center justify-center
                                    {{ in_array($order->delivery_status, ['dispatched', 'delivered']) ? 'bg-green-500' : 'bg-gray-300' }}">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="text-xs mt-2">Dispatched</div>
                                @if($order->dispatched_at)
                                    <div class="text-xs text-gray-500">{{ $order->dispatched_at->format('M d, Y') }}</div>
                                @endif
                            </div>

                            <!-- Delivered -->
                            <div class="flex flex-col items-center relative">
                                <div class="rounded-full w-10 h-10 flex items-center justify-center
                                    {{ $order->delivery_status === 'delivered' ? 'bg-green-500' : 'bg-gray-300' }}">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="text-xs mt-2">Delivered</div>
                                @if($order->delivered_at)
                                    <div class="text-xs text-gray-500">{{ $order->delivered_at->format('M d, Y') }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Progress Lines -->
                        <div class="absolute top-5 left-0 w-full">
                            <div class="h-0.5 w-full bg-gray-200">
                                <div class="h-0.5 bg-green-500 transition-all duration-500"
                                    style="width: {{ $order->delivery_status === 'pending' ? '0%' :
                                        ($order->delivery_status === 'warehouse_confirmed' ? '33%' :
                                        ($order->delivery_status === 'dispatched' ? '66%' :
                                        ($order->delivery_status === 'delivered' ? '100%' : '0%'))) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($order->warehouse)
                <div class="mt-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Warehouse Information</h4>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <p>{{ $order->warehouse->name }}</p>
                        <p>{{ $order->warehouse->address }}</p>
                        <p>Operating Hours: {{ $order->warehouse->opening_time }} - {{ $order->warehouse->closing_time }}</p>
                    </div>
                </div>
            @endif

            @if(auth()->user()->role === 'seller' && $order->delivery_status === 'pending')
                <div class="mt-6">
                    <form action="{{ route('orders.update-delivery-status', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="warehouse_confirmed">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Warehouse
                            </label>
                            <select name="warehouse_id" required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                <option value="">Select a warehouse</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">
                                        {{ $warehouse->name }} - {{ $warehouse->address }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Confirm Product at Warehouse
                        </button>
                    </form>
                </div>
            @endif

            @if(auth()->user()->role === 'delivery' && in_array($order->delivery_status, ['warehouse_confirmed', 'dispatched']))
                <div class="mt-6">
                    <form action="{{ route('orders.update-delivery-status', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $order->delivery_status === 'warehouse_confirmed' ? 'dispatched' : 'delivered' }}">
                        
                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            {{ $order->delivery_status === 'warehouse_confirmed' ? 'Mark as Dispatched' : 'Mark as Delivered' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

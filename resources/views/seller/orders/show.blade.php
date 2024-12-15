<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">Order #{{ $order->id }}</h2>
                        <div class="flex space-x-4">
                            <a href="{{ route('seller.dashboard') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Back to Dashboard
                            </a>
                            <a href="{{ route('delivery.warehouses.map') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                View Warehouses Map
                            </a>
                            <a href="{{ route('seller.orders.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
                                Back to Orders
                            </a>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium mb-4">Order Information</h3>
                                <p class="text-gray-600"><span class="font-medium">Status:</span> {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}</p>
                                <p class="text-gray-600"><span class="font-medium">Order Date:</span> {{ $order->created_at->format('M d, Y H:i') }}</p>
                                <p class="text-gray-600"><span class="font-medium">Total Amount:</span> Rs. {{ number_format($order->total_amount, 2) }}</p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium mb-4">Customer Information</h3>
                                <p class="text-gray-600"><span class="font-medium">Name:</span> {{ $order->user->name }}</p>
                                <p class="text-gray-600"><span class="font-medium">Email:</span> {{ $order->user->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Status Timeline -->
                    <div class="mb-8 bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-6">Delivery Tracking</h3>
                        <div class="relative">
                            <div class="absolute h-full w-0.5 bg-gray-200 left-6 top-0"></div>
                            @php
                                $statuses = [
                                    'pending' => ['icon' => '🔵', 'title' => 'Order Pending', 'action' => 'delivered_to_warehouse'],
                                    'delivered_to_warehouse' => ['icon' => '📦', 'title' => 'Delivered to Warehouse', 'action' => 'dispatched'],
                                    'dispatched' => ['icon' => '🚚', 'title' => 'Dispatched', 'action' => 'delivered'],
                                    'delivered' => ['icon' => '✅', 'title' => 'Delivered', 'action' => null]
                                ];
                                $currentFound = false;
                                $currentStatus = $order->delivery_status ?? 'pending';
                            @endphp
                            
                            @foreach($statuses as $status => $details)
                                @php
                                    $isActive = $currentStatus === $status;
                                    $isPast = !$currentFound && !$isActive;
                                    if ($isActive) $currentFound = true;
                                    
                                    $statusDate = null;
                                    if ($status === 'delivered_to_warehouse' && $order->warehouse_confirmed_at) {
                                        $statusDate = $order->warehouse_confirmed_at;
                                    } elseif ($status === 'dispatched' && $order->dispatched_at) {
                                        $statusDate = $order->dispatched_at;
                                    } elseif ($status === 'delivered' && $order->delivered_at) {
                                        $statusDate = $order->delivered_at;
                                    } elseif ($status === 'pending') {
                                        $statusDate = $order->created_at;
                                    }
                                @endphp
                                <div class="relative flex items-center mb-6 last:mb-0">
                                    <div class="absolute left-0 w-12 h-12 flex items-center justify-center {{ $isPast || $isActive ? 'text-gray-900' : 'text-gray-400' }}">
                                        <span class="text-2xl">{{ $details['icon'] }}</span>
                                    </div>
                                    <div class="ml-16 flex items-center justify-between w-full">
                                        <div>
                                            <h4 class="text-sm font-medium {{ $isPast || $isActive ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $details['title'] }}
                                            </h4>
                                            @if($statusDate && ($isPast || $isActive))
                                                <p class="text-sm text-gray-500">{{ $statusDate->format('M d, Y H:i') }}</p>
                                            @endif
                                        </div>
                                        
                                        @if($details['action'] && $isActive)
                                            <form action="{{ route('seller.orders.update.status', ['order' => $order->id]) }}" method="POST" class="ml-4">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $details['action'] }}">
                                                <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 text-sm">
                                                    Mark as {{ ucwords(str_replace('_', ' ', $details['action'])) }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-4">Order Items</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                @php
                                                    $product = $item->item;
                                                    $imageUrl = null;
                                                    $itemName = '';
                                                    $itemDesc = '';
                                                    $itemType = '';
                                                    
                                                    if ($product) {
                                                        if ($product instanceof \App\Models\Product) {
                                                            $imageUrl = $product->image_path ? asset('storage/' . $product->image_path) : null;
                                                            $itemName = $product->product_name;
                                                            $itemDesc = $product->description;
                                                            $itemType = 'Product';
                                                        } elseif ($product instanceof \App\Models\Bundle) {
                                                            $imageUrl = $product->bundle_image ? asset('storage/' . $product->bundle_image) : null;
                                                            $itemName = $product->bundle_name;
                                                            $itemDesc = $product->description;
                                                            $itemType = 'Bundle';
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($imageUrl)
                                                    <img src="{{ $imageUrl }}" alt="{{ $itemName }}" class="w-16 h-16 object-cover rounded-md mr-4">
                                                @else
                                                    <div class="w-16 h-16 bg-gray-200 rounded-md mr-4 flex items-center justify-center">
                                                        <span class="text-gray-400 text-xs">No Image</span>
                                                    </div>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        @if($product)
                                                            {{ $itemName }}
                                                            <span class="text-gray-500 text-xs">({{ $itemType }})</span>
                                                        @else
                                                            <span class="text-red-500">Item no longer available</span>
                                                        @endif
                                                    </div>
                                                    @if($product)
                                                        <div class="text-sm text-gray-500 mt-1">
                                                            {{ Str::limit($itemDesc, 150) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            Rs. {{ number_format($item->price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            Rs. {{ number_format($item->price * $item->quantity, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Update Status -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium mb-4">Update Order Status</h3>
                        @if(!in_array($order->delivery_status, ['delivered_to_warehouse', 'dispatched', 'delivered']))
                            <form action="{{ route('seller.orders.update.status', ['order' => $order->id]) }}" method="POST" class="flex items-center space-x-4">
                                @csrf
                                <input type="hidden" name="status" value="delivered_to_warehouse">
                                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    Mark as Delivered to Warehouse
                                </button>
                            </form>
                            @if ($errors->any())
                                <div class="mt-2 text-red-600">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="mt-2 text-red-600">
                                    {{ session('error') }}
                                </div>
                            @endif
                            @if (session('success'))
                                <div class="mt-2 text-green-600">
                                    {{ session('success') }}
                                </div>
                            @endif
                        @else
                            <p class="text-gray-600">Order is being processed automatically. Current status: {{ ucfirst(str_replace('_', ' ', $order->delivery_status)) }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

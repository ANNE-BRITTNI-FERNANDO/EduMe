<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">Order #{{ $order->id }}</h2>
                        <div class="flex space-x-4">
                            <a href="{{ route('home') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Back to Home
                            </a>
                            <a href="{{ route('orders.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300">
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
                                <h3 class="text-lg font-medium mb-4">Seller Information</h3>
                                @foreach($order->items->unique('seller_id') as $orderItem)
                                    <div class="mb-4">
                                        <p class="text-gray-600"><span class="font-medium">Name:</span> {{ $orderItem->seller->name }}</p>
                                        <p class="text-gray-600"><span class="font-medium">Email:</span> {{ $orderItem->seller->email }}</p>
                                        <!-- Display seller rating -->
                                        <div class="mt-2">
                                            <x-seller-rating :sellerId="$orderItem->seller_id" />
                                        </div>
                                    </div>
                                @endforeach
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
                                    'pending' => ['icon' => '🔵', 'title' => 'Order Pending'],
                                    'delivered_to_warehouse' => ['icon' => '📦', 'title' => 'Delivered to Warehouse'],
                                    'dispatched' => ['icon' => '🚚', 'title' => 'Dispatched'],
                                    'delivered' => ['icon' => '✅', 'title' => 'Delivered']
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
                                    <div class="ml-16">
                                        <h4 class="text-sm font-medium {{ $isPast || $isActive ? 'text-gray-900' : 'text-gray-400' }}">
                                            {{ $details['title'] }}
                                        </h4>
                                        @if($statusDate && ($isPast || $isActive))
                                            <p class="text-sm text-gray-500">{{ $statusDate->format('M d, Y H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($currentStatus === 'delivered')
                            <div class="mt-6">
                                <form action="{{ route('orders.confirm-delivery', ['order' => $order->id]) }}" method="POST" class="flex items-center space-x-4">
                                    @csrf
                                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        Confirm Delivery Received
                                    </button>
                                </form>
                            </div>
                        @endif
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
                                            Quantity
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($order->items as $item)
                                        @php
                                            $product = $item->item;
                                            $imageUrl = null;
                                            $itemName = '';
                                            $itemDesc = '';
                                            
                                            if ($product) {
                                                if ($product instanceof \App\Models\Product) {
                                                    $imageUrl = $product->image_path ? asset('storage/' . $product->image_path) : null;
                                                    $itemName = $product->product_name;
                                                    $itemDesc = $product->description;
                                                } elseif ($product instanceof \App\Models\Bundle) {
                                                    $imageUrl = $product->bundle_image ? asset('storage/' . $product->bundle_image) : null;
                                                    $itemName = $product->bundle_name;
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($imageUrl)
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $imageUrl }}" alt="{{ $itemName }}">
                                                        </div>
                                                    @endif
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $itemName }}</div>
                                                        @if($itemDesc)
                                                            <div class="text-sm text-gray-500">{{ Str::limit($itemDesc, 50) }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Rs. {{ number_format($item->price, 2) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $item->quantity }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">Rs. {{ number_format($item->price * $item->quantity, 2) }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            Subtotal:
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rs. {{ number_format($order->total_amount, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    @if($order->status === 'completed' && !$order->sellerRating)
                        <div class="mt-6 bg-white shadow sm:rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Rate Your Experience</h3>
                                <div class="mt-2 max-w-xl text-sm text-gray-500">
                                    <p>Please rate your experience with this seller.</p>
                                </div>
                                <form action="{{ route('seller.ratings.store', ['order' => $order->id]) }}" method="POST" class="mt-5" onsubmit="return validateRating()">
                                    @csrf
                                    <input type="hidden" name="seller_id" value="{{ $orderItem->seller_id }}">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Rating</label>
                                            <div class="mt-1 flex items-center space-x-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <button type="button" onclick="setRating({{ $i }})" class="rating-star text-2xl text-gray-300 hover:text-yellow-400 focus:outline-none">★</button>
                                                @endfor
                                                <input type="hidden" name="rating" id="rating-input" required>
                                            </div>
                                            <div id="rating-error" class="text-red-500 text-sm mt-1 hidden">Please select a rating before submitting.</div>
                                        </div>
                                        <div>
                                            <label for="comment" class="block text-sm font-medium text-gray-700">Comment (Optional)</label>
                                            <textarea id="comment" name="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                        </div>
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="is_anonymous" name="is_anonymous" type="checkbox" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="is_anonymous" class="font-medium text-gray-700">Post Anonymously</label>
                                                <p class="text-gray-500">Your name will not be displayed with this review</p>
                                            </div>
                                        </div>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Submit Review
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                    
                    <script>
                        function setRating(value) {
                            document.getElementById('rating-input').value = value;
                            const stars = document.querySelectorAll('.rating-star');
                            stars.forEach((star, index) => {
                                star.classList.toggle('text-yellow-400', index < value);
                                star.classList.toggle('text-gray-300', index >= value);
                            });
                            document.getElementById('rating-error').classList.add('hidden');
                        }

                        function validateRating() {
                            const ratingInput = document.getElementById('rating-input');
                            if (!ratingInput.value) {
                                document.getElementById('rating-error').classList.remove('hidden');
                                return false;
                            }
                            return true;
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
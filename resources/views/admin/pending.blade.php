<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Pending Products</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('admin.pending') }}" method="GET" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort By</label>
                            <select name="sort" class="form-select block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700" onchange="this.form.submit()">
                                <option value="latest" {{ $currentSort === 'latest' ? 'selected' : '' }}>Latest First</option>
                                <option value="oldest" {{ $currentSort === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Category</label>
                            <select name="category" class="form-select block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700" onchange="this.form.submit()">
                                <option value="all">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ $currentCategory === $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products List -->
            <div class="space-y-4">
                @forelse($products as $product)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between cursor-pointer" onclick="toggleDetails('product-{{ $product->id }}')">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 w-16 h-16">
                                        <img class="w-16 h-16 object-cover rounded-lg" 
                                             src="{{ asset('storage/' . $product->image_path) }}" 
                                             alt="{{ $product->product_name }}">
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $product->product_name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Added by {{ $product->user->name }} • {{ $product->created_at->diffForHumans() }}
                                            @if($product->rejection_reason)
                                                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Resubmitted
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                        LKR {{ number_format($product->price, 2) }}
                                    </span>
                                    <svg class="w-6 h-6 transform transition-transform duration-200" id="arrow-{{ $product->id }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Collapsible Details -->
                            <div class="hidden mt-6 border-t border-gray-200 dark:border-gray-700 pt-4" id="product-{{ $product->id }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Product Details</h4>
                                        <div class="space-y-2">
                                            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-medium">Category:</span> {{ $product->category }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-medium">Description:</span> {{ $product->description }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-medium">Quantity:</span> {{ $product->quantity }}</p>
                                            @if($product->rejection_reason)
                                                <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                                                    <h5 class="text-sm font-medium text-yellow-800 mb-2">Previous Rejection History</h5>
                                                    <p class="text-sm text-yellow-700">
                                                        <span class="font-medium">Reason:</span> {{ $product->rejection_reason }}
                                                    </p>
                                                    @if($product->rejection_note)
                                                        <p class="text-sm text-yellow-700 mt-1">
                                                            <span class="font-medium">Additional Notes:</span> {{ $product->rejection_note }}
                                                        </p>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Additional Images</h4>
                                        @if($product->productImages->isNotEmpty())
                                            <div class="grid grid-cols-3 gap-2">
                                                @foreach($product->productImages as $image)
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                         alt="Additional image" 
                                                         class="w-full h-24 object-cover rounded-lg">
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">No additional images</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-6 flex justify-end space-x-4">
                                    <button onclick="showRejectForm('{{ $product->id }}')" 
                                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        Reject
                                    </button>
                                    <form action="{{ route('admin.products.approve', $product->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                            Approve
                                        </button>
                                    </form>
                                </div>

                                <!-- Reject Form (Hidden by default) -->
                                <div id="reject-form-{{ $product->id }}" class="hidden mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <form action="{{ route('admin.products.reject', $product->id) }}" method="POST">
                                        @csrf
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rejection Reason</label>
                                                <select name="rejection_reason" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                                    <option value="">Select a reason</option>
                                                    <option value="inappropriate">Inappropriate Content</option>
                                                    <option value="quality">Poor Quality</option>
                                                    <option value="pricing">Incorrect Pricing</option>
                                                    <option value="description">Insufficient Description</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Additional Notes</label>
                                                <textarea name="rejection_note" rows="3" 
                                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800"
                                                          placeholder="Provide additional details about the rejection..."></textarea>
                                            </div>
                                            <div class="flex justify-end">
                                                <button type="submit" 
                                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                    Confirm Rejection
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No pending products found</p>
                    </div>
                @endforelse

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for toggling details -->
    <script>
        function toggleDetails(id) {
            const details = document.getElementById(id);
            const arrow = document.getElementById('arrow-' + id.split('-')[1]);
            details.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        function showRejectForm(productId) {
            const form = document.getElementById('reject-form-' + productId);
            form.classList.toggle('hidden');
        }
    </script>
</x-app-layout>

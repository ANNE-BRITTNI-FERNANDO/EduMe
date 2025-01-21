<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Approved Products</h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.pending') }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-200 ease-in-out">
                    View Pending Products
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Approved Products List</h3>
                    
                    @if($products->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400">No approved products found.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Image</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Seller</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($products as $product)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex-shrink-0 h-20 w-20">
                                                    <img class="h-20 w-20 object-cover rounded-lg" 
                                                         src="{{ asset('storage/' . $product->image_path) }}" 
                                                         alt="{{ $product->product_name }}">
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $product->product_name }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Added by {{ $product->user->name }} • {{ $product->created_at->diffForHumans() }}
                                                </div>
                                                <!-- Dropdown Button -->
                                                <button onclick="toggleDetails('product-{{ $product->id }}')" 
                                                        class="mt-2 text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 flex items-center">
                                                    <span>View Details</span>
                                                    <svg class="ml-1 w-4 h-4 transition-transform duration-200" id="arrow-{{ $product->id }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ Str::limit($product->description, 100) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-white">
                                                    LKR {{ number_format($product->price, 2) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $product->category }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $product->user->name }}
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Dropdown Content -->
                                        <tr id="product-{{ $product->id }}" class="hidden bg-gray-50 dark:bg-gray-700">
                                            <td colspan="6" class="px-6 py-4">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Product Details</h4>
                                                        <div class="space-y-2">
                                                            <p class="text-sm">
                                                                <span class="font-medium">Category:</span> 
                                                                <span class="text-gray-600 dark:text-gray-300">{{ $product->category }}</span>
                                                            </p>
                                                            <p class="text-sm">
                                                                <span class="font-medium">Description:</span> 
                                                                <span class="text-gray-600 dark:text-gray-300">{{ $product->description }}</span>
                                                            </p>
                                                            <p class="text-sm">
                                                                <span class="font-medium">Quantity:</span> 
                                                                <span class="text-gray-600 dark:text-gray-300">{{ $product->quantity }}</span>
                                                            </p>
                                                            <p class="text-sm">
                                                                <span class="font-medium">Added:</span> 
                                                                <span class="text-gray-600 dark:text-gray-300">{{ $product->created_at->format('F j, Y, g:i a') }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Additional Images</h4>
                                                        <div class="grid grid-cols-3 gap-2">
                                                            @foreach($product->productImages as $image)
                                                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                                     alt="Additional image" 
                                                                     class="h-20 w-20 object-cover rounded-lg">
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination Links -->
                        <div class="mt-6">
                            @if(method_exists($products, 'links'))
                                {{ $products->links() }}
                            @else
                                <div class="flex justify-center">
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <!-- Previous Page Link -->
                                        @if(method_exists($products, 'previousPageUrl'))
                                            <a href="{{ $products->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Previous</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        @endif

                                        <!-- Next Page Link -->
                                        @if(method_exists($products, 'nextPageUrl'))
                                            <a href="{{ $products->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Next</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        @endif
                                    </nav>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleDetails(id) {
            const element = document.getElementById(id);
            const arrowId = id.replace('product-', 'arrow-');
            const arrow = document.getElementById(arrowId);
            
            if (element && arrow) {
                element.classList.toggle('hidden');
                // Rotate arrow when expanded
                if (element.classList.contains('hidden')) {
                    arrow.style.transform = 'rotate(0deg)';
                } else {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        }
    </script>
    @endpush
</x-app-layout>

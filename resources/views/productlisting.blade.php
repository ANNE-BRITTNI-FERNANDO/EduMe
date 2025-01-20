<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-indigo-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4 md:mb-0">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">
                        Discover Products
                    </span>
                </h1>
                <div class="flex space-x-4">
                    <a href="{{ route('shop.bundles') }}" 
                       class="group px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-500 rounded-lg hover:from-blue-700 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            View Bundles
                        </span>
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div x-data="{ showFilters: false }" class="space-y-4 mb-8">
                <!-- Primary Filters (Always Visible) -->
                <div class="flex flex-col lg:flex-row gap-3">
                    <!-- Search Input -->
                    <div class="flex-1 relative">
                        <form id="quickFilterForm" method="GET" action="{{ route('productlisting') }}" class="m-0">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search products..." 
                                   class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white"
                                   x-on:input.debounce.500ms="$el.form.submit()">
                            <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </form>
                    </div>

                    <!-- Category Dropdown (Desktop Only) -->
                    <div class="hidden lg:block w-48">
                        <select name="category" 
                                form="quickFilterForm"
                                class="w-full px-3 py-2.5 bg-white dark:bg-gray-800 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white"
                                x-on:change="$el.form.submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Toggle & Clear -->
                    <div class="flex gap-2">
                        <!-- More Filters Toggle -->
                        <button type="button" 
                                x-on:click="showFilters = !showFilters"
                                class="px-4 py-2 bg-blue-50 dark:bg-gray-800 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-700 border border-blue-200 dark:border-gray-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            <span class="hidden sm:inline" x-text="showFilters ? 'Hide Filters' : 'More Filters'">More Filters</span>
                        </button>

                        <!-- Clear Filters -->
                        @if(request()->anyFilled(['search', 'category', 'location', 'min_price', 'max_price', 'sort']))
                            <a href="{{ route('productlisting') }}" 
                               class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="hidden sm:inline">Clear</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div x-show="showFilters"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-blue-200 dark:border-gray-700 p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Category (Mobile Only) -->
                        <div class="lg:hidden">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Category</label>
                            <select name="category" 
                                    form="quickFilterForm"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white"
                                    x-on:change="$el.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Location Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Location</label>
                            <select name="location" 
                                    form="quickFilterForm"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white"
                                    x-on:change="$el.form.submit()">
                                <option value="">All Locations</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                        {{ $location }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Price Range</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <input type="number" 
                                           name="min_price" 
                                           form="quickFilterForm"
                                           value="{{ request('min_price') }}" 
                                           placeholder="Min" 
                                           class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white"
                                           x-on:input.debounce.500ms="$el.form.submit()">
                                </div>
                                <div>
                                    <input type="number" 
                                           name="max_price" 
                                           form="quickFilterForm"
                                           value="{{ request('max_price') }}" 
                                           placeholder="Max" 
                                           class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white"
                                           x-on:input.debounce.500ms="$el.form.submit()">
                                </div>
                            </div>
                        </div>

                        <!-- Sort Options -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Sort By</label>
                            <select name="sort" 
                                    form="quickFilterForm"
                                    class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white"
                                    x-on:change="$el.form.submit()">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(request('search') || request('category') || request('location') || request('min_price') || request('max_price') || request('sort'))
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400 mr-2">Active Filters:</span>
                    
                    @if(request('search'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Search: {{ request('search') }}
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('search'), [])) }}" class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </span>
                    @endif

                    @if(request('category'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            Category: {{ request('category') }}
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('category'), [])) }}" class="ml-2 text-green-600 dark:text-green-400 hover:text-green-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </span>
                    @endif

                    @if(request('location'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                            Location: {{ request('location') }}
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('location'), [])) }}" class="ml-2 text-purple-600 dark:text-purple-400 hover:text-purple-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </span>
                    @endif

                    @if(request('min_price') || request('max_price'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                            Price: 
                            @if(request('min_price') && request('max_price'))
                                LKR {{ request('min_price') }} - {{ request('max_price') }}
                            @elseif(request('min_price'))
                                Min LKR {{ request('min_price') }}
                            @elseif(request('max_price'))
                                Max LKR {{ request('max_price') }}
                            @endif
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except(['min_price', 'max_price']), [])) }}" class="ml-2 text-orange-600 dark:text-orange-400 hover:text-orange-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </span>
                    @endif

                    @if(request('sort'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            Sort: {{ request('sort') == 'oldest' ? 'Oldest First' : (request('sort') == 'price_low' ? 'Price: Low to High' : (request('sort') == 'price_high' ? 'Price: High to Low' : 'Newest First')) }}
                            <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->except('sort'), [])) }}" class="ml-2 text-yellow-600 dark:text-yellow-400 hover:text-yellow-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </span>
                    @endif

                    @if(request('search') || request('category') || request('location') || request('min_price') || request('max_price') || request('sort'))
                        <a href="{{ route('productlisting') }}" 
                           class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-800 transition-colors duration-200">
                            Clear All Filters
                        </a>
                    @endif
                </div>
            @endif

            <!-- Products Grid -->
            @if($approvedProducts->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No Products Found</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Try adjusting your search or filter criteria</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($approvedProducts as $product)
                        <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl transform hover:-translate-y-1">
                            <div class="relative h-64 bg-blue-100 dark:bg-gray-700">
                                @if($product->image_path)
                                    <div class="relative">
                                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->product_name }}" class="w-full h-48 object-cover">
                                        @if($product->is_sold)
                                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                                <span class="text-white text-lg font-bold px-4 py-2 bg-red-500 rounded-lg">SOLD OUT</span>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="h-full flex items-center justify-center text-blue-300 dark:text-gray-400">
                                        <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute top-4 right-4">
                                    <span class="px-3 py-1 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-500 rounded-full">
                                        {{ $product->category }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ $product->product_name }}</h3>
                                <div class="mt-2 flex items-center text-sm text-blue-500 dark:text-blue-400">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $product->user->location ?? 'Location not specified' }}
                                </div>
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="text-xl font-bold text-gray-900 dark:text-white">Rs {{ number_format($product->price, 2) }}</span>
                                    <a href="{{ route('product.show', $product->id) }}" 
                                       class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-500 rounded-lg hover:from-blue-700 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    <div class="bg-white dark:bg-gray-800 px-4 py-3 rounded-xl shadow-lg border border-blue-100 dark:border-gray-700">
                        {{ $approvedProducts->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Debounce function to limit how often the form submits
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Function to submit the form
        const submitForm = () => {
            document.getElementById('filterForm').submit();
        };

        // Debounced version of the submit function (waits 500ms after last input)
        const debouncedSubmit = debounce(submitForm, 500);

        // Add event listeners to all filter inputs
        document.querySelectorAll('.filter-input').forEach(input => {
            if (input.type === 'number') {
                // For number inputs (price range), use debounced submit
                input.addEventListener('input', debouncedSubmit);
                // Also submit when user leaves the input field
                input.addEventListener('blur', submitForm);
            } else if (input.tagName === 'SELECT') {
                // For select elements, submit immediately on change
                input.addEventListener('change', submitForm);
            } else {
                // For text inputs (search), use debounced submit
                input.addEventListener('input', debouncedSubmit);
            }
        });
    </script>
</x-app-layout>

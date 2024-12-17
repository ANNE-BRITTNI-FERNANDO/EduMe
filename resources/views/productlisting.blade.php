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
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-8 transition-all duration-300 hover:shadow-xl border border-blue-100 dark:border-gray-700">
                <form method="GET" action="{{ route('productlisting') }}" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                        <!-- Search Input -->
                        <div class="col-span-1 md:col-span-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Products</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Search by name or description..." 
                                       class="w-full pl-10 pr-4 py-3 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
                                <svg class="absolute left-3 top-3.5 h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                            <select name="category" 
                                    class="w-full py-3 px-4 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
                            <select name="location" 
                                    class="w-full py-3 px-4 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
                                <option value="">All Locations</option>
                                @if(isset($locations))
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                            {{ $location }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price Range</label>
                            <div class="flex items-center space-x-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-3 text-gray-500">Rs</span>
                                    <input type="number" name="min_price" value="{{ request('min_price') }}" 
                                           placeholder="Min" min="0" step="0.01"
                                           class="w-full pl-8 pr-4 py-3 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
                                </div>
                                <span class="text-gray-500">-</span>
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-3 text-gray-500">Rs</span>
                                    <input type="number" name="max_price" value="{{ request('max_price') }}" 
                                           placeholder="Max" min="0" step="0.01"
                                           class="w-full pl-8 pr-4 py-3 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        <div class="w-full sm:w-auto">
                            <select name="sort_date" 
                                    class="w-full sm:w-48 py-3 px-4 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
                                <option value="">Sort by Date</option>
                                <option value="desc" {{ request('sort_date') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                <option value="asc" {{ request('sort_date') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </div>
                        <div class="flex space-x-4">
                            <a href="{{ route('productlisting') }}" 
                               class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-300 transform hover:scale-105">
                                Reset Filters
                            </a>
                            <button type="submit" 
                                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-500 text-white rounded-lg hover:from-blue-700 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="space-y-8">
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
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

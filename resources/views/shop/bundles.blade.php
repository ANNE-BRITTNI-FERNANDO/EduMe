<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-indigo-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Section -->
            <div class="flex flex-col text-center mb-12">
                <h1 class="text-4xl font-bold mb-4">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">
                        Exclusive Bundles
                    </span>
                </h1>
                <p class="text-gray-600 dark:text-gray-300 text-lg max-w-2xl mx-auto">
                    Discover our carefully curated bundles designed to give you the best value for your money.
                </p>
            </div>

            <!-- Filter Section -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-8 transition-all duration-300 hover:shadow-xl border border-blue-100 dark:border-gray-700">
                <form method="GET" action="{{ route('shop.bundles') }}" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Search Input -->
                        <div class="col-span-1 md:col-span-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Bundles</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Search by bundle name or description..." 
                                       class="w-full pl-10 pr-4 py-3 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
                                <svg class="absolute left-3 top-3.5 h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Seller Location</label>
                            <select name="location" 
                                    class="w-full py-3 px-4 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
                                <option value="">All Locations</option>
                                @foreach($locations ?? [] as $location)
                                    <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                        {{ $location }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range Filters -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min Price</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-500">Rs</span>
                                <input type="number" name="min_price" value="{{ request('min_price') }}" min="0" step="0.01"
                                       class="w-full pl-8 pr-4 py-3 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300"
                                       placeholder="Min price">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Price</label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-gray-500">Rs</span>
                                <input type="number" name="max_price" value="{{ request('max_price') }}" min="0" step="0.01"
                                       class="w-full pl-8 pr-4 py-3 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300"
                                       placeholder="Max price">
                            </div>
                        </div>

                        <!-- Date Sort -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort by Date</label>
                            <select name="sort_date" 
                                    class="w-full py-3 px-4 border border-blue-200 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-all duration-300">
                                <option value="">No Sort</option>
                                <option value="desc" {{ request('sort_date') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                <option value="asc" {{ request('sort_date') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex flex-col sm:flex-row justify-end items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-6">
                        <a href="{{ route('shop.bundles') }}" 
                           class="w-full sm:w-auto px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-300">
                            Reset Filters
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            @if($bundles->isEmpty())
                <!-- Empty State -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 text-center max-w-lg mx-auto border border-blue-100 dark:border-gray-700">
                    <div class="flex flex-col items-center">
                        <svg class="h-16 w-16 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No Bundles Found</h3>
                        <p class="text-gray-600 dark:text-gray-400">We couldn't find any bundles matching your criteria. Try adjusting your filters or check back later.</p>
                    </div>
                </div>
            @else
                <!-- Bundles Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($bundles as $bundle)
                        <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl transform hover:-translate-y-2 border border-blue-100 dark:border-gray-700">
                            <!-- Bundle Image -->
                            <div class="relative h-64 bg-blue-50 dark:bg-gray-700 overflow-hidden">
                                @if($bundle->bundle_image)
                                    <img src="{{ asset('storage/' . $bundle->bundle_image) }}" 
                                         alt="{{ $bundle->bundle_name }}"
                                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-300">
                                @else
                                    <div class="flex items-center justify-center h-full">
                                        <svg class="h-24 w-24 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Bundle Content -->
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                    {{ $bundle->bundle_name }}
                                </h3>

                                <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-2">
                                    {{ $bundle->description }}
                                </p>

                                <!-- Bundle Features -->
                                <div class="space-y-2 mb-6">
                                    <!-- Location -->
                                    @if($bundle->user && $bundle->user->location)
                                        <div class="flex items-center text-gray-600 dark:text-gray-400">
                                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>{{ $bundle->user->location }}</span>
                                        </div>
                                    @endif

                                    <!-- Price -->
                                    <div class="flex items-center text-blue-600 dark:text-blue-400">
                                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-bold">Rs {{ number_format($bundle->price, 2) }}</span>
                                    </div>

                                    <!-- Date -->
                                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $bundle->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <a href="{{ route('bundles.show', $bundle->id) }}" 
                                   class="block w-full text-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-500 text-white rounded-lg hover:from-blue-700 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300">
                                    View Bundle
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
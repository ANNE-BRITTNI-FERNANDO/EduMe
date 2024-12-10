<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <i class="fas fa-box-open mr-2 text-primary"></i> {{ __('Bundle Details') }}
        </h2>
    </x-slot>

    <div class="bg-gray-100 dark:bg-gray-900 py-12">
        <div class="container mx-auto px-4">
            <!-- Single Bundle Details Section -->
            <div class="flex flex-col lg:flex-row items-start lg:space-x-12 space-y-8 lg:space-y-0">
                <!-- Product Image Section -->
                <div class="relative w-full lg:w-1/2 max-w-lg mx-auto">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-4">
                        @if($bundle->bundle_image)
                            <img src="{{ asset('storage/' . $bundle->bundle_image) }}" 
                                 class="w-full h-auto object-cover rounded-lg transition-transform duration-300 transform hover:scale-105"
                                 alt="{{ $bundle->bundle_name }}">
                        @else
                            <div class="w-full h-96 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Product Details Section -->
                <div class="w-full lg:w-1/2 space-y-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-4xl font-bold text-gray-900 dark:text-white">{{ $bundle->bundle_name }}</h1>
                            @if($bundle->user->location)
                                <div class="flex items-center mt-2 text-gray-600 dark:text-gray-400">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $bundle->user->location }}
                                </div>
                            @endif
                        </div>
                        <span class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-500 text-white text-2xl font-bold rounded-full shadow-lg">
                            ${{ number_format($bundle->price, 2) }}
                        </span>
                    </div>

                    <div class="border-t border-b border-gray-200 dark:border-gray-700 py-6 my-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Description</h3>
                        <p class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed">{{ $bundle->description }}</p>
                    </div>

                    <div class="space-y-4">
                        <!-- Seller Info -->
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-600 to-indigo-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                    {{ strtoupper(substr($bundle->user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Sold by</p>
                                <p class="text-lg font-semibold text-purple-600 dark:text-purple-400">{{ $bundle->user->name }}</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 mt-8">
                            @auth
                                @if(auth()->id() !== $bundle->user_id)
                                    <button onclick="window.addToCart('bundle', {{ $bundle->id }})"
                                            class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-center font-medium rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <span class="flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Add to Cart
                                        </span>
                                    </button>

                                    <a href="{{ route('chat.start.bundle', ['id' => $bundle->id]) }}" 
                                       class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-500 text-white text-center font-medium rounded-lg hover:from-purple-700 hover:to-indigo-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <span class="flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                            Chat with Seller
                                        </span>
                                    </a>
                                @else
                                    <a href="{{ route('bundle.edit', $bundle->id) }}" 
                                       class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-500 text-white text-center font-medium rounded-lg hover:from-blue-600 hover:to-cyan-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                        <span class="flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit Bundle
                                        </span>
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}" 
                                   class="flex-1 px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-center font-medium rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-200">
                                    Login to Interact
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Section -->
            @if($bundle->categories && $bundle->categories->count() > 0)
                <div class="mt-12">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Bundle Categories</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach($bundle->categories as $category)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
                                @if($category->category_image)
                                    <img src="{{ asset('storage/' . $category->category_image) }}" 
                                         alt="{{ $category->category }}" 
                                         class="w-full h-40 object-cover">
                                @else
                                    <div class="w-full h-40 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white text-center">{{ $category->category }}</h4>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4 text-center">
            <p class="text-sm text-gray-400">&copy; {{ date('Y') }} Edume. All rights reserved.</p>
        </div>
    </footer>
</x-app-layout>

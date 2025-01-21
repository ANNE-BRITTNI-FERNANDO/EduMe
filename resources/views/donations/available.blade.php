<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Available Donations
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('donations.available') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                <select name="category" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">All Categories</option>
                                    <option value="textbooks" {{ request('category') == 'textbooks' ? 'selected' : '' }}>Textbooks</option>
                                    <option value="stationery" {{ request('category') == 'stationery' ? 'selected' : '' }}>Stationery</option>
                                    <option value="devices" {{ request('category') == 'devices' ? 'selected' : '' }}>Electronic Devices</option>
                                    <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <!-- Education Level -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Education Level</label>
                                <select name="education_level" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">All Levels</option>
                                    <option value="primary" {{ request('education_level') == 'primary' ? 'selected' : '' }}>Primary School</option>
                                    <option value="secondary" {{ request('education_level') == 'secondary' ? 'selected' : '' }}>Secondary School</option>
                                    <option value="higher_secondary" {{ request('education_level') == 'higher_secondary' ? 'selected' : '' }}>Higher Secondary</option>
                                    <option value="undergraduate" {{ request('education_level') == 'undergraduate' ? 'selected' : '' }}>Undergraduate</option>
                                    <option value="postgraduate" {{ request('education_level') == 'postgraduate' ? 'selected' : '' }}>Postgraduate</option>
                                </select>
                            </div>

                            <!-- Condition -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condition</label>
                                <select name="condition" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">Any Condition</option>
                                    <option value="new" {{ request('condition') == 'new' ? 'selected' : '' }}>New</option>
                                    <option value="like_new" {{ request('condition') == 'like_new' ? 'selected' : '' }}>Like New</option>
                                    <option value="good" {{ request('condition') == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ request('condition') == 'fair' ? 'selected' : '' }}>Fair</option>
                                </select>
                            </div>

                            <!-- Location -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                <select name="location" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="">Any Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $location)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Apply Button -->
                            <div class="flex items-end">
                                <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Available Items Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($donations as $donation)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="relative">
                            @php
                                $images = is_string($donation->images) ? json_decode($donation->images, true) : $donation->images;
                                $hasImages = !empty($images) && is_array($images);
                            @endphp
                            
                            @if($hasImages)
                                <img src="{{ asset('storage/' . $images[0]) }}" 
                                    alt="{{ $donation->category }}" 
                                    class="w-full h-48 object-cover"
                                    onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                            @else
                                <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif

                            <!-- Item Details -->
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $donation->item_name }}</h3>
                                <div class="mt-2 space-y-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Category:</span> {{ ucfirst($donation->category) }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Education Level:</span> {{ ucfirst($donation->education_level) }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Condition:</span> {{ ucfirst(str_replace('_', ' ', $donation->condition)) }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Available Quantity:</span> {{ $donation->available_quantity }}
                                    </p>
                                    @if($donation->user && $donation->user->location)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-medium">Location:</span> 
                                            <span class="inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                {{ $donation->user->location }}
                                            </span>
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-4 flex justify-end">
                                    @if($donation->user_id !== auth()->id())
                                        <a href="{{ route('donations.request.form', $donation) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Request Item
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase">
                                            Your Donation
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No donations found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Try adjusting your filters or check back later for new donations.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $donations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

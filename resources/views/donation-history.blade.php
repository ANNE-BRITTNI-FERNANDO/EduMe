<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Donation History
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('donations.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Donate Items
                </a>
                <a href="{{ route('donations.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Donor Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">My Donations</h3>
                        
                        <!-- Filter -->
                        <div class="flex gap-4">
                            <select id="statusFilter" onchange="filterDonations()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            
                            <select id="categoryFilter" onchange="filterDonations()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="all">All Categories</option>
                                <option value="books">Books</option>
                                <option value="stationery">Stationery</option>
                                <option value="uniforms">Uniforms</option>
                                <option value="electronics">Electronics</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    @if($donations->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">No donations found.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($donations as $donation)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 donation-card" 
                                     data-status="{{ $donation->status }}"
                                     data-category="{{ $donation->category }}">
                                    <div class="relative">
                                        <!-- Delete Button -->
                                        <form action="{{ route('donations.destroy', $donation) }}" 
                                              method="POST" 
                                              class="absolute top-2 right-2 z-10"
                                              onsubmit="return confirm('Are you sure you want to delete this donation?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-500 text-white rounded-full p-2 hover:bg-red-600 focus:outline-none">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>

                                        <!-- Image Slider -->
                                        @php
                                            $hasImage = false;
                                            $images = is_string($donation->images) ? json_decode($donation->images, true) : $donation->images;
                                            $hasImage = !empty($images) && is_array($images);
                                            $imageId = 'donation-images-' . $donation->id;
                                        @endphp

                                        <div class="relative mb-4">
                                            @if($hasImage)
                                                <div class="relative h-48" id="{{ $imageId }}">
                                                    @foreach($images as $index => $image)
                                                        <img src="{{ Storage::url($image) }}" 
                                                             alt="{{ $donation->item_name }}" 
                                                             class="absolute w-full h-48 object-cover rounded-lg transition-opacity duration-300 ease-in-out {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
                                                             data-index="{{ $index }}"
                                                             onerror="this.src='{{ asset('images/placeholder.jpg') }}'; this.onerror=null;">
                                                    @endforeach
                                                </div>
                                                @if(count($images) > 1)
                                                    <button type="button" onclick="changeImage('{{ $imageId }}', -1)" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-75 focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" onclick="changeImage('{{ $imageId }}', 1)" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-75 focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </button>
                                                    <div class="absolute bottom-2 left-0 right-0 flex justify-center space-x-2">
                                                        @foreach($images as $index => $image)
                                                            <button type="button" onclick="showImage('{{ $imageId }}', {{ $index }})" 
                                                                    class="w-2 h-2 rounded-full bg-white bg-opacity-50 hover:bg-opacity-100 focus:outline-none transition-all duration-300"
                                                                    data-index="{{ $index }}">
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @else
                                                <img src="{{ asset('images/placeholder.jpg') }}" 
                                                     alt="No Image Available" 
                                                     class="w-full h-48 object-cover rounded-lg">
                                            @endif
                                        </div>

                                        <h4 class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ $donation->item_name }}</h4>
                                        <p class="text-gray-600 dark:text-gray-400 mb-2">{{ $donation->description }}</p>
                                        
                                        <div class="grid grid-cols-2 gap-2 mb-4">
                                            <div>
                                                <span class="text-sm font-medium text-gray-500">Category:</span>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($donation->category) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-500">Quantity:</span>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $donation->quantity }}</span>
                                            </div>
                                            <!-- Status Badge -->
                                            <div class="col-span-2 mt-2">
                                                <span class="text-sm font-medium text-gray-500">Status:</span>
                                                @if($donation->status === 'pending')
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Pending Review
                                                    </span>
                                                @elseif($donation->status === 'approved')
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Approved
                                                    </span>
                                                @elseif($donation->status === 'rejected')
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Rejected
                                                    </span>
                                                    @if($donation->rejection_reason)
                                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                                            <span class="font-semibold">Reason:</span> {{ $donation->rejection_reason }}
                                                        </p>
                                                    @endif
                                                @else
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ ucfirst($donation->status) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-500">Education Level:</span>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($donation->education_level) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-500">Condition:</span>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $donation->condition)) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-500">Available:</span>
                                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $donation->available_quantity }}</span>
                                            </div>
                                        </div>

                                        @if($donation->donationRequests->isNotEmpty())
                                            <div class="mt-4">
                                                <h5 class="text-sm font-semibold mb-2">Requests:</h5>
                                                @foreach($donation->donationRequests as $request)
                                                    <div class="border-t border-gray-200 dark:border-gray-600 py-2">
                                                        <div class="flex justify-between items-center">
                                                            <div>
                                                                <p class="text-sm">{{ $request->user->name }}</p>
                                                                <p class="text-xs text-gray-500">Quantity: {{ $request->quantity }}</p>
                                                            </div>
                                                            <span class="px-2 py-1 text-xs rounded-full 
                                                                {{ $request->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                                   ($request->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                                   'bg-yellow-100 text-yellow-800') }}">
                                                                {{ ucfirst($request->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

<script>
    function changeImage(imageId, direction) {
        const container = document.getElementById(imageId);
        const images = container.querySelectorAll('img');
        let currentIndex = -1;

        // Find current visible image
        images.forEach((img, index) => {
            if (getComputedStyle(img).opacity === '1') {
                currentIndex = index;
            }
        });

        // Calculate new index
        let newIndex = currentIndex + direction;
        if (newIndex >= images.length) newIndex = 0;
        if (newIndex < 0) newIndex = images.length - 1;

        // Update visibility
        images.forEach((img, index) => {
            img.style.opacity = index === newIndex ? '1' : '0';
        });

        // Update dot indicators
        const dots = container.parentElement.querySelectorAll('button[data-index]');
        dots.forEach((dot, index) => {
            dot.classList.toggle('bg-opacity-100', index === newIndex);
            dot.classList.toggle('bg-opacity-50', index !== newIndex);
        });
    }

    function showImage(imageId, index) {
        const container = document.getElementById(imageId);
        const images = container.querySelectorAll('img');

        // Update visibility
        images.forEach((img, i) => {
            img.style.opacity = i === index ? '1' : '0';
        });

        // Update dot indicators
        const dots = container.parentElement.querySelectorAll('button[data-index]');
        dots.forEach((dot, i) => {
            dot.classList.toggle('bg-opacity-100', i === index);
            dot.classList.toggle('bg-opacity-50', i !== index);
        });
    }

    function filterDonations() {
        const statusFilter = document.getElementById('statusFilter').value;
        const categoryFilter = document.getElementById('categoryFilter').value;
        const donationCards = document.querySelectorAll('.donation-card');

        donationCards.forEach(card => {
            const status = card.dataset.status;
            const category = card.dataset.category;
            const statusMatch = statusFilter === 'all' || status === statusFilter;
            const categoryMatch = categoryFilter === 'all' || category === categoryFilter;

            if (statusMatch && categoryMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
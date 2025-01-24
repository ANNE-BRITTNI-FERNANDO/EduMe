<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Educational Item Donations
            </h2>
            <a href="{{ route('donations.history') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                View Donation History
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Item Donation Options -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Donate Items -->
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
                            <div class="flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-500 rounded-full mb-4 mx-auto">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-center mb-4 text-gray-900 dark:text-gray-100">Donate Educational Items</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center mb-6">Help students by donating educational materials, books, supplies, and other learning resources.</p>
                            <div class="text-center">
                                <a href="{{ route('donations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Donate Items Now
                                </a>
                            </div>
                        </div>

                        <!-- Browse Available Items -->
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
                            <div class="flex items-center justify-center w-16 h-16 bg-green-100 text-green-500 rounded-full mb-4 mx-auto">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-center mb-4 text-gray-900 dark:text-gray-100">Browse Available Items</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center mb-6">Find and request educational items that have been donated by others in your community.</p>
                            <div class="text-center">
                                <a href="{{ route('donations.available') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Browse Items
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <!-- My Donation Requests -->
             <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">My Donation Requests</h3>
                    
                    <!-- Instructions Section -->
                    <div class="mb-6 bg-blue-50 dark:bg-gray-700 p-4 rounded-lg">
                        <h4 class="text-blue-800 dark:text-blue-300 font-semibold mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Understanding Your Donation Requests
                        </h4>
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <p class="flex items-start">
                                <span class="text-yellow-600 dark:text-yellow-400 font-medium mr-2">•</span>
                                <span><strong>Pending Requests:</strong> These are your new requests waiting for donor approval. The donor will review your request and decide whether to approve or reject it.</span>
                            </p>
                            <p class="flex items-start">
                                <span class="text-green-600 dark:text-green-400 font-medium mr-2">•</span>
                                <span><strong>Approved Requests:</strong> These requests have been accepted by donors. You can now chat with the donor to arrange pickup/delivery details.</span>
                            </p>
                            <p class="flex items-start">
                                <span class="text-red-600 dark:text-red-400 font-medium mr-2">•</span>
                                <span><strong>Rejected Requests:</strong> These requests were not approved by the donors. You can view the rejection reason and try requesting other available items.</span>
                            </p>
                            <p class="flex items-start">
                                <span class="text-blue-600 dark:text-blue-400 font-medium mr-2">•</span>
                                <span><strong>Received Requests:</strong> If you're a donor, this tab shows requests from others for your donated items. You can approve or reject these requests.</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="pending-tab" 
                                        data-tabs-target="#pending" type="button" role="tab" 
                                        aria-controls="pending" aria-selected="false">
                                    Pending Requests
                                    @if($sentRequests->where('status', 'pending')->count() > 0)
                                        <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">
                                            {{ $sentRequests->where('status', 'pending')->count() }}
                                        </span>
                                    @endif
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="approved-tab"
                                        data-tabs-target="#approved" type="button" role="tab"
                                        aria-controls="approved" aria-selected="false">
                                    Approved Requests
                                    @if($sentRequests->where('status', 'approved')->count() > 0)
                                        <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                            {{ $sentRequests->where('status', 'approved')->count() }}
                                        </span>
                                    @endif
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="rejected-tab"
                                        data-tabs-target="#rejected" type="button" role="tab"
                                        aria-controls="rejected" aria-selected="false">
                                    Rejected Requests
                                    @if($sentRequests->where('status', 'rejected')->count() > 0)
                                        <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">
                                            {{ $sentRequests->where('status', 'rejected')->count() }}
                                        </span>
                                    @endif
                                </button>
                            </li>
                            <li role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="received-tab"
                                        data-tabs-target="#received" type="button" role="tab"
                                        aria-controls="received" aria-selected="false">
                                    Received Requests
                                    @if($receivedRequests->count() > 0)
                                        <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                            {{ $receivedRequests->count() }}
                                        </span>
                                    @endif
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div id="tabContents">
                        <!-- Pending Requests -->
                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            @if($sentRequests->where('status', 'pending')->count() > 0)
                                @foreach($sentRequests->where('status', 'pending') as $request)
                                    @if($request->donationItem)
                                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-gray-800 dark:to-gray-700 rounded-lg shadow-lg p-6 mb-6">
                                            <!-- Header -->
                                            <div class="flex items-center justify-between mb-4">
                                                <h5 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                                                    <span class="border-b-2 border-yellow-500">{{ $request->donationItem->item_name }}</span>
                                                </h5>
                                                <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100">
                                                    Pending
                                                </span>
                                            </div>

                                            <!-- Image Carousel -->
                                            <div class="relative mb-6 group">
                                                <div class="overflow-hidden rounded-lg h-64 relative">
                                                    @php
                                                        $images = $request->donationItem->images ?? [];
                                                    @endphp
                                                    <div class="flex transition-transform duration-500 ease-in-out" id="imageSlider-{{ $request->id }}" style="width: {{ count($images) * 100 }}%">
                                                        @foreach($images as $image)
                                                            <div class="min-w-full h-64">
                                                                <img src="{{ asset('storage/' . $image) }}" alt="Donation Item" class="w-full h-full object-contain">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @if(count($images) > 1)
                                                    <button onclick="moveSlide('{{ $request->id }}', -1)" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-r transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                        </svg>
                                                    </button>
                                                    <button onclick="moveSlide('{{ $request->id }}', 1)" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-l transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>

                                            <!-- Content Grid -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <!-- Left Column: Requester Information -->
                                                <div class="space-y-4">
                                                    <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                        <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200 flex items-center">
                                                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                            </svg>
                                                            Requester Information
                                                        </h6>
                                                        <div class="space-y-2 text-sm">
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Name:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->user->name }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Contact:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->user->phone }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Email:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->user->email }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Quantity:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->quantity }}</span>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Request Details -->
                                                    <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                        <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200 flex items-center">
                                                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            Request Details
                                                        </h6>
                                                        <div class="space-y-3">
                                                            <div>
                                                                <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Purpose:</span>
                                                                <p class="text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700 p-2 rounded text-sm">
                                                                    {{ $request->purpose }}
                                                                </p>
                                                            </div>
                                                            @if($request->verification_details)
                                                                <div class="pt-2">
                                                                    <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Verification:</span>
                                                                    <span class="inline-block px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full text-sm">
                                                                        {{ ucfirst(str_replace('_', ' ', $request->verification_details['document_type'])) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div class="pt-2 text-sm text-gray-500 dark:text-gray-400">
                                                                Requested on: {{ $request->created_at->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Right Column: Item Details -->
                                                <div class="space-y-4">
                                                    <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                        <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200 flex items-center">
                                                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                            </svg>
                                                            Item Details
                                                        </h6>
                                                        <div class="space-y-2 text-sm">
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Category:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ ucfirst($request->donationItem->category) }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Condition:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ ucfirst(str_replace('_', ' ', $request->donationItem->condition)) }}</span>
                                                            </p>
                                                            <div class="pt-2">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Description:</span>
                                                                <p class="text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700 p-2 rounded text-sm">
                                                                    {{ $request->donationItem->description }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <!-- Approved Requests -->
                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                            @if($sentRequests->where('status', 'approved')->count() > 0)
                                @foreach($sentRequests->where('status', 'approved') as $request)
                                    @if($request->donationItem)
                                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg shadow-lg p-6 mb-6">
                                            <div class="flex items-center justify-between mb-4">
                                                <h5 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                                                    <span class="border-b-2 border-blue-500">{{ $request->donationItem->item_name }}</span>
                                                </h5>
                                                <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                    Approved
                                                </span>
                                            </div>

                                            <div class="relative mb-6 group">
                                                <div class="overflow-hidden rounded-lg h-64 relative">
                                                    @php
                                                        $images = $request->donationItem->images ?? [];
                                                    @endphp
                                                    <div class="flex transition-transform duration-500 ease-in-out" id="imageSlider-{{ $request->id }}" style="width: {{ count($images) * 100 }}%">
                                                        @foreach($images as $image)
                                                            <div class="min-w-full h-64">
                                                                <img src="{{ asset('storage/' . $image) }}" alt="Donation Item" class="w-full h-full object-contain">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @if(count($images) > 1)
                                                    <button onclick="moveSlide('{{ $request->id }}', -1)" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-r transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                        </svg>
                                                    </button>
                                                    <button onclick="moveSlide('{{ $request->id }}', 1)" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-l transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <div class="space-y-4">
                                                    <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                        <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200 flex items-center">
                                                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                            </svg>
                                                            Donor Details
                                                        </h6>
                                                        <div class="space-y-2 text-sm">
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Name:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->donationItem->user->name }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Contact:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->donationItem->contact_number }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Contact Method:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ ucfirst($request->donationItem->preferred_contact_method) }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Email:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->donationItem->user->email }}</span>
                                                            </p>
                                                            <div class="py-1">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Available Times:</span>
                                                                <div class="flex flex-wrap gap-1">
                                                                    @if(!empty($request->donationItem->preferred_contact_times))
                                                                        @foreach($request->donationItem->preferred_contact_times as $time)
                                                                            <span class="inline-block px-2 py-1 text-xs rounded-full {{ 
                                                                                $time === 'morning' ? 'bg-yellow-100 text-yellow-800' : 
                                                                                ($time === 'afternoon' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') 
                                                                            }}">
                                                                                {{ ucfirst($time) }}
                                                                            </span>
                                                                        @endforeach
                                                                    @else
                                                                        <span class="text-gray-500">No preferred times specified</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="pt-2">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Pickup Address:</span>
                                                                <p class="text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700 p-2 rounded text-sm">
                                                                    {{ $request->donationItem->pickup_address ?? 'No pickup address specified' }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="space-y-4">
                                                    <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                        <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200 flex items-center">
                                                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                            </svg>
                                                            Item Details
                                                        </h6>
                                                        <div class="space-y-2 text-sm">
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Category:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ ucfirst($request->donationItem->category) }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Education Level:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ ucfirst(str_replace('_', ' ', $request->donationItem->education_level)) }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Condition:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ ucfirst($request->donationItem->condition) }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Quantity:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->donationItem->quantity }}</span>
                                                            </p>
                                                            <div class="pt-2">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Description:</span>
                                                                <p class="text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700 p-2 rounded text-sm">
                                                                    {{ $request->donationItem->description }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-6 flex justify-end">
                                                <a href="{{ route('donation.chat.show', $request) }}" 
                                                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-colors duration-200">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                    </svg>
                                                    Chat with Donor
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <!-- Rejected Requests -->
                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                            @if($sentRequests->where('status', 'rejected')->count() > 0)
                                @foreach($sentRequests->where('status', 'rejected') as $request)
                                    @if($request->donationItem)
                                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg shadow-lg p-6 mb-6">
                                            <div class="flex items-center justify-between mb-4">
                                                <h5 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                                                    <span class="border-b-2 border-blue-500">{{ $request->donationItem->item_name }}</span>
                                                </h5>
                                                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($request->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </div>

                                            @if($request->donationItem && $request->donationItem->images && count($request->donationItem->images) > 0)
                                            <div class="relative mb-6 group">
                                                <div class="overflow-hidden rounded-lg h-64 relative">
                                                    @php
                                                        $images = $request->donationItem->images ?? [];
                                                    @endphp
                                                    <div class="flex transition-transform duration-500 ease-in-out" id="imageSlider-{{ $request->id }}" style="width: {{ count($images) * 100 }}%">
                                                        @foreach($images as $image)
                                                        <div class="min-w-full h-64">
                                                            <img src="{{ Storage::url($image) }}" 
                                                                 alt="Item image" 
                                                                 class="w-full h-full object-contain bg-gray-100 dark:bg-gray-700"
                                                                 loading="lazy">
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    
                                                    @if(count($images) > 1)
                                                    <!-- Navigation Arrows -->
                                                    <button class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-r opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:bg-opacity-75"
                                                            onclick="moveSlide('{{ $request->id }}', -1)">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                        </svg>
                                                    </button>
                                                    <button class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-l opacity-0 group-hover:opacity-100 transition-opacity duration-300 hover:bg-opacity-75"
                                                            onclick="moveSlide('{{ $request->id }}', 1)">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>

                                                    <!-- Dots Indicator -->
                                                    <div class="absolute bottom-4 left-0 right-0">
                                                        <div class="flex items-center justify-center gap-2">
                                                            @foreach($request->donationItem->images as $index => $image)
                                                            <button class="w-2 h-2 rounded-full bg-black bg-opacity-50 hover:bg-opacity-75 transition-opacity duration-300 dot-indicator-{{ $request->id }} {{ $index === 0 ? 'bg-white' : '' }}"
                                                                    data-index="{{ $index }}"
                                                                    onclick="goToSlide('{{ $request->id }}', {{ $index }})"></button>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif

                                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                                                <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                    <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">
                                                        <svg class="inline w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        Requester Information
                                                    </h6>
                                                    <div class="space-y-2">
                                                        <p class="text-gray-600 dark:text-gray-300">
                                                            <span class="inline-block w-24 font-medium">Name:</span>
                                                            {{ $request->user->name }}
                                                        </p>
                                                        <p class="text-gray-600 dark:text-gray-300">
                                                            <span class="inline-block w-24 font-medium">Contact:</span>
                                                            {{ $request->user->phone }}
                                                        </p>
                                                        <p class="text-gray-600 dark:text-gray-300">
                                                            <span class="inline-block w-24 font-medium">Email:</span>
                                                            {{ $request->user->email }}
                                                        </p>
                                                        <p class="text-gray-600 dark:text-gray-300">
                                                            <span class="inline-block w-24 font-medium">Quantity:</span>
                                                            {{ $request->quantity }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                    <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">
                                                        <svg class="inline w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                        </svg>
                                                        Item Details
                                                    </h6>
                                                    <div class="divide-y dark:divide-gray-500">
                                                        <div class="py-2">
                                                            <span class="font-medium text-gray-600 dark:text-gray-300">Category:</span>
                                                            <span class="ml-2 text-gray-800 dark:text-gray-200">{{ $request->donationItem->category }}</span>
                                                        </div>
                                                        <div class="py-2">
                                                            <span class="font-medium text-gray-600 dark:text-gray-300">Condition:</span>
                                                            <span class="ml-2 text-gray-800 dark:text-gray-200">{{ ucfirst(str_replace('_', ' ', $request->donationItem->condition)) }}</span>
                                                        </div>
                                                        <div class="py-2">
                                                            <p class="font-medium text-gray-600 dark:text-gray-300 mb-1">Description:</p>
                                                            <p class="text-gray-800 dark:text-gray-200">{{ $request->donationItem->description }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                    <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">
                                                        <svg class="inline w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        Request Details
                                                    </h6>
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded p-3 mb-3">
                                                        <p class="font-medium text-gray-600 dark:text-gray-300 mb-2">Purpose:</p>
                                                        <p class="text-gray-800 dark:text-gray-200">{{ $request->purpose }}</p>
                                                    </div>
                                                    @if($request->verification_details)
                                                    <div class="mt-3 border-t dark:border-gray-500 pt-3">
                                                        <p class="font-medium text-gray-600 dark:text-gray-300">Verification Document:</p>
                                                        <span class="inline-block mt-1 px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-sm">
                                                            {{ ucfirst(str_replace('_', ' ', $request->verification_details['document_type'])) }}
                                                        </span>
                                                    </div>
                                                    @endif
                                                    <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                                        Requested on: {{ $request->created_at->format('M d, Y') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <!-- Received Requests (For Donors) -->
                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="received" role="tabpanel" aria-labelledby="received-tab">
                            @if($receivedRequests->count() > 0)
                                @foreach($receivedRequests as $request)
                                    @if($request->donationItem)
                                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-800 dark:to-gray-700 rounded-lg shadow-lg p-6 mb-6">
                                            <!-- Header -->
                                            <div class="flex items-center justify-between mb-4">
                                                <h5 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                                                    <span class="border-b-2 border-green-500">{{ $request->donationItem->item_name }}</span>
                                                </h5>
                                                <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </div>

                                            <!-- Image Carousel -->
                                            <div class="relative mb-6 group">
                                                <div class="overflow-hidden rounded-lg h-64 relative">
                                                    @php
                                                        $images = $request->donationItem->images ?? [];
                                                    @endphp
                                                    <div class="flex transition-transform duration-500 ease-in-out" id="imageSlider-{{ $request->id }}" style="width: {{ count($images) * 100 }}%">
                                                        @foreach($images as $image)
                                                            <div class="min-w-full h-64">
                                                                <img src="{{ asset('storage/' . $image) }}" alt="Donation Item" class="w-full h-full object-contain">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @if(count($images) > 1)
                                                    <button onclick="moveSlide('{{ $request->id }}', -1)" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-r transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                        </svg>
                                                    </button>
                                                    <button onclick="moveSlide('{{ $request->id }}', 1)" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white p-2 rounded-l transition-all duration-200 opacity-0 group-hover:opacity-100">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>

                                            <!-- Content Grid -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <!-- Left Column: Requester Information -->
                                                <div class="space-y-4">
                                                    <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                        <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200 flex items-center">
                                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                            </svg>
                                                            Requester Information
                                                        </h6>
                                                        <div class="space-y-2 text-sm">
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Name:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->user->name }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Contact:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->user->phone }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Email:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->user->email }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Quantity:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ $request->quantity }}</span>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Request Details -->
                                                    <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                        <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200 flex items-center">
                                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                            Request Details
                                                        </h6>
                                                        <div class="space-y-3">
                                                            <div>
                                                                <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Purpose:</span>
                                                                <p class="text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700 p-2 rounded text-sm">
                                                                    {{ $request->purpose }}
                                                                </p>
                                                            </div>
                                                            @if($request->verification_details)
                                                                <div class="pt-2">
                                                                    <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Verification:</span>
                                                                    <span class="inline-block px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full text-sm">
                                                                        {{ ucfirst(str_replace('_', ' ', $request->verification_details['document_type'])) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <div class="pt-2 text-sm text-gray-500 dark:text-gray-400">
                                                                Requested on: {{ $request->created_at->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Right Column: Item Details -->
                                                <div class="space-y-4">
                                                    <div class="bg-white dark:bg-gray-600 rounded-lg p-4 shadow-md">
                                                        <h6 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200 flex items-center">
                                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                            </svg>
                                                            Item Details
                                                        </h6>
                                                        <div class="space-y-2 text-sm">
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Category:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ ucfirst($request->donationItem->category) }}</span>
                                                            </p>
                                                            <p class="flex justify-between items-center py-1 border-b dark:border-gray-500">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300">Condition:</span>
                                                                <span class="text-gray-800 dark:text-gray-200">{{ ucfirst(str_replace('_', ' ', $request->donationItem->condition)) }}</span>
                                                            </p>
                                                            <div class="pt-2">
                                                                <span class="font-medium text-gray-600 dark:text-gray-300 block mb-1">Description:</span>
                                                                <p class="text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700 p-2 rounded text-sm">
                                                                    {{ $request->donationItem->description }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Chat Button -->
                                                    <div class="mt-4">
                                                        <a href="{{ route('donation.chat.show', $request) }}" 
                                                           class="inline-flex w-full items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition-colors duration-200">
                                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                            </svg>
                                                            Chat with Recipient
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        // Initialize tabs
        function setupTabs() {
            const tabs = document.querySelectorAll('[role="tab"]');
            const tabPanels = document.querySelectorAll('[role="tabpanel"]');

            // Set initial active tab
            const initialTab = document.querySelector('#pending-tab');
            if (initialTab) {
                initialTab.click();
            }

            // Add click event to each tab
            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active classes from all tabs
                    tabs.forEach(t => {
                        t.classList.remove('text-blue-600', 'border-blue-600', 'dark:text-blue-500', 'dark:border-blue-500');
                        t.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                        t.setAttribute('aria-selected', 'false');
                    });

                    // Hide all panels
                    tabPanels.forEach(panel => {
                        panel.classList.add('hidden');
                    });

                    // Add active classes to clicked tab
                    this.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                    this.classList.add('text-blue-600', 'border-blue-600', 'dark:text-blue-500', 'dark:border-blue-500');
                    this.setAttribute('aria-selected', 'true');

                    // Show corresponding panel
                    const panelId = this.getAttribute('aria-controls');
                    const panel = document.getElementById(panelId);
                    if (panel) {
                        panel.classList.remove('hidden');
                    }
                });
            });
        }

        // Image Slider functionality
        const sliders = {};
        const totalSlides = {};

        function initializeSlider(requestId) {
            const slider = document.querySelector(`#imageSlider-${requestId}`);
            if (slider) {
                sliders[requestId] = 0; // Current slide index
                totalSlides[requestId] = slider.children.length;
                updateSliderView(requestId);
            }
        }

        function moveSlide(requestId, direction) {
            const newIndex = sliders[requestId] + direction;
            if (newIndex >= 0 && newIndex < totalSlides[requestId]) {
                sliders[requestId] = newIndex;
                updateSliderView(requestId);
            }
        }

        function goToSlide(requestId, index) {
            if (index >= 0 && index < totalSlides[requestId]) {
                sliders[requestId] = index;
                updateSliderView(requestId);
            }
        }

        function updateSliderView(requestId) {
            const slider = document.querySelector(`#imageSlider-${requestId}`);
            const dots = document.querySelectorAll(`.dot-indicator-${requestId}`);
            
            if (slider) {
                slider.style.transform = `translateX(-${sliders[requestId] * 100}%)`;
                
                // Update dots
                dots.forEach((dot, index) => {
                    if (index === sliders[requestId]) {
                        dot.classList.add('bg-opacity-100');
                        dot.classList.add('scale-125');
                    } else {
                        dot.classList.remove('bg-opacity-100');
                        dot.classList.remove('scale-125');
                    }
                });
            }
        }

        // Initialize everything when the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            setupTabs();
            
            // Initialize all sliders
            document.querySelectorAll('[id^="imageSlider-"]').forEach(slider => {
                const requestId = slider.id.split('-')[1];
                initializeSlider(requestId);
            });
        });
    </script>
@endpush
</x-app-layout>
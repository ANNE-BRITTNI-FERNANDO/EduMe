<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Donation Requests Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button onclick="showTab('pending')" 
                            id="pending-tab"
                            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Pending ({{ isset($pendingRequests) ? $pendingRequests->count() : 0 }})
                    </button>
                    <button onclick="showTab('approved')" 
                            id="approved-tab"
                            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Approved ({{ isset($approvedRequests) ? $approvedRequests->count() : 0 }})
                    </button>
                    <button onclick="showTab('rejected')" 
                            id="rejected-tab"
                            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Rejected ({{ isset($rejectedRequests) ? $rejectedRequests->count() : 0 }})
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                <!-- Pending Requests -->
                <div id="pending-content" class="tab-content">
                    @if(!isset($pendingRequests) || $pendingRequests->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500">No pending requests found.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($pendingRequests as $request)
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                    <!-- Request Header -->
                                    <div class="p-6">
                                        <div class="flex items-center justify-between">
                                            <div class="min-w-0 flex-1">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    @if($request->donationItem)
                                                        {{ $request->donationItem->item_name ?? 'Item Not Found' }}
                                                        <span class="text-sm text-gray-500">({{ $request->donationItem->category ?? 'No Category' }})</span>
                                                    @else
                                                        <span class="text-red-500">Item no longer available</span>
                                                    @endif
                                                </h3>
                                                <div class="mt-1 text-sm text-gray-500">
                                                    Requested by <span class="font-medium text-gray-900">{{ $request->user->name }}</span>
                                                    on {{ $request->created_at->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <button onclick="toggleCollapse({{ $request->id }})" 
                                                        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                                                    <span class="mr-2" id="button-text-{{ $request->id }}">View Details</span>
                                                    <svg id="icon-{{ $request->id }}" 
                                                         class="w-5 h-5 transform transition-transform duration-200" 
                                                         fill="none" 
                                                         stroke="currentColor" 
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" 
                                                              stroke-linejoin="round" 
                                                              stroke-width="2" 
                                                              d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                <div class="flex space-x-2">
                                                    <form action="{{ route('admin.donations.requests.approve', $request) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('admin.donations.requests.reject', $request) }}" method="POST" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="reason" value="">
                                                        <button type="button"
                                                                onclick="showRejectModal(this.form)"
                                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Collapsible Content -->
                                    <div id="content-{{ $request->id }}" class="hidden border-t border-gray-200">
                                        <div class="p-6">
                                            <div class="grid grid-cols-3 gap-6">
                                                <!-- Item Details -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Item Details</h4>
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    @if($request->donationItem)
                                                                        {{ $request->donationItem->item_name ?? 'Item Not Found' }}
                                                                        <span class="text-sm text-gray-500">({{ $request->donationItem->category ?? 'No Category' }})</span>
                                                                    @else
                                                                        <span class="text-red-500">Item no longer available</span>
                                                                    @endif
                                                                </dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->quantity }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Condition</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->condition ?? 'N/A' }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Category</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->category ?? 'N/A' }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->description ?? 'N/A' }}</dd>
                                                            </div>
                                                        </dl>
                                                        @if($request->donationItem && $request->donationItem->images)
                                                            <div class="mt-4">
                                                                <dt class="text-sm font-medium text-gray-500">Images</dt>
                                                                <dd class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                                                    @php
                                                                        $images = is_string($request->donationItem->images) 
                                                                            ? json_decode($request->donationItem->images) 
                                                                            : (is_array($request->donationItem->images) ? $request->donationItem->images : []);
                                                                    @endphp
                                                                    @foreach($images as $image)
                                                                        <div class="relative">
                                                                            <img src="{{ asset('storage/' . $image) }}" alt="Donation Item Image" class="h-24 w-24 object-cover rounded-lg">
                                                                        </div>
                                                                    @endforeach
                                                                </dd>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Request & Contact Info -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Request Information</h4>
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->quantity }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($request->purpose) }}</dd>
                                                            </div>
                                                        </dl>
                                                    </div>
                                                </div>

                                                <!-- Verification Details -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Verification Details</h4>
                                                        @php
                                                            $verificationDetails = is_string($request->purpose_details) 
                                                                ? json_decode($request->purpose_details, true)
                                                                : $request->purpose_details;
                                                        @endphp
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Document Type</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ ucwords(str_replace('_', ' ', $verificationDetails['document_type'] ?? 'N/A')) }}
                                                                </dd>
                                                            </div>
                                                        </dl>
                                                        <div class="mt-6">
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                <div>

                                                                <div>
                                                                    <dt class="text-sm font-medium text-gray-500 mb-2">Verification Document</dt>
                                                                    @if(isset($verificationDetails['document_path']))
                                                                        <img src="{{ asset('storage/' . $verificationDetails['document_path']) }}" 
                                                                             alt="Verification Document" 
                                                                             class="rounded-lg w-full h-48 object-cover cursor-pointer hover:opacity-75 transition-opacity"
                                                                             onclick="window.open(this.src, '_blank')">
                                                                    @else
                                                                        <p class="text-sm text-gray-500">No verification document available</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Approved Requests -->
                <div id="approved-content" class="tab-content hidden">
                    @if(!isset($approvedRequests) || $approvedRequests->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500">No approved requests found.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($approvedRequests as $request)
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                    <!-- Request Header -->
                                    <div class="p-6">
                                        <div class="flex items-center justify-between">
                                            <div class="min-w-0 flex-1">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    @if($request->donationItem)
                                                        {{ $request->donationItem->item_name ?? 'Item Not Found' }}
                                                        <span class="text-sm text-gray-500">({{ $request->donationItem->category ?? 'No Category' }})</span>
                                                    @else
                                                        <span class="text-red-500">Item no longer available</span>
                                                    @endif
                                                </h3>
                                                <div class="mt-1 text-sm text-gray-500">
                                                    Requested by <span class="font-medium text-gray-900">{{ $request->user->name }}</span>
                                                    on {{ $request->created_at->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <button onclick="toggleCollapse({{ $request->id }})" 
                                                        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                                                    <span class="mr-2" id="button-text-{{ $request->id }}">View Details</span>
                                                    <svg id="icon-{{ $request->id }}" 
                                                         class="w-5 h-5 transform transition-transform duration-200" 
                                                         fill="none" 
                                                         stroke="currentColor" 
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" 
                                                              stroke-linejoin="round" 
                                                              stroke-width="2" 
                                                              d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Collapsible Content -->
                                    <div id="content-{{ $request->id }}" class="hidden border-t border-gray-200">
                                        <div class="p-6">
                                            <div class="grid grid-cols-3 gap-6">
                                                <!-- Item Details -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Item Details</h4>
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    @if($request->donationItem)
                                                                        {{ $request->donationItem->item_name ?? 'Item Not Found' }}
                                                                        <span class="text-sm text-gray-500">({{ $request->donationItem->category ?? 'No Category' }})</span>
                                                                    @else
                                                                        <span class="text-red-500">Item no longer available</span>
                                                                    @endif
                                                                </dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Category</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ $request->donationItem->category ?? 'N/A' }}
                                                                </dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Condition</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ $request->donationItem->condition ?? 'N/A' }}
                                                                </dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ $request->donationItem->description ?? 'N/A' }}
                                                                </dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Quantity Requested</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ $request->quantity }}
                                                                </dd>
                                                            </div>
                                                            @if($request->donationItem)
                                                                <div>
                                                                    <dt class="text-sm font-medium text-gray-500">Available Quantity</dt>
                                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                        {{ $request->donationItem->available_quantity ?? 'N/A' }}
                                                                    </dd>
                                                                </div>
                                                            @endif
                                                        </dl>
                                                        @if($request->donationItem && $request->donationItem->images)
                                                            <div class="mt-4">
                                                                <dt class="text-sm font-medium text-gray-500">Images</dt>
                                                                <dd class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                                                    @php
                                                                        $images = is_string($request->donationItem->images) 
                                                                            ? json_decode($request->donationItem->images) 
                                                                            : (is_array($request->donationItem->images) ? $request->donationItem->images : []);
                                                                    @endphp
                                                                    @foreach($images as $image)
                                                                        <div class="relative">
                                                                            <img src="{{ asset('storage/' . $image) }}" alt="Donation Item Image" class="h-24 w-24 object-cover rounded-lg">
                                                                        </div>
                                                                    @endforeach
                                                                </dd>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Request & Contact Info -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Request Information</h4>
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->quantity }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($request->purpose) }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                                                <dd class="mt-1 text-sm font-medium text-green-600">Approved</dd>
                                                            </div>
                                                        </dl>
                                                    </div>
                                                </div>

                                                <!-- Verification Details -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Verification Details</h4>
                                                        @php
                                                            $verificationDetails = is_string($request->purpose_details) 
                                                                ? json_decode($request->purpose_details, true)
                                                                : $request->purpose_details;
                                                        @endphp
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Document Type</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ ucwords(str_replace('_', ' ', $verificationDetails['document_type'] ?? 'N/A')) }}
                                                                </dd>
                                                            </div>
                                                        </dl>
                                                        <div class="mt-6">
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                <div>
                                                                    <dt class="text-sm font-medium text-gray-500 mb-2">Product Images</dt>
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        @php
                                                                            $images = $request->donationItem && $request->donationItem->images 
                                                                                ? (is_string($request->donationItem->images) 
                                                                                    ? json_decode($request->donationItem->images) 
                                                                                    : (is_array($request->donationItem->images) ? $request->donationItem->images : []))
                                                                                : [];
                                                                        @endphp
                                                                        @if(count($images) > 0)
                                                                            @foreach($images as $image)
                                                                                <div class="relative aspect-[4/3]">
                                                                                    <img src="{{ asset('storage/' . $image) }}" 
                                                                                        alt="Product image" 
                                                                                        class="absolute inset-0 h-full w-full object-cover rounded-lg">
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <p class="text-sm text-gray-500">No product images available</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <dt class="text-sm font-medium text-gray-500 mb-2">Verification Document</dt>
                                                                    @if(isset($verificationDetails['document_path']))
                                                                        <img src="{{ asset('storage/' . $verificationDetails['document_path']) }}" 
                                                                             alt="Verification Document" 
                                                                             class="rounded-lg w-full h-48 object-cover cursor-pointer hover:opacity-75 transition-opacity"
                                                                             onclick="window.open(this.src, '_blank')">
                                                                    @else
                                                                        <p class="text-sm text-gray-500">No verification document available</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Rejected Requests -->
                <div id="rejected-content" class="tab-content hidden">
                    @if(!isset($rejectedRequests) || $rejectedRequests->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500">No rejected requests found.</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($rejectedRequests as $request)
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                    <!-- Request Header -->
                                    <div class="p-6">
                                        <div class="flex items-center justify-between">
                                            <div class="min-w-0 flex-1">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    Request from {{ $request->user->name }}
                                                </h3>
                                                <div class="mt-1">
                                                    <p class="text-sm text-gray-500">
                                                        Requested on {{ $request->created_at->format('M d, Y') }}
                                                    </p>
                                                </div>
                                                @if($request->rejection_reason)
                                                    <div class="mt-2 bg-red-50 border border-red-100 rounded-md p-3">
                                                        <p class="text-sm font-medium text-gray-900">Rejection Reason:</p>
                                                        <p class="text-sm text-red-600">{{ $request->rejection_reason }}</p>
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Rejected by {{ $request->rejectedBy ? $request->rejectedBy->name : 'Unknown' }} 
                                                            on {{ $request->rejected_at ? $request->rejected_at->format('M d, Y') : 'Unknown date' }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex items-center">
                                                <button onclick="toggleCollapse({{ $request->id }})" 
                                                        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                                                    <span class="mr-2" id="button-text-{{ $request->id }}">View Details</span>
                                                    <svg id="icon-{{ $request->id }}" 
                                                         class="w-5 h-5 transform transition-transform duration-200" 
                                                         fill="none" 
                                                         stroke="currentColor" 
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" 
                                                              stroke-linejoin="round" 
                                                              stroke-width="2" 
                                                              d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Collapsible Content -->
                                    <div id="content-{{ $request->id }}" class="hidden border-t border-gray-200">
                                        <div class="p-6">
                                            <div class="grid grid-cols-3 gap-6">
                                                <!-- Item Details -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Item Details</h4>
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->item_name ?? 'N/A' }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Category</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->category ?? 'N/A' }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Condition</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->condition ?? 'N/A' }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Description</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->description ?? 'N/A' }}</dd>
                                                            </div>
                                                        </dl>
                                                        @if($request->donationItem && $request->donationItem->images)
                                                            <div class="mt-4">
                                                                <dt class="text-sm font-medium text-gray-500">Images</dt>
                                                                <dd class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                                                    @php
                                                                        $images = is_string($request->donationItem->images) 
                                                                            ? json_decode($request->donationItem->images) 
                                                                            : (is_array($request->donationItem->images) ? $request->donationItem->images : []);
                                                                    @endphp
                                                                    @foreach($images as $image)
                                                                        <div class="relative">
                                                                            <img src="{{ asset('storage/' . $image) }}" alt="Donation Item Image" class="h-24 w-24 object-cover rounded-lg">
                                                                        </div>
                                                                    @endforeach
                                                                </dd>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Request & Contact Info -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Request Information</h4>
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->quantity }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($request->purpose) }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                                                <dd class="mt-1 text-sm font-medium text-red-600">Rejected</dd>
                                                            </div>
                                                            @if($request->rejection_reason)
                                                                <div>
                                                                    <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->rejection_reason }}</dd>
                                                                </div>
                                                            @endif
                                                        </dl>
                                                    </div>
                                                </div>

                                                <!-- Verification Details -->
                                                <div class="col-span-1">
                                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Verification Details</h4>
                                                        @php
                                                            $verificationDetails = is_string($request->purpose_details) 
                                                                ? json_decode($request->purpose_details, true)
                                                                : $request->purpose_details;
                                                        @endphp
                                                        <dl class="space-y-2">
                                                            <div>
                                                                <dt class="text-sm font-medium text-gray-500">Document Type</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                    {{ ucwords(str_replace('_', ' ', $verificationDetails['document_type'] ?? 'N/A')) }}
                                                                </dd>
                                                            </div>
                                                        </dl>
                                                        <div class="mt-6">
                                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                <div>
                                                                    <dt class="text-sm font-medium text-gray-500 mb-2">Product Images</dt>
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        @php
                                                                            $images = $request->donationItem && $request->donationItem->images 
                                                                                ? (is_string($request->donationItem->images) 
                                                                                    ? json_decode($request->donationItem->images) 
                                                                                    : (is_array($request->donationItem->images) ? $request->donationItem->images : []))
                                                                                : [];
                                                                        @endphp
                                                                        @if(count($images) > 0)
                                                                            @foreach($images as $image)
                                                                                <div class="relative aspect-[4/3]">
                                                                                    <img src="{{ asset('storage/' . $image) }}" 
                                                                                        alt="Product image" 
                                                                                        class="absolute inset-0 h-full w-full object-cover rounded-lg">
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            <p class="text-sm text-gray-500">No product images available</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <dt class="text-sm font-medium text-gray-500 mb-2">Verification Document</dt>
                                                                    @if(isset($verificationDetails['document_path']))
                                                                        <img src="{{ asset('storage/' . $verificationDetails['document_path']) }}" 
                                                                             alt="Verification Document" 
                                                                             class="rounded-lg w-full h-48 object-cover cursor-pointer hover:opacity-75 transition-opacity"
                                                                             onclick="window.open(this.src, '_blank')">
                                                                    @else
                                                                        <p class="text-sm text-gray-500">No verification document available</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 text-center">Reject Request</h3>
                <div class="mt-4 px-4">
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for rejection</label>
                        <textarea id="rejection_reason" 
                                 name="rejection_reason" 
                                 rows="3" 
                                 class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                 required
                                 placeholder="Please provide a reason for rejecting this request"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 mt-5">
                        <button type="button" 
                                onclick="hideRejectModal()"
                                class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button type="button" 
                                onclick="submitRejectForm()"
                                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Show the pending tab by default when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            showTab('pending');
        });

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Update tab styles
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('border-indigo-500', 'text-indigo-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-indigo-500', 'text-indigo-600');
        }

        function toggleCollapse(id) {
            const content = document.getElementById('content-' + id);
            const icon = document.getElementById('icon-' + id);
            const buttonText = document.getElementById('button-text-' + id);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
                buttonText.textContent = 'Hide Details';
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
                buttonText.textContent = 'View Details';
            }
        }

        let activeRejectForm = null;

        function showRejectModal(form) {
            activeRejectForm = form;
            document.getElementById('reject-modal').classList.remove('hidden');
            document.getElementById('rejection_reason').value = ''; // Clear previous value
            document.getElementById('rejection_reason').focus();
        }

        function hideRejectModal() {
            document.getElementById('reject-modal').classList.add('hidden');
            activeRejectForm = null;
        }

        function submitRejectForm() {
            if (!activeRejectForm) {
                console.error('No active form found');
                return;
            }
            
            const reason = document.getElementById('rejection_reason').value.trim();
            if (!reason) {
                alert('Please provide a reason for rejection');
                return;
            }

            console.log('Submitting rejection with reason:', reason);

            // Set the reason in the hidden input
            const reasonInput = activeRejectForm.querySelector('input[name="reason"]');
            reasonInput.value = reason;
            
            // Submit the form
            activeRejectForm.submit();
            hideRejectModal();
        }

        // Close modal when clicking outside
        document.getElementById('reject-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideRejectModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('reject-modal').classList.contains('hidden')) {
                hideRejectModal();
            }
        });
    </script>
    @endpush

    @push('styles')
    <style>
        .rotate-180 {
            transform: rotate(180deg);
        }
    </style>
    @endpush
</x-app-layout>

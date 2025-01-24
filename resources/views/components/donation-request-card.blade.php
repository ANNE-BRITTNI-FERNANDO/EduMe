@props(['request'])

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
                @if($request->status === 'rejected' && $request->rejection_reason)
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
                @if($request->status === 'pending')
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
                @endif
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
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 text-sm font-medium {{ $request->status === 'approved' ? 'text-green-600' : ($request->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                                    {{ ucfirst($request->status) }}
                                </dd>
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

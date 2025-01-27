<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manage Donations
            </h2>
            <a href="{{ route('admin.donations.requests') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-inbox mr-2"></i>
                View Donation Requests
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(auth()->user()->is_admin)
                <div class="mb-4 p-4 bg-gray-100 dark:bg-gray-700 rounded">
                    <h4 class="font-semibold mb-2">Debug Information:</h4>
                    <p>Total Donations in DB: {{ $debug['total_donations'] ?? 'N/A' }}</p>
                    <p>Status Counts:</p>
                    <ul class="ml-4">
                        @foreach($debug['status_counts'] ?? [] as $status => $count)
                            <li>{{ $status }}: {{ $count }}</li>
                        @endforeach
                    </ul>
                    <p>Pending Count: {{ $debug['pending_count'] ?? 'N/A' }}</p>
                    <p>Total Approved (before quantity filter): {{ $debug['total_approved'] ?? 'N/A' }}</p>
                    <p>Approved with quantity > 0: {{ $debug['approved_with_quantity'] ?? 'N/A' }}</p>
                    <p>Final Approved Count: {{ $debug['final_approved_count'] ?? 'N/A' }}</p>
                    <p>Rejected Count: {{ $debug['rejected_count'] ?? 'N/A' }}</p>
                    @if(isset($debug['error']))
                        <p class="text-red-500">Error: {{ $debug['error'] }}</p>
                    @endif
                </div>
            @endif
            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                    <li class="mr-2">
                        <a href="{{ route('admin.donations.index', ['tab' => 'pending']) }}" 
                           class="tab-link inline-block p-4 rounded-t-lg {{ request('tab') === 'pending' ? 'text-blue-600 border-b-2 border-blue-600 active' : 'hover:text-gray-600 hover:border-gray-300' }}"
                           data-tab="pending">
                            Pending Donations
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ route('admin.donations.index', ['tab' => 'approved']) }}" 
                           class="tab-link inline-block p-4 rounded-t-lg {{ request('tab') === 'approved' ? 'text-blue-600 border-b-2 border-blue-600 active' : 'hover:text-gray-600 hover:border-gray-300' }}"
                           data-tab="approved">
                            Approved Donations
                        </a>
                    </li>
                    <li class="mr-2">
                        <a href="{{ route('admin.donations.index', ['tab' => 'rejected']) }}" 
                           class="tab-link inline-block p-4 rounded-t-lg {{ request('tab') === 'rejected' ? 'text-blue-600 border-b-2 border-blue-600 active' : 'hover:text-gray-600 hover:border-gray-300' }}"
                           data-tab="rejected">
                            Rejected Donations
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Pending Donations Tab -->
            <div id="pending-tab" class="{{ request('tab') === 'pending' ? 'block' : 'hidden' }}">
                @if(isset($error) && $error)
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <p class="font-bold">Error:</p>
                        <p>{{ $error }}</p>
                    </div>
                @endif

                <!-- Debug Info (if needed) -->
                @if(request('tab') === 'pending' && isset($debug))
                    <div class="bg-gray-100 p-4 mb-4">
                        <p class="text-sm text-gray-600">Found {{ $pendingDonations->count() }} pending donations</p>
                    </div>
                @endif

                <!-- Main Content -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        @forelse($pendingDonations as $donation)
                            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-4">
                                <div class="px-4 py-5 sm:p-6">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-grow">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                                {{ $donation->item_name }}
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                    Pending
                                                </span>
                                            </h3>
                                            <div class="mt-2 grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                                                <div class="text-sm text-gray-500 dark:text-gray-400">Category: {{ ucfirst($donation->category) }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">Condition: {{ ucfirst($donation->condition) }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">Available: {{ $donation->available_quantity }} of {{ $donation->quantity }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">Location: {{ $donation->user->location ?? $donation->user->province ?? 'Not specified' }}</div>
                                            </div>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $donation->description }}</p>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <button type="button" onclick="toggleDetails('donation-{{ $donation->id }}')" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Collapsible Details -->
                                    <div id="donation-{{ $donation->id }}" class="hidden mt-4">
                                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                            <div class="grid grid-cols-1 gap-4">
                                                <!-- Additional Details -->
                                                <div>
                                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">Description</h4>
                                                    <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $donation->description }}</p>
                                                </div>

                                                <!-- Donor Information -->
                                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                    <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Donor Details</h4>
                                                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                                        <div class="sm:col-span-1">
                                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Donor Name</dt>
                                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                {{ $donation->is_anonymous ? 'Anonymous' : $donation->user->name }}
                                                                @if($donation->is_anonymous)
                                                                    <span class="ml-2 text-xs text-gray-500">(Anonymous donation)</span>
                                                                @endif
                                                            </dd>
                                                        </div>

                                                        @if(!$donation->is_anonymous)
                                                            <div class="sm:col-span-1">
                                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->user->email }}</dd>
                                                            </div>
                                                        @endif

                                                        <div class="sm:col-span-1">
                                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Number</dt>
                                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                {{ $donation->contact_number ?? 'Not provided' }}
                                                            </dd>
                                                        </div>

                                                        <div class="sm:col-span-1">
                                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Preference</dt>
                                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                {{ ucfirst($donation->preferred_contact_method ?? 'Not specified') }}
                                                            </dd>
                                                        </div>

                                                        <div class="sm:col-span-2">
                                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Preferred Contact Times</dt>
                                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                @if($donation->preferred_contact_times && is_array($donation->preferred_contact_times))
                                                                    {{ implode(', ', array_map('ucfirst', $donation->preferred_contact_times)) }}
                                                                @else
                                                                    Not specified
                                                                @endif
                                                            </dd>
                                                        </div>

                                                        <div class="sm:col-span-2">
                                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pickup Address</dt>
                                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                                {{ $donation->pickup_address ?? $donation->user->address ?? 'Not specified' }}
                                                            </dd>
                                                        </div>

                                                        <div class="sm:col-span-2">

                                                        </div>

                                                        @if($donation->notes)
                                                            <div class="sm:col-span-2">
                                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Additional Notes</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->notes }}</dd>
                                                            </div>
                                                        @endif
                                                    </dl>
                                                </div>

                                                <!-- Images -->
                                                @if($donation->images && count($donation->images) > 0)
                                                    <div>
                                                        <h4 class="font-medium text-gray-900 mb-2">Images</h4>
                                                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                                            @foreach($donation->images as $image)
                                                                <div class="relative group">
                                                                    <img src="{{ Storage::url($image) }}" 
                                                                         alt="Donation image" 
                                                                         class="w-full h-32 object-cover rounded-lg shadow-sm hover:opacity-75 transition-opacity cursor-pointer"
                                                                         onclick="window.open('{{ Storage::url($image) }}', '_blank')"
                                                                    >
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="mt-4 flex justify-end space-x-3">
                                                <button type="button" 
                                                        onclick="handleDonationAction('{{ $donation->id }}', 'approve')"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    Approve
                                                </button>
                                                <button type="button"
                                                        onclick="showRejectionModal('{{ $donation->id }}')"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Reject
                                                </button>
                                                <button type="button"
                                                        onclick="handleDonationAction('{{ $donation->id }}', 'delete')"
                                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No pending donations found.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            @if(request('tab') === 'approved')
                <div class="bg-gray-100 p-4 mb-4">
                    <h3 class="font-bold mb-2">Debug Information:</h3>
                    
                    @if(isset($error))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <p class="font-bold">Error:</p>
                            <p>{{ $error }}</p>
                        </div>
                    @endif

                    <!-- <div class="space-y-2">
                        <p><strong>Collection Info:</strong></p>
                        <ul class="list-disc pl-5">
                            <li>Current Tab: {{ request('tab', 'pending') }}</li>
                            <li>Total Approved in Database: {{ $debug['totalApproved'] ?? 0 }}</li>
                            <li>Approved Items Count: {{ isset($approvedDonations) ? $approvedDonations->count() : 0 }}</li>
                        </ul>

                        <p><strong>Items on Current Page:</strong></p>
                        @if(isset($pendingDonations) && $pendingDonations->isNotEmpty())
                            <ul class="list-disc pl-5">
                                @foreach($pendingDonations as $donation)
                                    <li>
                                        ID: {{ $donation->id }} - 
                                        Name: {{ $donation->item_name ?? 'N/A' }} - 
                                        Status: {{ $donation->status ?? 'N/A' }} - 
                                        Available: {{ $donation->available_quantity ?? 'N/A' }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">No pending donations found.</p>
                        @endif
                    </div> -->
                </div>
            @endif

            <!-- Approved Donations Tab -->
            <div id="approved-tab" class="{{ request('tab') === 'approved' ? 'block' : 'hidden' }}">
                @forelse($approvedDonations as $donation)
                    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-4">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-grow">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                        {{ $donation->item_name }}
                                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Approved
                                        </span>
                                    </h3>
                                    <div class="mt-2 grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Category: {{ ucfirst($donation->category) }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Condition: {{ ucfirst($donation->condition) }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Available: {{ $donation->available_quantity }} of {{ $donation->quantity }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Location: {{ $donation->user->location ?? $donation->user->province ?? 'Not specified' }}</div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $donation->description }}</p>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <button type="button" onclick="toggleDetails('approved-{{ $donation->id }}')" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                        View Details
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Collapsible Details -->
                            <div id="approved-{{ $donation->id }}" class="hidden mt-4">
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <div class="grid grid-cols-1 gap-4">
                                        <!-- Additional Details -->
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Description</h4>
                                            <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $donation->description }}</p>
                                        </div>

                                        <!-- Donor Information -->
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Donor Details</h4>
                                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                                <div class="sm:col-span-1">
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Donor Name</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $donation->is_anonymous ? 'Anonymous' : $donation->user->name }}
                                                        @if($donation->is_anonymous)
                                                            <span class="ml-2 text-xs text-gray-500">(Anonymous donation)</span>
                                                        @endif
                                                    </dd>
                                                </div>

                                                @if(!$donation->is_anonymous)
                                                    <div class="sm:col-span-1">
                                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->user->email }}</dd>
                                                    </div>
                                                @endif

                                                <div class="sm:col-span-1">
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Number</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $donation->contact_number ?? 'Not provided' }}
                                                    </dd>
                                                </div>

                                                <div class="sm:col-span-1">
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Preference</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                        {{ ucfirst($donation->preferred_contact_method ?? 'Not specified') }}
                                                    </dd>
                                                </div>

                                                <div class="sm:col-span-2">
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Preferred Contact Times</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                        @if($donation->preferred_contact_times && is_array($donation->preferred_contact_times))
                                                            {{ implode(', ', array_map('ucfirst', $donation->preferred_contact_times)) }}
                                                        @else
                                                            Not specified
                                                        @endif
                                                    </dd>
                                                </div>

                                                <div class="sm:col-span-2">
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pickup Address</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $donation->pickup_address ?? $donation->user->address ?? 'Not specified' }}
                                                    </dd>
                                                </div>

                                                <div class="sm:col-span-2">

                                                </div>

                                                @if($donation->notes)
                                                    <div class="sm:col-span-2">
                                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Additional Notes</dt>
                                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->notes }}</dd>
                                                    </div>
                                                @endif
                                            </dl>
                                        </div>

                                        <!-- Images -->
                                        @if($donation->images && count($donation->images) > 0)
                                            <div>
                                                <h4 class="font-medium text-gray-900 mb-2">Images</h4>
                                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                                    @foreach($donation->images as $image)
                                                        <div class="relative group">
                                                            <img src="{{ Storage::url($image) }}" 
                                                                 alt="Donation image" 
                                                                 class="w-full h-32 object-cover rounded-lg shadow-sm hover:opacity-75 transition-opacity cursor-pointer"
                                                                 onclick="window.open('{{ Storage::url($image) }}', '_blank')"
                                                            >
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="mt-4 flex justify-end space-x-3">
                                        <button type="button"
                                                onclick="handleDonationAction('{{ $donation->id }}', 'remove')"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            Remove from Available
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">No approved donations found.</p>
                @endforelse
            </div>

            <!-- Rejected Donations Tab -->
            <div id="rejected-tab" class="{{ request('tab') === 'rejected' ? 'block' : 'hidden' }}">
                @forelse($rejectedDonations as $donation)
                    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-4">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-grow">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                        {{ $donation->item_name }}
                                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Rejected
                                        </span>
                                    </h3>
                                    
                                    <!-- Rejection Details -->
                                    <div class="mt-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                                    Rejection Reason
                                                </h3>
                                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                                    <p>{{ $donation->rejection_reason ?: 'No reason provided' }}</p>
                                                </div>
                                                <div class="mt-1 text-sm text-red-600 dark:text-red-400">
                                                    Rejected on: {{ $donation->updated_at->format('F j, Y, g:i a') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-2 grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Category: {{ ucfirst($donation->category) }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Condition: {{ ucfirst($donation->condition) }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Quantity: {{ $donation->quantity }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Location: {{ $donation->user->location ?? $donation->user->province ?? 'Not specified' }}</div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <button type="button" onclick="toggleDetails('rejected-{{ $donation->id }}')" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                        View Details
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Collapsible Details -->
                            <div id="rejected-{{ $donation->id }}" class="hidden mt-4">
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                    <div class="grid grid-cols-1 gap-4">
                                        <!-- Description -->
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">Description</h4>
                                            <p class="mt-1 text-gray-500 dark:text-gray-400">{{ $donation->description }}</p>
                                        </div>

                                        <!-- Donor Information -->
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Donor Details</h4>
                                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                                <div class="sm:col-span-1">
                                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Donor Name</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $donation->is_anonymous ? 'Anonymous' : $donation->user->name }}
                                                        @if($donation->is_anonymous)
                                                            <span class="ml-2 text-xs text-gray-500">(Anonymous donation)</span>
                                                        @endif
                                                    </dd>
                                                </div>
                                                @if(!$donation->is_anonymous)
                                                    <div class="sm:col-span-1">
                                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->user->email }}</dd>
                                                    </div>
                                                    <div class="sm:col-span-1">
                                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->user->phone ?? 'Not provided' }}</dd>
                                                    </div>
                                                @endif
                                            </dl>
                                        </div>

                                        <!-- Images -->
                                        @if($donation->images && count($donation->images) > 0)
                                            <div class="mt-4">
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Images</h4>
                                                <div class="grid grid-cols-2 gap-4">
                                                    @foreach($donation->images as $image)
                                                        <div class="relative aspect-w-16 aspect-h-9 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                                                            <img src="{{ Storage::url($image) }}" 
                                                                 alt="Donation item image" 
                                                                 class="object-cover">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">No rejected donations found.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejection-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Reject Donation</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Rejection Reason
                    </label>
                    <select id="rejection-reason" 
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-700 text-sm"
                        required>
                        <option value="">Select Reason</option>
                        <option value="Item condition does not match description">Item condition does not match description</option>
                        <option value="Item quantity does not match">Item quantity does not match</option>
                        <option value="Item is damaged or unusable">Item is damaged or unusable</option>
                        <option value="Item specifications do not meet requirements">Item specifications do not meet requirements</option>
                        <option value="Item is not suitable for educational use">Item is not suitable for educational use</option>
                        <option value="Item is missing essential components">Item is missing essential components</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button"
                        onclick="hideRejectionModal()"
                        class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                    <button type="button"
                        onclick="confirmRejection()"
                        class="bg-red-600 text-white hover:bg-red-700 px-4 py-2 rounded-lg">
                        Confirm Rejection
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let currentDonationId = null;

        function toggleDetails(id) {
            const element = document.getElementById(id);
            if (element) {
                element.classList.toggle('hidden');
            }
        }

        function showRejectionModal(donationId) {
            currentDonationId = donationId;
            document.getElementById('rejection-modal').classList.remove('hidden');
            document.getElementById('rejection-reason').value = '';
        }

        function hideRejectionModal() {
            document.getElementById('rejection-modal').classList.add('hidden');
            currentDonationId = null;
        }

        function confirmRejection() {
            const reason = document.getElementById('rejection-reason').value;
            if (!reason) {
                alert('Please select a rejection reason');
                return;
            }
            handleDonationAction(currentDonationId, 'reject', reason);
            hideRejectionModal();
        }

        // Handle donation actions
        async function handleDonationAction(donationId, action, rejectionReason = null) {
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                if (rejectionReason) {
                    formData.append('rejection_reason', rejectionReason);
                }

                const response = await fetch(`/admin/donations/${donationId}/${action}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    alert(data.message);
                    // Reload the page to show updated status
                    window.location.reload();
                } else {
                    // Show error message
                    alert(data.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing your request');
            }
        }
    </script>
    @endpush
</x-app-layout>
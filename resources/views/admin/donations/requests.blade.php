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
                    <a href="#" 
                            id="pending-tab"
                            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Pending ({{ isset($pendingRequests) ? $pendingRequests->count() : 0 }})
                    </a>
                    <a href="#" 
                            id="approved-tab"
                            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Approved ({{ isset($approvedRequests) ? $approvedRequests->count() : 0 }})
                    </a>
                    <a href="#" 
                            id="rejected-tab"
                            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Rejected ({{ isset($rejectedRequests) ? $rejectedRequests->count() : 0 }})
                    </a>
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
                                <x-donation-request-card :request="$request" />
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
                                <x-donation-request-card :request="$request" />
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
                                <x-donation-request-card :request="$request" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reject Modal -->
            <div id="reject-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 text-center mt-4">Reject Request</h3>
                        <div class="mt-2 px-7 py-3">
                            <textarea id="rejection_reason" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter reason for rejection"
                                    rows="3"></textarea>
                        </div>
                        <div class="flex justify-end mt-4 px-4 py-3 bg-gray-50 text-right sm:px-6 rounded-b-md">
                            <button onclick="hideRejectModal()" 
                                    class="mr-2 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button onclick="submitRejectForm()" 
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('DOM loaded');
                    // Add click event listeners to all tabs
                    document.querySelectorAll('[id$="-tab"]').forEach(function(tab) {
                        tab.addEventListener('click', function(e) {
                            e.preventDefault();
                            const tabName = this.id.replace('-tab', '');
                            console.log('Tab clicked:', tabName);
                            showTab(tabName);
                        });
                    });

                    // Show pending tab by default
                    showTab('pending');
                });

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

                function showTab(tabName) {
                    console.log('Showing tab:', tabName);
                    
                    // Get all tab contents and hide them
                    const tabContents = document.querySelectorAll('.tab-content');
                    console.log('Found tab contents:', tabContents.length);
                    tabContents.forEach(function(content) {
                        console.log('Hiding content:', content.id);
                        content.classList.add('hidden');
                    });

                    // Show the selected tab content
                    const selectedContent = document.getElementById(tabName + '-content');
                    console.log('Selected content:', selectedContent?.id);
                    if (selectedContent) {
                        selectedContent.classList.remove('hidden');
                    }

                    // Update tab styles
                    document.querySelectorAll('[id$="-tab"]').forEach(function(tab) {
                        console.log('Updating tab style:', tab.id);
                        tab.classList.remove('border-indigo-500', 'text-indigo-600', 'border-b-2');
                        tab.classList.add('border-transparent', 'text-gray-500');
                    });

                    const activeTab = document.getElementById(tabName + '-tab');
                    console.log('Active tab:', activeTab?.id);
                    if (activeTab) {
                        activeTab.classList.remove('border-transparent', 'text-gray-500');
                        activeTab.classList.add('border-indigo-500', 'text-indigo-600', 'border-b-2');
                    }
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
            </script>

            <style>
                .rotate-180 {
                    transform: rotate(180deg);
                }
            </style>
        </div>
    </div>
</x-app-layout>

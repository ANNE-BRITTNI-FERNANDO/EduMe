<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Donation Request Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Request Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Donation Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Donation Information</h3>
                            <div class="space-y-3">
                                @if($donationRequest && $donationRequest->donationItem)
                                    <p class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Item Name:</span>
                                        <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donationRequest->donationItem->item_name }}</span>
                                    </p>
                                    <p class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Category:</span>
                                        <span class="text-gray-600 dark:text-gray-400 ml-2">{{ ucfirst($donationRequest->donationItem->category) }}</span>
                                    </p>
                                    @if($donationRequest->donationItem->user)
                                        <p class="text-sm">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">Donor:</span>
                                            <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donationRequest->donationItem->user->name }}</span>
                                        </p>
                                    @endif
                                @else
                                    <p class="text-sm text-red-500">Donation item information not available</p>
                                @endif
                            </div>
                        </div>

                        <!-- Request Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Request Information</h3>
                            <div class="space-y-3">
                                @if($donationRequest && $donationRequest->user)
                                    <p class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Requested By:</span>
                                        <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donationRequest->user->name }}</span>
                                    </p>
                                @else
                                    <p class="text-sm text-red-500">Requester information not available</p>
                                @endif

                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Quantity:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donationRequest->quantity }}</span>
                                </p>

                                @if($donationRequest->pickup_date && !is_string($donationRequest->pickup_date))
                                    <p class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Pickup Date:</span>
                                        <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donationRequest->pickup_date->format('M d, Y') }}</span>
                                    </p>
                                @elseif($donationRequest->pickup_date)
                                    <p class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Pickup Date:</span>
                                        <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donationRequest->pickup_date }}</span>
                                    </p>
                                @endif

                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Contact Number:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donationRequest->contact_number }}</span>
                                </p>

                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $donationRequest->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                           ($donationRequest->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                           'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($donationRequest->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Purpose</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $donationRequest->purpose }}</p>
                    </div>

                    @if($donationRequest->notes)
                        <div class="mt-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Additional Notes</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $donationRequest->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($donationRequest->status === 'pending')
                <!-- Action Buttons -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-end space-x-4">
                            <!-- Reject Button -->
                            <button onclick="openRejectModal()" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Reject Request
                            </button>

                            <!-- Approve Button -->
                            <button onclick="openApproveModal()" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Approve Request
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Approve Modal -->
                <div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Approve Donation Request</h3>
                            <form action="{{ route('admin.donations.requests.approve', $donationRequest) }}" method="POST">
                                @csrf
                                <div class="mt-4">
                                    <label for="approve_admin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Notes (Optional)</label>
                                    <textarea name="admin_notes" id="approve_admin_notes" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"></textarea>
                                </div>

                                <div class="flex justify-end space-x-4 mt-4">
                                    <button type="button" onclick="closeApproveModal()"
                                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        Confirm Approval
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Reject Donation Request</h3>
                            <form action="{{ route('admin.donations.requests.reject', $donationRequest) }}" method="POST">
                                @csrf
                                <div class="mt-2">
                                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason for Rejection</label>
                                    <textarea name="rejection_reason" id="rejection_reason" rows="3" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                              required></textarea>
                                </div>

                                <div class="mt-4">
                                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Notes (Optional)</label>
                                    <textarea name="admin_notes" id="admin_notes" rows="2" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"></textarea>
                                </div>

                                <div class="flex justify-end space-x-4 mt-4">
                                    <button type="button" onclick="closeRejectModal()"
                                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        Confirm Rejection
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        function openApproveModal() {
            document.getElementById('approveModal').classList.remove('hidden');
        }

        function closeApproveModal() {
            document.getElementById('approveModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>

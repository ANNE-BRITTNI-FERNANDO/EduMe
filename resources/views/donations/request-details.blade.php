<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Request Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Request Status Banner -->
                    <div class="mb-6">
                        @if($request->status === 'pending')
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                                <p class="font-bold">Request Status: Pending</p>
                                <p>Your request is being reviewed by the donor.</p>
                            </div>
                        @elseif($request->status === 'approved')
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                                <p class="font-bold">Request Status: Approved</p>
                                <p>Your request has been approved! You can now proceed with the donation collection.</p>
                            </div>
                        @elseif($request->status === 'rejected')
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                                <p class="font-bold">Request Status: Rejected</p>
                                <p>Unfortunately, your request was not approved.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Donation Item Details -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Donation Item Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Item Name</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->item_name }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($request->donationItem->category) }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Education Level</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($request->donationItem->education_level) }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condition</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $request->donationItem->condition)) }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->donationItem->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Request Details -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Request Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Requested Quantity</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->quantity }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purpose</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->purpose }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Number</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->contact_number }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document Type</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ ucfirst(str_replace('_', ' ', $request->purpose_details['document_type'] ?? '')) }}
                                    </p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Verification Document</label>
                                    @if(isset($request->purpose_details['document_path']))
                                        <a href="{{ asset('storage/' . $request->purpose_details['document_path']) }}" 
                                           target="_blank"
                                           class="mt-1 inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                            View Document
                                        </a>
                                    @else
                                        <p class="mt-1 text-sm text-gray-500">No document uploaded</p>
                                    @endif
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Requested On</label>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $request->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center mt-6">
                        <a href="{{ url()->previous() }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Back
                        </a>
                        @if($request->status === 'approved')
                            <a href="{{ route('donation.chat.show', $request) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Chat with Donor
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

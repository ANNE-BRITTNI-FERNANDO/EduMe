<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Item Donation Details</h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.donations.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200">
                    Back to Donations
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Banner -->
            <div class="mb-6 overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                <div class="w-full {{ 
                    $donation->status === 'received' ? 'bg-green-500' : 
                    ($donation->status === 'verified' ? 'bg-blue-500' :
                    ($donation->status === 'under_review' ? 'bg-purple-500' :
                    ($donation->status === 'pending' || $donation->status === 'available' ? 'bg-yellow-500' :
                    ($donation->status === 'unavailable' ? 'bg-gray-500' : 'bg-red-500')))) 
                }} text-white px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-lg font-semibold">Status: {{ ucfirst($donation->status) }}</p>
                            @if($donation->received_at)
                                <p class="text-sm">Received on: {{ $donation->received_at->format('Y-m-d H:i') }}</p>
                            @endif
                            @if($donation->verified_at)
                                <p class="text-sm">Verified on: {{ $donation->verified_at->format('Y-m-d H:i') }}</p>
                            @endif
                            @if($donation->review_started_at)
                                <p class="text-sm">Review started: {{ $donation->review_started_at->format('Y-m-d H:i') }}</p>
                            @endif
                        </div>
                        <div class="flex space-x-2">
                            @if($donation->status === 'pending' || $donation->status === 'available')
                                <form action="{{ route('admin.donations.items.start-review', $donation) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="is_available" value="1">
                                    <button type="submit" class="bg-white text-purple-600 hover:bg-purple-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200">
                                        Start Review
                                    </button>
                                </form>
                            @elseif($donation->status === 'under_review')
                                <form action="{{ route('admin.donations.items.verify', $donation) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="condition_matches" value="1">
                                    <input type="hidden" name="quantity_matches" value="1">
                                    <input type="hidden" name="is_available" value="1">
                                    <button type="submit" class="bg-white text-blue-600 hover:bg-blue-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200">
                                        Verify Details
                                    </button>
                                </form>
                                <button type="button" 
                                    onclick="document.getElementById('rejection-modal').classList.remove('hidden')"
                                    class="bg-white text-red-600 hover:bg-red-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200">
                                    Reject
                                </button>

                                <!-- Rejection Modal -->
                                <div id="rejection-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                                        <div class="mt-3">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Reject Donation</h3>
                                            <form action="{{ route('admin.donations.reject', ['type' => 'item', 'id' => $donation->id]) }}" method="POST">
                                                @csrf
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        Rejection Reason
                                                    </label>
                                                    <select name="rejection_reason" 
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
                                                        onclick="document.getElementById('rejection-modal').classList.add('hidden')"
                                                        class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 rounded-lg">
                                                        Cancel
                                                    </button>
                                                    <button type="submit"
                                                        class="bg-red-600 text-white hover:bg-red-700 px-4 py-2 rounded-lg">
                                                        Confirm Rejection
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @elseif($donation->status === 'verified')
                                <form action="{{ route('admin.donations.items.receive', $donation) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-white text-green-600 hover:bg-green-50 font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200">
                                        Mark as Received
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Donor Information -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Donor Information</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $donation->is_anonymous ? 'Anonymous' : ($donation->user ? $donation->user->name : 'N/A') }}
                                </p>
                            </div>
                            @if(!$donation->is_anonymous && $donation->user)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->user->email }}</p>
                            </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Contact Number</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->contact_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item Details -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Item Details</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Item Name</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->item_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($donation->category) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Education Level</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($donation->education_level) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Quantity</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->quantity }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Condition</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($donation->condition) }}</p>
                            </div>
                            @if($donation->description)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->description }}</p>
                            </div>
                            @endif
                            @if($donation->images && count($donation->images) > 0)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Images</p>
                                <div class="mt-2 grid grid-cols-2 gap-4">
                                    @foreach($donation->images as $image)
                                        <div class="relative aspect-w-16 aspect-h-9 overflow-hidden rounded-lg">
                                            <img src="{{ Storage::url($image) }}" 
                                                 alt="Donation image" 
                                                 class="object-cover w-full h-full">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Pickup Information -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pickup Information</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pickup Address</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->pickup_address ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Preferred Pickup Date</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $donation->preferred_pickup_date ? $donation->preferred_pickup_date->format('Y-m-d') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Information -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Admin Information</h3>
                        <div class="mt-4 space-y-4">
                            @if($donation->reviewer)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Reviewed By</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->reviewer->name }}</p>
                            </div>
                            @endif
                            @if($donation->verifier)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified By</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->verifier->name }}</p>
                            </div>
                            @endif
                            @if($donation->receiver)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Received By</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->receiver->name }}</p>
                            </div>
                            @endif
                            @if($donation->rejector)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected By</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->rejector->name }}</p>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mt-2">Rejection Reason</p>
                                <p class="mt-1 text-sm text-red-600">{{ $donation->rejection_reason }}</p>
                            </div>
                            @endif
                            @if($donation->notes)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Admin Notes</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $donation->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item Images -->
            {{-- Removed --}}
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Bundle Approval Management') }}
            </h2>
            <div class="flex gap-4">
                <form id="filter-form" action="" method="get">
                    <select id="status-filter" name="status" class="text-white bg-gray-800 border border-gray-600 rounded-md shadow-sm px-4 py-2" onchange="document.getElementById('filter-form').submit()">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Bundles</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <select id="sort-by" name="sort_by" class="text-white bg-gray-800 border border-gray-600 rounded-md shadow-sm px-4 py-2" onchange="document.getElementById('filter-form').submit()">
                        <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="price-high" {{ request('sort_by') == 'price-high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="price-low" {{ request('sort_by') == 'price-low' ? 'selected' : '' }}>Price: Low to High</option>
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Bundle List -->
            <div class="space-y-4">
                @forelse ($bundles as $bundle)
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden bundle-card" 
                         data-status="{{ $bundle->status }}" 
                         data-date="{{ $bundle->created_at->timestamp }}" 
                         data-price="{{ $bundle->price }}">
                        <!-- Bundle Header -->
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600 flex justify-between items-center">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('storage/' . $bundle->bundle_image) }}" alt="Bundle Image" 
                                         class="w-16 h-16 object-cover rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $bundle->bundle_name }}
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full 
                                            {{ $bundle->is_sold ? 'bg-gray-100 text-gray-800' : 
                                               ($bundle->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($bundle->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                'bg-red-100 text-red-800')) }}">
                                            {{ $bundle->is_sold ? 'Sold' : ucfirst($bundle->status) }}
                                        </span>
                                    </h3>
                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <span>Price: ${{ number_format($bundle->price, 2) }}</span>
                                        <span class="mx-2">•</span>
                                        <span>Seller: {{ $bundle->user->name }}</span>
                                        <span class="mx-2">•</span>
                                        <span>Submitted: {{ $bundle->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                    onclick="toggleBundleDetails('{{ $bundle->id }}')">
                                <svg class="w-6 h-6 transform transition-transform" id="chevron-{{ $bundle->id }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Bundle Details (Hidden by default) -->
                        <div id="bundle-details-{{ $bundle->id }}" class="hidden">
                            <div class="p-4 space-y-4">
                                <!-- Bundle Description -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Description</h4>
                                    <p class="text-gray-600 dark:text-gray-300">{{ $bundle->description }}</p>
                                </div>

                                <!-- Bundle Items -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($bundle->categories as $category)
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                            <div class="mb-3">
                                                <img src="{{ asset('storage/' . $category->category_image) }}" 
                                                     alt="{{ $category->category }}" 
                                                     class="w-full h-40 object-cover rounded-lg">
                                            </div>
                                            <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-2">
                                                {{ $category->category }}
                                            </h5>
                                            <div class="space-y-2">
                                                <select name="category_status[{{ $category->id }}]" 
                                                        class="category-status text-white w-full bg-gray-800 border border-gray-600 rounded-md shadow-sm"
                                                        data-category-id="{{ $category->id }}">
                                                    <option value="approved" {{ $category->status == 'approved' ? 'selected' : '' }} class="text-white">Approve</option>
                                                    <option value="rejected" {{ $category->status == 'rejected' ? 'selected' : '' }} class="text-white">Reject</option>
                                                    <option value="pending" {{ $category->status == 'pending' ? 'selected' : '' }} class="text-white">Pending</option>
                                                </select>
                                                <select name="category_rejection_reason[{{ $category->id }}]" 
                                                        class="rejection-reason text-white w-full bg-gray-800 border border-gray-600 rounded-md shadow-sm {{ $category->status == 'rejected' ? '' : 'hidden' }}"
                                                        data-category-id="{{ $category->id }}">
                                                    <option value="" class="text-white">Select Rejection Reason</option>
                                                    <option value="low_quality_images" class="text-white" {{ $category->rejection_reason == 'low_quality_images' ? 'selected' : '' }}>Low Quality Images</option>
                                                    <option value="missing_images" class="text-white" {{ $category->rejection_reason == 'missing_images' ? 'selected' : '' }}>Missing Images</option>
                                                    <option value="inappropriate_images" class="text-white" {{ $category->rejection_reason == 'inappropriate_images' ? 'selected' : '' }}>Inappropriate Images</option>
                                                    <option value="inappropriate_content" class="text-white" {{ $category->rejection_reason == 'inappropriate_content' ? 'selected' : '' }}>Inappropriate Content</option>
                                                    <option value="misleading_information" class="text-white" {{ $category->rejection_reason == 'misleading_information' ? 'selected' : '' }}>Misleading Information</option>
                                                    <option value="incomplete_description" class="text-white" {{ $category->rejection_reason == 'incomplete_description' ? 'selected' : '' }}>Incomplete Description</option>
                                                    <option value="incorrect_pricing" class="text-white" {{ $category->rejection_reason == 'incorrect_pricing' ? 'selected' : '' }}>Incorrect Pricing</option>
                                                    <option value="unreasonable_price" class="text-white" {{ $category->rejection_reason == 'unreasonable_price' ? 'selected' : '' }}>Unreasonable Price</option>
                                                    <option value="incompatible_items" class="text-white" {{ $category->rejection_reason == 'incompatible_items' ? 'selected' : '' }}>Incompatible Items</option>
                                                    <option value="missing_items" class="text-white" {{ $category->rejection_reason == 'missing_items' ? 'selected' : '' }}>Missing Items</option>
                                                    <option value="other" class="text-white" {{ $category->rejection_reason == 'other' ? 'selected' : '' }}>Other (Specify Below)</option>
                                                </select>
                                                <textarea name="category_rejection_detail[{{ $category->id }}]" 
                                                          class="rejection-detail text-white w-full bg-gray-800 border border-gray-600 rounded-md shadow-sm {{ $category->status == 'rejected' && $category->rejection_reason == 'other' ? '' : 'hidden' }}"
                                                          data-category-id="{{ $category->id }}"
                                                          placeholder="Please provide additional details about the rejection..."
                                                          rows="3">{{ $category->rejection_details }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Bundle Actions -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="space-y-3">
                                        <select id="bundle_status_{{ $bundle->id }}" 
                                                class="bundle-status text-white w-full md:w-auto bg-gray-800 border border-gray-600 rounded-md shadow-sm">
                                            <option value="" class="text-white">Select Bundle Action</option>
                                            <option value="approved" class="text-white">Approve Bundle</option>
                                            <option value="rejected" class="text-white">Reject Bundle</option>
                                            <option value="pending" class="text-white">Mark as Pending</option>
                                        </select>
                                        
                                        <select id="rejection_reason_{{ $bundle->id }}"
                                                class="rejection-reason hidden text-white w-full md:w-auto bg-gray-800 border border-gray-600 rounded-md shadow-sm">
                                            <option value="" class="text-white">Select Rejection Reason</option>
                                            <optgroup label="Image Issues">
                                                <option value="low_quality_images" class="text-white">Low Quality Images</option>
                                                <option value="missing_images" class="text-white">Missing Images</option>
                                                <option value="inappropriate_images" class="text-white">Inappropriate Images</option>
                                            </optgroup>
                                            <optgroup label="Content Issues">
                                                <option value="inappropriate_content" class="text-white">Inappropriate Content</option>
                                                <option value="misleading_information" class="text-white">Misleading Information</option>
                                                <option value="incomplete_description" class="text-white">Incomplete Description</option>
                                            </optgroup>
                                            <optgroup label="Pricing Issues">
                                                <option value="incorrect_pricing" class="text-white">Incorrect Pricing</option>
                                                <option value="unreasonable_price" class="text-white">Unreasonable Price</option>
                                            </optgroup>
                                            <optgroup label="Bundle Issues">
                                                <option value="incompatible_items" class="text-white">Incompatible Items</option>
                                                <option value="missing_items" class="text-white">Missing Items</option>
                                            </optgroup>
                                            <option value="other" class="text-white">Other (Specify Below)</option>
                                        </select>

                                        <textarea id="custom_reason_{{ $bundle->id }}"
                                                  class="custom-reason hidden text-white w-full bg-gray-800 border border-gray-600 rounded-md shadow-sm"
                                                  placeholder="Please provide details about the rejection..."
                                                  rows="3"></textarea>

                                        <div class="flex justify-end mt-4">
                                            <button type="button"
                                                    class="update-bundle-status px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                                    data-bundle-id="{{ $bundle->id }}">
                                                Update Status
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-8 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No bundles available for review.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($bundles->count() > 0 && method_exists($bundles, 'links'))
                <div class="mt-4">
                    {{ $bundles->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleBundleDetails(bundleId) {
            const details = document.getElementById(`bundle-details-${bundleId}`);
            const chevron = document.getElementById(`chevron-${bundleId}`);
            details.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Handle category status changes
            document.querySelectorAll('.category-status').forEach(select => {
                select.addEventListener('change', (e) => {
                    const categoryId = e.target.getAttribute('data-category-id');
                    const rejectionReason = e.target.closest('.space-y-2').querySelector('.rejection-reason');
                    const rejectionDetail = e.target.closest('.space-y-2').querySelector('.rejection-detail');
                    
                    if (e.target.value === 'rejected') {
                        rejectionReason.classList.remove('hidden');
                    } else {
                        rejectionReason.classList.add('hidden');
                        rejectionDetail.classList.add('hidden');
                        rejectionReason.value = '';
                        rejectionDetail.value = '';
                    }
                });
            });

            // Handle rejection reason changes
            document.querySelectorAll('.rejection-reason').forEach(select => {
                select.addEventListener('change', (e) => {
                    const rejectionDetail = e.target.closest('.space-y-2').querySelector('.rejection-detail');
                    if (e.target.value === 'other') {
                        rejectionDetail.classList.remove('hidden');
                    } else {
                        rejectionDetail.classList.add('hidden');
                        rejectionDetail.value = '';
                    }
                });
            });

            // Handle bundle status changes
            document.querySelectorAll('.bundle-status').forEach(select => {
                select.addEventListener('change', (e) => {
                    const bundleId = e.target.id.split('_').pop();
                    const rejectionReasonSelect = document.getElementById(`rejection_reason_${bundleId}`);
                    const customReasonTextarea = document.getElementById(`custom_reason_${bundleId}`);

                    if (e.target.value === 'rejected') {
                        rejectionReasonSelect.classList.remove('hidden');
                    } else {
                        rejectionReasonSelect.classList.add('hidden');
                        customReasonTextarea.classList.add('hidden');
                    }
                });
            });

            // Handle update status button clicks
            document.querySelectorAll('.update-bundle-status').forEach(button => {
                button.addEventListener('click', async () => {
                    const bundleId = button.getAttribute('data-bundle-id');
                    const statusSelect = document.getElementById(`bundle_status_${bundleId}`);
                    const rejectionSelect = document.getElementById(`rejection_reason_${bundleId}`);
                    const customReasonText = document.getElementById(`custom_reason_${bundleId}`);

                    if (!statusSelect.value) {
                        alert('Please select a bundle status');
                        return;
                    }

                    // Collect category statuses
                    const categoryStatuses = {};
                    let hasInvalidCategory = false;
                    
                    // Find all category status selects within this bundle's container
                    const bundleContainer = button.closest('.bundle-card');
                    bundleContainer.querySelectorAll('.category-status').forEach(categorySelect => {
                        const categoryId = categorySelect.getAttribute('data-category-id');
                        const rejectionReasonSelect = bundleContainer.querySelector(`.rejection-reason[data-category-id="${categoryId}"]`);
                        const rejectionDetailTextarea = bundleContainer.querySelector(`.rejection-detail[data-category-id="${categoryId}"]`);
                        
                        if (!categorySelect.value) {
                            alert('Please select a status for all categories');
                            hasInvalidCategory = true;
                            return;
                        }

                        categoryStatuses[categoryId] = {
                            status: categorySelect.value,
                            rejection_reason: null,
                            rejection_details: null
                        };

                        if (categorySelect.value === 'rejected') {
                            if (!rejectionReasonSelect || !rejectionReasonSelect.value) {
                                alert('Please select a rejection reason for all rejected categories');
                                hasInvalidCategory = true;
                                return;
                            }

                            categoryStatuses[categoryId].rejection_reason = rejectionReasonSelect.value;
                            
                            if (rejectionReasonSelect.value === 'other') {
                                if (!rejectionDetailTextarea || !rejectionDetailTextarea.value.trim()) {
                                    alert('Please provide rejection details for categories with "Other" reason');
                                    hasInvalidCategory = true;
                                    return;
                                }
                                categoryStatuses[categoryId].rejection_details = rejectionDetailTextarea.value.trim();
                            }
                        }
                    });

                    if (hasInvalidCategory) {
                        return;
                    }

                    // Prepare bundle data
                    let bundleData = {
                        status: statusSelect.value,
                        category_status: categoryStatuses
                    };

                    if (statusSelect.value === 'rejected') {
                        if (!rejectionSelect.value) {
                            alert('Please select a bundle rejection reason');
                            return;
                        }
                        bundleData.rejection_reason = rejectionSelect.value;
                        bundleData.rejection_details = rejectionSelect.value === 'other' ? customReasonText.value : '';
                    }

                    try {
                        const response = await fetch(`/admin/bundles/${bundleId}/updateStatus`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(bundleData)
                        });

                        const result = await response.json();
                        
                        if (result.success) {
                            // Refresh the page to show updated statuses
                            window.location.reload();
                        } else {
                            alert('Failed to update status: ' + (result.message || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while updating the status');
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>

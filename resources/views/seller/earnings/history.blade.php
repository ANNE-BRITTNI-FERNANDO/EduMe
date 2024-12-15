<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Payout History</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payouts as $payout)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">#{{ $payout->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${{ number_format($payout->amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $payout->payment_method)) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($payout->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($payout->status === 'approved') bg-green-100 text-green-800
                                            @elseif($payout->status === 'completed') bg-blue-100 text-blue-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($payout->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $payout->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="toggleDetails({{ $payout->id }})" 
                                                class="text-indigo-600 hover:text-indigo-900">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                                <tr id="details-{{ $payout->id }}" class="hidden bg-gray-50">
                                    <td colspan="6" class="px-6 py-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <h3 class="text-lg font-semibold mb-4">Payment Details</h3>
                                                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                                                    <dl>
                                                        @if($payout->payment_method === 'bank_transfer')
                                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                                                <dt class="text-sm font-medium text-gray-500">Bank Name</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payout->payment_details['bank_name'] }}</dd>
                                                            </div>
                                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                                                <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payout->payment_details['account_number'] }}</dd>
                                                            </div>
                                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                                                <dt class="text-sm font-medium text-gray-500">Account Holder</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payout->payment_details['account_holder_name'] }}</dd>
                                                            </div>
                                                        @elseif($payout->payment_method === 'paypal')
                                                            <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                                                <dt class="text-sm font-medium text-gray-500">PayPal Email</dt>
                                                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payout->payment_details['paypal_email'] }}</dd>
                                                            </div>
                                                        @endif
                                                    </dl>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold mb-4">Status History</h3>
                                                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                                                    <div class="px-4 py-3">
                                                        <div class="space-y-4">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0">
                                                                    <div class="h-2 w-2 rounded-full bg-green-600"></div>
                                                                </div>
                                                                <div class="ml-4 flex-1">
                                                                    <p class="text-sm font-medium text-gray-900">Request Created</p>
                                                                    <p class="text-sm text-gray-500">{{ $payout->created_at->format('M d, Y H:i') }}</p>
                                                                </div>
                                                            </div>
                                                            @if($payout->status !== 'pending')
                                                                <div class="flex items-center">
                                                                    <div class="flex-shrink-0">
                                                                        <div class="h-2 w-2 rounded-full {{ $payout->status === 'approved' || $payout->status === 'completed' ? 'bg-green-600' : 'bg-red-600' }}"></div>
                                                                    </div>
                                                                    <div class="ml-4 flex-1">
                                                                        <p class="text-sm font-medium text-gray-900">
                                                                            {{ $payout->status === 'approved' ? 'Request Approved' : ($payout->status === 'completed' ? 'Payment Completed' : 'Request Rejected') }}
                                                                        </p>
                                                                        <p class="text-sm text-gray-500">{{ $payout->updated_at->format('M d, Y H:i') }}</p>
                                                                        @if($payout->status === 'rejected' && $payout->rejection_reason)
                                                                            <p class="text-sm text-red-600 mt-1">Reason: {{ $payout->rejection_reason }}</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            @if($payout->status === 'completed' && $payout->transaction_id)
                                                                <div class="flex items-center">
                                                                    <div class="flex-shrink-0">
                                                                        <div class="h-2 w-2 rounded-full bg-green-600"></div>
                                                                    </div>
                                                                    <div class="ml-4 flex-1">
                                                                        <p class="text-sm font-medium text-gray-900">Transaction Completed</p>
                                                                        <p class="text-sm text-gray-500">Transaction ID: {{ $payout->transaction_id }}</p>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No payout requests found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($payouts->hasPages())
                    <div class="mt-4">
                        {{ $payouts->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleDetails(id) {
            const detailsRow = document.getElementById('details-' + id);
            if (detailsRow.classList.contains('hidden')) {
                // Close any other open details first
                document.querySelectorAll('[id^="details-"]').forEach(row => {
                    if (!row.classList.contains('hidden')) {
                        row.classList.add('hidden');
                    }
                });
                // Open this details row
                detailsRow.classList.remove('hidden');
            } else {
                detailsRow.classList.add('hidden');
            }
        }
    </script>
    @endpush
</x-app-layout>

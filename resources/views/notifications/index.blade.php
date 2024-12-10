<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Notifications</h2>
                    
                    @forelse(auth()->user()->notifications as $notification)
                        <div class="mb-4 p-4 border rounded-lg {{ $notification->read_at ? 'bg-gray-50' : 'bg-blue-50' }}">
                            @if($notification->type === 'App\\Notifications\\PaymentSlipUploaded')
                                <div class="flex items-start">
                                    <svg class="h-6 w-6 text-blue-500 mr-2 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="flex-grow">
                                        <p class="font-semibold">New Payment Slip Uploaded</p>
                                        <p class="text-sm text-gray-600">Amount: ${{ number_format($notification->data['amount'], 2) }}</p>
                                        
                                        <!-- Payment Slip Preview -->
                                        @php
                                            $slipPath = $notification->data['payment_slip_path'] ?? null;
                                        @endphp
                                        @if($slipPath)
                                        <div class="mt-2">
                                            <p class="text-sm font-medium text-gray-700">Payment Slip:</p>
                                            <a href="{{ url('storage/' . $slipPath) }}" 
                                               target="_blank" 
                                               class="inline-flex items-center mt-1 text-sm text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View Payment Slip
                                            </a>
                                        </div>
                                        @endif

                                        @if(isset($notification->data['bank_details']) && $notification->data['bank_details'])
                                        <div class="mt-2">
                                            <p class="text-sm font-medium text-gray-700">Bank Details:</p>
                                            <p class="text-sm text-gray-600">{{ $notification->data['bank_details'] }}</p>
                                        </div>
                                        @endif

                                        @if($notification->type === 'App\\Notifications\\PaymentSlipUploaded')
                                        <div class="mt-3 flex space-x-2">
                                            <button onclick="confirmPayment({{ $notification->data['conversation_id'] }})" 
                                                    class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Confirm Payment
                                            </button>
                                            <button onclick="rejectPayment({{ $notification->data['conversation_id'] }})" 
                                                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Reject Payment
                                            </button>
                                        </div>
                                        @endif
                                        <a href="{{ route('chat.show', $notification->data['conversation_id']) }}" 
                                           class="text-blue-500 hover:underline text-sm block mt-2">
                                            View Conversation
                                        </a>
                                    </div>
                                </div>
                            @elseif($notification->type === 'App\\Notifications\\PaymentConfirmed')
                                <div class="flex items-center">
                                    <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <div class="flex-grow">
                                        <p class="font-semibold">Payment Confirmed</p>
                                        <p class="text-sm text-gray-600">Amount: ${{ number_format($notification->data['amount'], 2) }}</p>
                                        
                                        @if(isset($notification->data['payment_slip_path']))
                                        <div class="mt-2">
                                            <p class="text-sm font-medium text-gray-700">Payment Slip:</p>
                                            <a href="{{ url('storage/' . $notification->data['payment_slip_path']) }}" 
                                               target="_blank" 
                                               class="inline-flex items-center mt-1 text-sm text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View Payment Slip
                                            </a>
                                        </div>
                                        @endif
                                        
                                        <a href="{{ route('chat.show', $notification->data['conversation_id']) }}" class="text-blue-500 hover:underline text-sm block mt-1">View Details</a>
                                    </div>
                                </div>
                            @elseif($notification->type === 'App\\Notifications\\PaymentRejected')
                                <div class="flex items-center">
                                    <svg class="h-6 w-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <div class="flex-grow">
                                        <p class="font-semibold">Payment Rejected</p>
                                        <p class="text-sm text-gray-600">Amount: ${{ number_format($notification->data['amount'], 2) }}</p>
                                        @if(isset($notification->data['rejection_reason']))
                                            <p class="text-sm text-red-600">Reason: {{ $notification->data['rejection_reason'] }}</p>
                                        @endif
                                        
                                        @if(isset($notification->data['payment_slip_path']))
                                        <div class="mt-2">
                                            <p class="text-sm font-medium text-gray-700">Payment Slip:</p>
                                            <a href="{{ url('storage/' . $notification->data['payment_slip_path']) }}" 
                                               target="_blank" 
                                               class="inline-flex items-center mt-1 text-sm text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View Payment Slip
                                            </a>
                                        </div>
                                        @endif
                                        
                                        <a href="{{ route('chat.show', $notification->data['conversation_id']) }}" class="text-blue-500 hover:underline text-sm block mt-1">View Details</a>
                                    </div>
                                </div>
                            @endif
                            <p class="text-xs text-gray-500 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500">No notifications yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmPayment(conversationId) {
            if (confirm('Are you sure you want to confirm this payment?')) {
                axios.post(`/bank-transfer/${conversationId}/confirm`, {}, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(response => {
                        if (response.data.success) {
                            alert('Payment confirmed successfully');
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.response?.data?.message || 'Failed to confirm payment');
                    });
            }
        }

        function rejectPayment(conversationId) {
            const reason = prompt('Please enter the reason for rejection:');
            if (reason !== null) {
                axios.post(`/bank-transfer/${conversationId}/reject`, {
                    reason: reason
                }, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                    .then(response => {
                        if (response.data.success) {
                            alert('Payment rejected successfully');
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.response?.data?.message || 'Failed to reject payment');
                    });
            }
        }
    </script>
    @endpush
</x-app-layout>

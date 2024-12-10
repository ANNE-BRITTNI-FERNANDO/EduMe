<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Manage Payments
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Pending Payments</h3>
                    
                    @forelse($pendingPayments as $payment)
                        <div class="mb-6 p-4 border rounded-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold">Payment #{{ $payment->id }}</p>
                                    <p class="text-sm text-gray-600">Amount: ${{ number_format($payment->amount, 2) }}</p>
                                    <p class="text-sm text-gray-600">From: {{ $payment->conversation->buyer->name }}</p>
                                    <p class="text-sm text-gray-600">Date: {{ $payment->created_at->format('M d, Y H:i') }}</p>
                                    
                                    @if($payment->payment_slip_path)
                                        <a href="{{ Storage::url($payment->payment_slip_path) }}" 
                                           target="_blank"
                                           class="text-blue-500 hover:underline text-sm inline-flex items-center mt-2">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Payment Slip
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button onclick="confirmPayment({{ $payment->id }})" 
                                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        Confirm
                                    </button>
                                    <button onclick="rejectPayment({{ $payment->id }})" 
                                            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        Reject
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-sm text-gray-600">
                                <p class="font-semibold">Bank Transfer Details:</p>
                                <p class="whitespace-pre-line">{{ $payment->bank_details }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">No pending payments.</p>
                    @endforelse

                    <h3 class="text-lg font-semibold mb-4 mt-8">Recent Payments</h3>
                    @forelse($recentPayments as $payment)
                        <div class="mb-4 p-4 border rounded-lg {{ $payment->status === 'confirmed' ? 'bg-green-50' : 'bg-red-50' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-semibold">Payment #{{ $payment->id }}</p>
                                    <p class="text-sm text-gray-600">Amount: ${{ number_format($payment->amount, 2) }}</p>
                                    <p class="text-sm text-gray-600">From: {{ $payment->conversation->buyer->name }}</p>
                                    <p class="text-sm text-gray-600">Date: {{ $payment->created_at->format('M d, Y H:i') }}</p>
                                    <p class="text-sm {{ $payment->status === 'confirmed' ? 'text-green-600' : 'text-red-600' }}">
                                        Status: {{ ucfirst($payment->status) }}
                                        @if($payment->status === 'rejected')
                                            - {{ $payment->rejection_reason }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">No recent payments.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function confirmPayment(paymentId) {
            if (!confirm('Are you sure you want to confirm this payment?')) return;

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            fetch(`/bank-transfer/${paymentId}/confirm`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment confirmed successfully.');
                    location.reload();
                } else {
                    alert(data.message || 'Error confirming payment.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error confirming payment. Please try again.');
            });
        }

        function rejectPayment(paymentId) {
            const reason = prompt('Please enter reason for rejection:');
            if (!reason) return;

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('reason', reason);

            fetch(`/bank-transfer/${paymentId}/reject`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment rejected successfully.');
                    location.reload();
                } else {
                    alert(data.message || 'Error rejecting payment.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error rejecting payment. Please try again.');
            });
        }
    </script>
    @endpush
</x-app-layout>

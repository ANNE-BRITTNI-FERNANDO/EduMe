<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Notifications</h2>
                    
                    @forelse(auth()->user()->notifications()->latest()->get() as $notification)
                        <div class="mb-4 p-4 border rounded-lg {{ $notification->read_at ? 'bg-gray-50' : 'bg-blue-50' }}">
                            @php
                                $notificationType = class_basename($notification->type);
                            @endphp

                            <div class="flex items-start">
                                <!-- Icon based on notification type -->
                                <div class="flex-shrink-0">
                                    @if($notificationType === 'NewOrderNotification')
                                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    @elseif($notificationType === 'NewMessage')
                                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                        </svg>
                                    @elseif(str_contains($notificationType, 'Bundle'))
                                        <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    @else
                                        <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                    @endif
                                </div>

                                <div class="ml-4 flex-1">
                                    <!-- Notification Content -->
                                    <div class="flex justify-between items-start">
                                        <div>
                                            @if($notificationType === 'NewOrderNotification')
                                                <p class="font-semibold text-gray-900">New Order Received</p>
                                                <p class="text-sm text-gray-600">Order #{{ $notification->data['order_id'] }}</p>
                                                <p class="text-sm text-gray-600">Amount: ₹{{ number_format($notification->data['amount'], 2) }}</p>
                                                <p class="text-sm text-gray-600">From: {{ $notification->data['buyer_name'] }}</p>
                                            @elseif($notificationType === 'NewMessage')
                                                <p class="font-semibold text-gray-900">New Message from {{ $notification->data['sender_name'] }}</p>
                                                <p class="mt-1 text-sm text-gray-600 bg-gray-50 p-2 rounded">
                                                    "{{ $notification->data['content_preview'] }}"
                                                </p>
                                            @elseif($notificationType === 'BundleSubmittedForReview')
                                                <p class="font-semibold text-gray-900">New Bundle Submission</p>
                                                <p class="text-sm text-gray-600">Bundle: {{ $notification->data['bundle_name'] }}</p>
                                                <p class="text-sm text-gray-600">Seller: {{ $notification->data['seller_name'] }}</p>
                                            @elseif($notificationType === 'BundleStatusUpdated')
                                                <p class="font-semibold text-gray-900">Bundle Status Update</p>
                                                <p class="text-sm text-gray-600">Bundle: {{ $notification->data['bundle_name'] }}</p>
                                                <p class="text-sm text-gray-600">Status: {{ ucfirst($notification->data['action']) }}</p>
                                                @if(isset($notification->data['rejection_reason']))
                                                    <p class="text-sm text-gray-600">Reason: {{ $notification->data['rejection_reason'] }}</p>
                                                @endif
                                                @if(isset($notification->data['rejection_details']))
                                                    <p class="text-sm text-gray-600">Details: {{ $notification->data['rejection_details'] }}</p>
                                                @endif
                                            @else
                                                <p class="font-semibold text-gray-900">{{ $notification->data['message'] }}</p>
                                            @endif

                                            <!-- Action Links -->
                                            <div class="mt-2">
                                                @if($notificationType === 'NewOrderNotification')
                                                    <a href="{{ route('orders.show', $notification->data['order_id']) }}" 
                                                       class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        View Order Details →
                                                    </a>
                                                @elseif($notificationType === 'NewMessage')
                                                    <a href="{{ route('conversations.show', $notification->data['conversation_id']) }}" 
                                                       class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        View Conversation →
                                                    </a>
                                                @elseif(str_contains($notificationType, 'Bundle') && isset($notification->data['bundle_id']))
                                                    <a href="{{ route('bundles.show', $notification->data['bundle_id']) }}" 
                                                       class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        View Bundle →
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex flex-col items-end">
                                            <span class="text-sm text-gray-500">{{ $notification->created_at->format('M j, Y g:i A') }}</span>
                                            <span class="text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                                            
                                            @unless($notification->read_at)
                                                <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}" class="mt-2">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                                                        Mark as read
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <p class="mt-4 text-gray-500">No notifications yet.</p>
                        </div>
                    @endforelse

                    @if(auth()->user()->notifications->count() > 0)
                        <div class="mt-6 text-right">
                            <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Mark all as read
                                </button>
                            </form>
                        </div>
                    @endif
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

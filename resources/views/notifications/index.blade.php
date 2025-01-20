<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Notifications</h2>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 focus:outline-none">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Mark all as read
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    @forelse(auth()->user()->notifications()->latest()->get() as $notification)
                        <div class="mb-4 p-4 border dark:border-gray-700 rounded-lg transition-all duration-200 transform hover:scale-[1.01] 
                            {{ $notification->read_at 
                                ? 'bg-white dark:bg-gray-800' 
                                : 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-100 dark:border-indigo-800' }}">
                            @php
                                $notificationType = class_basename($notification->type);
                            @endphp

                            <div class="flex items-start">
                                <!-- Icon based on notification type -->
                                <div class="flex-shrink-0">
                                    @if($notificationType === 'NewOrderNotification')
                                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-full">
                                            <svg class="h-6 w-6 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                    @elseif($notificationType === 'NewMessage')
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                                            <svg class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                            </svg>
                                        </div>
                                    @elseif(str_contains($notificationType, 'Bundle'))
                                        <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-full">
                                            <svg class="h-6 w-6 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-full">
                                            <svg class="h-6 w-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="ml-4 flex-1">
                                    <div class="flex justify-between items-start">
                                        <div class="space-y-1">
                                            @if($notificationType === 'NewOrderNotification')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">New Order Received</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Order #{{ $notification->data['order_id'] }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Amount: LKR {{ number_format($notification->data['amount'], 2) }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">From: {{ $notification->data['buyer_name'] }}</p>
                                                <a href="{{ route('orders.show', $notification->data['order_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    View Order Details
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            @elseif($notificationType === 'NewMessage')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">New Message from {{ $notification->data['sender_name'] }}</p>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 p-2 rounded">
                                                    "{{ $notification->data['content_preview'] }}"
                                                </p>
                                                <a href="{{ route('chat.show', $notification->data['conversation_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    View Conversation
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            @elseif($notificationType === 'BundleSubmittedForReview')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">New Bundle Submission</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Bundle: {{ $notification->data['bundle_name'] }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Seller: {{ $notification->data['seller_name'] }}</p>
                                                @if(auth()->user()->role === 'admin')
                                                    <a href="{{ route('admin.bundles.show', $notification->data['bundle_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        Review Bundle
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </a>
                                                @else
                                                    <a href="{{ route('seller.bundles.show', $notification->data['bundle_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        View Bundle
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </a>
                                                @endif
                                            @elseif($notificationType === 'BundleStatusUpdated')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Bundle Status Update</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Bundle: {{ $notification->data['bundle_name'] }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Action: {{ ucfirst($notification->data['action']) }}</p>
                                                @if($notification->data['action'] === 'rejected')
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">Reason: {{ $notification->data['rejection_reason'] }}</p>
                                                    @if(isset($notification->data['rejection_details']))
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">Details: {{ $notification->data['rejection_details'] }}</p>
                                                    @endif
                                                    <a href="{{ route('seller.bundles.edit', $notification->data['bundle_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        Edit Bundle
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </a>
                                                @else
                                                    <a href="{{ route('bundles.show', $notification->data['bundle_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        View Bundle
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </a>
                                                @endif
                                            @elseif($notificationType === 'ProductSubmittedForReview')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Product Submitted for Review</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Product: {{ $notification->data['product_name'] }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Seller: {{ $notification->data['seller_name'] }}</p>
                                                @if(auth()->user()->role === 'admin')
                                                    <a href="{{ route('admin.products.show', $notification->data['product_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        Review Product
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </a>
                                                @else
                                                    <a href="{{ route('seller.products.show', $notification->data['product_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        View Product
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </a>
                                                @endif
                                            @elseif($notificationType === 'ProductApproved')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Product Approved</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Product: {{ $notification->data['product_name'] }}</p>
                                                <a href="{{ route('seller.products.index') }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    View Products
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            @elseif($notificationType === 'ProductRejected')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Product Rejected</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Product: {{ $notification->data['product_name'] }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Reason: {{ $notification->data['rejection_reason'] }}</p>
                                                <a href="{{ route('seller.products.edit', $notification->data['product_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    Edit Product
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            @elseif($notificationType === 'OrderStatusUpdated')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Order Status Update</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Order #{{ $notification->data['order_id'] }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Status: {{ $notification->data['status'] }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $notification->data['message'] }}</p>
                                                <a href="{{ route('orders.show', $notification->data['order_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    View Order
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            @elseif($notificationType === 'PaymentConfirmed')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Payment Confirmed</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Amount: LKR {{ number_format($notification->data['amount'], 2) }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Reference: {{ $notification->data['reference'] }}</p>
                                                <a href="{{ route('chat.show', $notification->data['conversation_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    View Details
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            @elseif($notificationType === 'PaymentRejected')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Payment Rejected</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Amount: LKR {{ number_format($notification->data['amount'], 2) }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Reason: {{ $notification->data['rejection_reason'] }}</p>
                                                <a href="{{ route('chat.show', $notification->data['conversation_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    View Details
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            @elseif($notificationType === 'PayoutStatusChanged')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Payout Status Update</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Amount: LKR {{ number_format($notification->data['amount'], 2) }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Status: {{ $notification->data['status'] }}</p>
                                                @if(auth()->user()->role === 'admin')
                                                    <a href="{{ route('admin.payouts.show', $notification->data['payout_id']) }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        View Payout
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </a>
                                                @else
                                                    <a href="{{ route('seller.earnings') }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                        View Details
                                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </a>
                                                @endif
                                            @elseif($notificationType === 'PayoutCompleted')
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">Payout Completed</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Amount: LKR {{ number_format($notification->data['amount'], 2) }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Reference: {{ $notification->data['reference'] }}</p>
                                                <a href="{{ route('seller.earnings') }}" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-900">
                                                    View Details
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                </a>
                                            @else
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $notification->data['message'] ?? '' }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                            @if(!$notification->read_at)
                                                <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No notifications yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">We'll notify you when something arrives!</p>
                        </div>
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

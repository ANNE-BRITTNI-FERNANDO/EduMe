<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ auth()->user()->unreadNotifications->count() }}
            </span>
        @endif
    </button>

    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50">
        
        <div class="py-2">
            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                Notifications
            </div>
            
            @forelse(auth()->user()->notifications()->latest()->take(5)->get() as $notification)
                <div class="px-4 py-3 hover:bg-gray-50 {{ $notification->read_at ? 'opacity-75' : '' }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @php
                                $notificationType = class_basename($notification->type);
                            @endphp
                            @if($notificationType === 'NewOrderNotification')
                                <svg class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            @elseif($notificationType === 'NewMessage')
                                <svg class="h-6 w-6 text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-3 w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900">
                                @if($notificationType === 'NewMessage')
                                    <p>New message from {{ $notification->data['sender_name'] }}</p>
                                    <p class="mt-1 text-sm text-gray-600 bg-gray-50 p-2 rounded">
                                        "{{ $notification->data['content_preview'] }}"
                                    </p>
                                    @if(isset($notification->data['product_id']))
                                        <p class="mt-1 text-xs text-gray-500">Re: Product Discussion</p>
                                    @elseif(isset($notification->data['bundle_id']))
                                        <p class="mt-1 text-xs text-gray-500">Re: Bundle Discussion</p>
                                    @endif
                                @elseif($notificationType === 'BundleSubmittedForReview')
                                    <p>New Bundle Submission</p>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Bundle: {{ $notification->data['bundle_name'] }}<br>
                                        Seller: {{ $notification->data['seller_name'] }}
                                    </p>
                                @elseif($notificationType === 'BundleStatusUpdated')
                                    <p>Bundle Status Update</p>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Bundle: {{ $notification->data['bundle_name'] }}<br>
                                        Status: {{ ucfirst($notification->data['action']) }}
                                        @if(isset($notification->data['rejection_reason']))
                                            <br>Reason: {{ $notification->data['rejection_reason'] }}
                                        @endif
                                        @if(isset($notification->data['rejection_details']))
                                            <br>Details: {{ $notification->data['rejection_details'] }}
                                        @endif
                                    </p>
                                @elseif($notificationType === 'NewOrderNotification')
                                    <p>New Order Received</p>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Order #{{ $notification->data['order_id'] }}<br>
                                        Amount: ₹{{ number_format($notification->data['amount'], 2) }}
                                    </p>
                                @else
                                    <p>{{ $notification->data['message'] }}</p>
                                @endif
                            </div>
                            <div class="mt-2 text-xs text-gray-500 flex items-center space-x-1">
                                <span>{{ $notification->created_at->format('M j, Y g:i A') }}</span>
                                <span class="text-gray-400">•</span>
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @unless($notification->read_at)
                            <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}" class="ml-3">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">
                                    Mark as read
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="px-4 py-3 text-sm text-gray-500">
                    No notifications
                </div>
            @endforelse
            
            @if(auth()->user()->notifications->count() > 5)
                <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-center text-gray-700 hover:bg-gray-50">
                    View all notifications
                </a>
            @endif
        </div>
    </div>
</div>

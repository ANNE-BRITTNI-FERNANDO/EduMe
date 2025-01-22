<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Conversations') }}
            </h2>
            @if(auth()->user()->is_seller)
                <a href="{{ route('seller.dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Seller Dashboard
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($conversations->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400 text-center py-8">
                            No conversations yet. Start chatting with sellers to see your conversations here!
                        </p>
                    @else
                        <div class="space-y-4">
                            @foreach($conversations as $conversation)
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <a href="{{ route('chat.show', ['conversation' => $conversation->id]) }}" class="block">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    @if(auth()->id() === $conversation->seller_id)
                                                        Chat with {{ $conversation->buyer->name }}
                                                    @else
                                                        Chat with {{ $conversation->seller->name }}
                                                    @endif
                                                </h3>
                                                
                                                @if($conversation->product)
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        Product: {{ $conversation->product->product_name }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        Price: ₹{{ number_format($conversation->product->price, 2) }}
                                                    </p>
                                                @endif
                                                @if($conversation->bundle)
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        Bundle: {{ $conversation->bundle->bundle_name }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        Price: ₹{{ number_format($conversation->bundle->price, 2) }}
                                                    </p>
                                                @endif
                                            </div>
                                            
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : 'No messages yet' }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

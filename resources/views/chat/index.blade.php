<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Conversations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($conversations->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400 text-center py-4">No conversations yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($conversations as $conversation)
                                <a href="{{ route('chat.show', $conversation) }}" 
                                   class="block p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                {{ auth()->id() === $conversation->seller_id ? $conversation->buyer->name : $conversation->seller->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $conversation->product ? 'Product' : 'Bundle' }}: 
                                                {{ $conversation->product ? $conversation->product->product_name : $conversation->bundle->bundle_name }}
                                            </p>
                                            @if($conversation->messages->isNotEmpty())
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                                    {{ Str::limit($conversation->messages->last()->content, 50) }}
                                                </p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    {{ $conversation->messages->last()->created_at->diffForHumans() }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Price: ${{ number_format($conversation->product ? $conversation->product->price : $conversation->bundle->price, 2) }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

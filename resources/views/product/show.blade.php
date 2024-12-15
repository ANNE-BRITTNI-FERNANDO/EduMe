@push('header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Explore More') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto bg-gray-800 text-white p-8 rounded-lg shadow-2xl">
                <!-- Full Image Section -->
                <div class="w-full h-96 bg-gray-200 dark:bg-gray-700 rounded-lg mb-8 overflow-hidden">
                    @if($product->image_path)
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="Product Image" class="h-full w-full object-cover">
                    @else
                        <span class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">No Image Available</span>
                    @endif
                </div>

                <!-- Product Details Section -->
                <div class="space-y-6">
                    <h4 class="text-3xl font-semibold text-gray-100">{{ $product->product_name }}</h4>
                    <p class="text-lg text-gray-300">{{ $product->description }}</p>
                    
                    <!-- Price & Category -->
                    <div class="flex items-center justify-between">
                        <p class="text-xl font-bold text-indigo-300">${{ number_format($product->price, 2) }}</p><br>
                        <p class="text-sm text-gray-400">Category: <span class="font-semibold text-indigo-400">{{ $product->category }}</span></p>
                    </div>

                    <!-- Created At -->
                    <p class="text-sm text-gray-400">Added on: <span class="font-semibold text-indigo-400">{{ $product->created_at->format('M d, Y') }}</span></p>
                </div>

                <div class="mt-8">
                    @auth
                        @if(auth()->id() !== $product->user_id)
                            <div class="space-x-4">
                                <a href="{{ route('cart.add.product', ['id' => $product->id]) }}" 
                                   class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 transition-colors duration-200 inline-flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Add to Cart
                                </a>
                                <form action="{{ route('chat-with-seller', ['id' => $product->id]) }}" method="GET" class="inline">
                                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">
                                        Chat with Seller
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="space-y-4">
                            <p class="text-gray-300">Please <a href="{{ route('login') }}" class="text-indigo-400 hover:underline">login</a> to chat with the seller or add items to your cart.</p>
                        </div>
                    @endauth
                </div>

                <!-- Back to Product List Link -->
                <div class="mt-8">
                    <a href="{{ route('productlisting') }}" class="text-sm text-indigo-300 hover:text-indigo-400 font-semibold">Back to Product List</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

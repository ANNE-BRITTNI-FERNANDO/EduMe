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
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-lg shadow-xl overflow-hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
                        <!-- Image Gallery Section -->
                        <div class="space-y-4">
                            <!-- Main Image -->
                            <div class="w-full h-96 bg-gray-100 rounded-lg overflow-hidden">
                                <img id="mainImage" src="{{ $product->image_url }}" 
                                     alt="{{ $product->product_name }}" 
                                     class="w-full h-full object-contain">
                            </div>

                            <!-- Thumbnail Gallery -->
                            <div class="grid grid-cols-4 gap-4">
                                @if($product->image_path)
                                    <div class="w-24 h-24 rounded-lg overflow-hidden cursor-pointer border-2 border-indigo-500">
                                        <img src="{{ asset('storage/' . $product->image_path) }}" 
                                             alt="Main Product Image"
                                             onclick="updateMainImage(this.src)"
                                             class="w-full h-full object-cover">
                                    </div>
                                @endif
                                @foreach($product->images as $image)
                                    <div class="w-24 h-24 rounded-lg overflow-hidden cursor-pointer {{ $image->is_primary ? 'border-2 border-indigo-500' : 'border border-gray-200' }}">
                                        <img src="{{ $image->image_url }}" 
                                             alt="Product Image {{ $loop->iteration }}"
                                             onclick="updateMainImage(this.src)"
                                             class="w-full h-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Product Details Section -->
                        <div class="space-y-6">
                            <div class="border-b pb-4">
                                <h1 class="text-3xl font-bold text-gray-900">{{ $product->product_name }}</h1>
                                <p class="text-2xl font-semibold text-indigo-600 mt-2">
                                    LKR {{ number_format($product->price, 2) }}
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Description</h3>
                                    <p class="mt-2 text-gray-600">{{ $product->description }}</p>
                                </div>

                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Category</h3>
                                    <p class="mt-1 text-indigo-600 font-medium">{{ $product->category }}</p>
                                </div>

                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Added on</h3>
                                    <p class="mt-1 text-gray-600">{{ $product->created_at->format('F d, Y') }}</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="border-t pt-6 mt-6">
                                @auth
                                    @if(auth()->id() !== $product->user_id)
                                        <div class="flex space-x-4">
                                            <a href="{{ route('cart.add.product', ['id' => $product->id]) }}" 
                                               class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg text-center font-medium hover:bg-indigo-700 transition-colors duration-200">
                                                <span class="flex items-center justify-center">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    Add to Cart
                                                </span>
                                            </a>
                                            <form action="{{ route('chat-with-seller', ['id' => $product->id]) }}" method="GET" class="flex-1">
                                                <button type="submit" 
                                                        class="w-full bg-gray-800 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-900 transition-colors duration-200">
                                                    Chat with Seller
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @else
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <p class="text-gray-600 text-center">
                                            Please <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-medium">login</a> 
                                            to chat with the seller or add items to your cart.
                                        </p>
                                    </div>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back to Product List Link -->
                <div class="mt-8 text-center">
                    <a href="{{ route('productlisting') }}" 
                       class="text-indigo-600 hover:text-indigo-700 font-medium inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Product List
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateMainImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
    @endpush
</x-app-layout>

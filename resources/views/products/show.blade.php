<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Product Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Product Images Section -->
                        <div class="space-y-4">
                            <!-- Main Image -->
                            <div class="aspect-w-1 aspect-h-1 w-full">
                                <img id="mainImage" src="{{ $product->productImages->count() > 0 ? Storage::url($product->productImages->first()->image_path) : asset('images/placeholder.jpg') }}" 
                                     class="w-full h-[400px] object-cover rounded-lg shadow-lg" 
                                     alt="{{ $product->product_name }}">
                            </div>
                            
                            <!-- Thumbnail Images -->
                            @if($product->productImages->count() > 0)
                                <div class="grid grid-cols-4 gap-4">
                                    @foreach($product->productImages as $image)
                                        <img src="{{ Storage::url($image->image_path) }}" 
                                             class="w-full h-24 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity duration-150"
                                             onclick="changeMainImage('{{ Storage::url($image->image_path) }}')"
                                             alt="{{ $product->product_name }}">
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Product Details Section -->
                        <div class="space-y-6">
                            <div class="flex justify-between items-start">
                                <div class="space-y-2">
                                    <h1 class="text-3xl font-bold">{{ $product->product_name }}</h1>
                                    <p class="text-gray-500 dark:text-gray-400">Category: {{ $product->category }}</p>
                                </div>
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    LKR {{ number_format($product->price, 2) }}
                                </div>
                            </div>

                            <!-- Seller Information with Rating -->
                            <div class="mt-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex items-start space-x-4">
                                    <!-- Seller Avatar -->
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                            {{ strtoupper(substr($product->user->name, 0, 1)) }}
                                        </div>
                                    </div>

                                    <!-- Seller Info and Rating -->
                                    <div class="flex-grow">
                                        <div class="flex flex-col">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Sold by</span>
                                            <span class="font-semibold text-lg">{{ $product->user->name }}</span>
                                            
                                            <!-- Rating Section -->
                                            <div class="mt-2">
                                                <div class="flex items-center space-x-2">
                                                    <div class="flex">
                                                        @php
                                                            $averageRating = $product->user->averageRating();
                                                            $totalRatings = $product->user->totalRatings();
                                                        @endphp
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $averageRating)
                                                                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            @else
                                                                <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                                        {{ number_format($averageRating, 1) }} ({{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }})
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <h2 class="text-xl font-semibold mb-2">Description</h2>
                                <p class="text-gray-600 dark:text-gray-300">{{ $product->description }}</p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-4">
                                @auth
                                    @if(auth()->user()->role === 'seller' && auth()->id() === $product->user_id)
                                        <a href="{{ route('seller.products.edit', $product->id) }}" 
                                           class="block w-full text-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                            Edit Product
                                        </a>
                                    @elseif(auth()->id() !== $product->user_id)
                                        <div class="grid grid-cols-2 gap-4">
                                            <form action="{{ route('cart.add', $product) }}" method="POST">
                                                @csrf
                                                <button type="submit" 
                                                   class="w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                                    Add to Cart
                                                </button>
                                            </form>
                                            <a href="{{ route('chat.start.product', ['id' => $product->id]) }}" 
                                               class="block text-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                                                Chat with Seller
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" 
                                       class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                        Login to Purchase
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function changeMainImage(imagePath) {
            document.getElementById('mainImage').src = imagePath;
        }
    </script>
    @endpush
</x-app-layout>

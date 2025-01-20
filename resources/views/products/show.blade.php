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
                                <div>
                                    <h1 class="text-3xl font-bold">{{ $product->product_name }}</h1>
                                    <p class="text-gray-500 dark:text-gray-400">Category: {{ $product->category }}</p>
                                </div>
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    LKR {{ number_format($product->price, 2) }}
                                </div>
                            </div>

                            <!-- Seller Information -->
                            <div class="flex items-center space-x-4 border-t border-b border-gray-200 dark:border-gray-700 py-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                        {{ strtoupper(substr($product->user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Sold by</p>
                                    <p class="font-semibold">{{ $product->user->name }}</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
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

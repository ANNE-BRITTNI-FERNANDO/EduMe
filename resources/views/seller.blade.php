<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add a New Product') }}
        </h2>
    </x-slot>

    <div class="h-screen flex items-center justify-center p-6" 
         style="background-image: url('/images/ll.jpeg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="flex w-full max-w-7xl space-x-16">
            <!-- Left Side: Product Form -->
            <div class="bg-gradient-to-b from-gray-800 via-gray-900 to-gray-800 p-6 rounded-3xl shadow-xl w-3/4 md:w-1/3 transform transition-transform duration-300 hover:scale-105">
                <!-- Success Message -->
                @if (session('success'))
                    <div class="mb-4 text-green-500 font-semibold text-center">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Product Form -->
                <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <!-- Product Name -->
                    <div class="mb-4">
                        <label for="product_name" class="block text-gray-300 font-semibold text-lg mb-2">Product Name</label>
                        <input type="text" name="product_name" id="product_name" value="{{ old('product_name') }}" class="w-full p-3 rounded-lg text-gray-200 bg-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        @error('product_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-gray-300 font-semibold text-lg mb-2">Description</label>
                        <textarea name="description" id="description" class="w-full p-3 rounded-lg text-gray-200 bg-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Price -->
                    <div class="mb-4">
                        <label for="price" class="block text-gray-300 font-semibold text-lg mb-2">Price (LKR)</label>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" class="w-full p-3 rounded-lg text-gray-200 bg-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" step="0.01" required>
                        @error('price')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label for="category" class="block text-gray-300 font-semibold text-lg mb-2">Category</label>
                        <select name="category" id="category" class="w-full p-3 rounded-lg text-gray-200 bg-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                            <option value="electronics">Electronics</option>
                            <option value="furniture">Furniture</option>
                            <option value="clothing">Clothing</option>
                            <option value="books">Books</option>
                            <option value="toys">Toys</option>
                        </select>
                    </div>

                    <!-- Product Image -->
                    <div class="mb-4">
                        <label for="image" class="block text-gray-300 font-semibold text-lg mb-2">Product Image</label>
                        <input type="file" name="image" id="image" class="w-full p-3 rounded-lg text-gray-200 bg-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        @error('image')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hidden seller_id -->
                    <input type="hidden" name="seller_id" value="{{ auth()->user()->id }}">

                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 transition duration-300">
                        Add Product
                    </button>
                </form>
            </div>

            <!-- Right Side: Question and Product Table -->
            <div class="w-full lg:w-1/2">
                <div class="text-center mb-4 bg-gradient-to-t from-gray-700 via-gray-800 to-gray-900 p-6 rounded-lg">
                    <h3 class="text-2xl font-semibold text-white mb-2">Do you have more products to add?</h3>
                    <p class="text-lg text-white">If you have more products to add, feel free to continue adding more items.</p>
                    <a href="{{ route('sell-bundle') }}" class="mt-4 px-6 py-2 bg-gray-800 text-white hover:bg-indigo-700 rounded-full opacity-60">
                        Sell as Bundle
                    </a>
                </div>
                <!-- Product List -->
                <div class="mt-8">
                    <h3 class="text-xl font-semibold text-gray-300 mb-4">Your Approved Products</h3>
                    @if($approvedProducts->isEmpty())
                        <p class="text-gray-400">No approved products yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-gray-700 rounded-lg overflow-hidden">
                                <thead class="bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-600">
                                    @foreach($approvedProducts as $product)
                                        <tr class="hover:bg-gray-600 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-300">
                                                {{ $product->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-300">
                                                LKR {{ number_format($product->price, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="#" class="text-indigo-400 hover:text-indigo-300">Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

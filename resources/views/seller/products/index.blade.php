<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold">My Products</h2>
                        <a href="{{ route('seller.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Add New Product
                        </a>
                    </div>

                    @if($products->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500">You haven't added any products yet.</p>
                            <a href="{{ route('seller.products.create') }}" class="text-indigo-600 hover:text-indigo-800 mt-2 inline-block">
                                Get started by adding your first product
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($products as $product)
                                <div class="border rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover rounded-md mb-4">
                                    @else
                                        <div class="w-full h-48 bg-gray-200 rounded-md mb-4 flex items-center justify-center">
                                            <span class="text-gray-400">No Image</span>
                                        </div>
                                    @endif
                                    
                                    <h3 class="text-lg font-semibold mb-2">{{ $product->name }}</h3>
                                    <p class="text-gray-600 mb-2">{{ Str::limit($product->description, 100) }}</p>
                                    <p class="text-indigo-600 font-semibold mb-4">Rs. {{ number_format($product->price, 2) }}</p>
                                    
                                    <div class="flex justify-between items-center">
                                        <a href="{{ route('seller.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-800">
                                            Edit
                                        </a>
                                        <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this product?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

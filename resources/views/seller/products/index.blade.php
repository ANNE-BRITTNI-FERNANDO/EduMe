<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-semibold text-gray-900">My Products</h1>
                        <a href="{{ route('seller.products.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Add New Product
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @forelse($products as $product)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                <div class="aspect-w-16 aspect-h-9">
                                    @if($product->image_path)
                                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->product_name }}" 
                                             class="w-full h-48 object-cover">
                                    @else
                                        <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $product->product_name }}</h3>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $product->description }}</p>
                                    
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="text-lg font-bold text-gray-900">LKR {{ number_format($product->price, 2) }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($product->is_approved) bg-green-100 text-green-800
                                            @elseif($product->is_rejected) bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $product->is_approved ? 'Approved' : ($product->is_rejected ? 'Rejected' : 'Pending') }}
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <div class="space-x-2">
                                            <a href="{{ route('seller.products.edit', $product->id) }}" 
                                               class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Edit
                                            </a>
                                            <form action="{{ route('seller.products.destroy', $product->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Are you sure you want to delete this product?')"
                                                        class="inline-flex items-center px-3 py-1 border border-transparent rounded-md text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full">
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new product.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('seller.products.create') }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                            Add New Product
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

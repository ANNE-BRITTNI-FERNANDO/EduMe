<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h1 class="text-2xl font-semibold text-gray-900">My Products</h1>
                        </div>
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('seller.dashboard') }}" class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Dashboard
                            </a>
                            <a href="{{ route('seller.products.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Add New Product
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-4 mb-6">
                        <div class="bg-white p-4 rounded shadow">
                            <div class="text-gray-500 text-sm">Total</div>
                            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                        </div>
                        <div class="bg-white p-4 rounded shadow">
                            <div class="text-gray-500 text-sm">Pending</div>
                            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="bg-white p-4 rounded shadow">
                            <div class="text-gray-500 text-sm">Approved</div>
                            <div class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                        </div>
                        <div class="bg-white p-4 rounded shadow">
                            <div class="text-gray-500 text-sm">Rejected</div>
                            <div class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded shadow mb-6">
                        <form method="GET" class="flex gap-4">
                            <select name="status" class="rounded border-gray-300" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="rounded border-gray-300" onchange="this.form.submit()">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="rounded border-gray-300" onchange="this.form.submit()">
                            @if(request('status') || request('date_from') || request('date_to'))
                                <a href="{{ route('seller.products.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded">Clear</a>
                            @endif
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($products as $product)
                            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                                <!-- Image Gallery -->
                                <div class="relative">
                                    <!-- Main Image -->
                                    <div class="aspect-w-16 aspect-h-9">
                                        @if($product->images->where('is_primary', true)->first())
                                            <img src="{{ asset('storage/' . $product->images->where('is_primary', true)->first()->image_path) }}" 
                                                 alt="{{ $product->product_name }}" 
                                                 class="w-full h-48 object-cover"
                                                 id="mainImage-{{ $product->id }}">
                                        @elseif($product->image_path)
                                            <img src="{{ asset('storage/' . $product->image_path) }}" 
                                                 alt="{{ $product->product_name }}" 
                                                 class="w-full h-48 object-cover"
                                                 id="mainImage-{{ $product->id }}">
                                        @else
                                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Thumbnail Gallery -->
                                    @if($product->images->isNotEmpty() || $product->image_path)
                                        <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/50 to-transparent">
                                            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-transparent">
                                                @if($product->image_path)
                                                    <img src="{{ asset('storage/' . $product->image_path) }}" 
                                                         alt="Main image"
                                                         class="w-12 h-12 object-cover rounded cursor-pointer border-2 border-white"
                                                         onclick="updateProductImage('{{ $product->id }}', '{{ asset('storage/' . $product->image_path) }}')">
                                                @endif
                                                @foreach($product->images->sortBy('sort_order') as $image)
                                                    <img src="{{ asset('storage/' . $image->image_path) }}" 
                                                         alt="Product image"
                                                         class="w-12 h-12 object-cover rounded cursor-pointer border-2 {{ $image->is_primary ? 'border-indigo-500' : 'border-white' }}"
                                                         onclick="updateProductImage('{{ $product->id }}', '{{ asset('storage/' . $image->image_path) }}')">
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $product->product_name }}</h3>
                                        <div class="text-right">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $product->is_approved ? 'bg-green-100 text-green-800' : 
                                                   ($product->is_rejected ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ $product->is_approved ? 'Approved' : 
                                                   ($product->is_rejected ? 'Rejected' : 'Pending') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col space-y-4">
                                        <p class="text-gray-500 text-sm">{{ Str::limit($product->description, 100) }}</p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-lg font-bold text-gray-900">LKR {{ number_format($product->price, 2) }}</span>
                                        </div>
                                        <div class="flex justify-end items-center space-x-2">
                                            @if($product->is_rejected)
                                                <a href="{{ route('seller.products.resubmit.form', $product->id) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Resubmit
                                                </a>
                                            @endif
                                            <a href="{{ route('seller.products.edit', $product->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-700">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('seller.products.destroy', $product->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.');"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @if($product->is_rejected && $product->rejection_reason)
                                        <div class="mt-2 p-2 bg-red-50 text-red-700 text-sm rounded">
                                            <strong>Rejection Reason:</strong> {{ $product->rejection_reason }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-8">
                                <p class="text-gray-500">No products found.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $products->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    function updateProductImage(productId, imageUrl) {
        const mainImage = document.getElementById(`mainImage-${productId}`);
        if (mainImage) {
            mainImage.src = imageUrl;
        }
    }
</script>
@endpush

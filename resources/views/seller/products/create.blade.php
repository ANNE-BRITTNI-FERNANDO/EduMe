<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Products') }}
            </h2>
            <a href="{{ route('seller.products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('View All Products') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Product Creation Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Product</h3>
                        <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" data-test="product-form">
                            @csrf

                            <!-- Product Name -->
                            <div>
                                <x-input-label for="product_name" :value="__('Product Name')" />
                                <x-text-input id="product_name" name="product_name" type="text" class="mt-1 block w-full" :value="old('product_name')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('product_name')" />
                            </div>

                            <!-- Description -->
                            <div>
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('description') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>

                            <!-- Price -->
                            <div>
                                <x-input-label for="price" :value="__('Price (LKR)')" />
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">LKR</span>
                                    </div>
                                    <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="pl-16 block w-full" :value="old('price')" required />
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('price')" />
                            </div>

                            <!-- Category -->
                            <div>
                                <x-input-label for="category" :value="__('Category')" />
                                <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select a category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('category')" />
                            </div>

                            <!-- Main Image -->
                            <div>
                                <x-input-label for="image" :value="__('Main Product Image')" />
                                <div class="mt-1 flex items-center">
                                    <input type="file" id="image" name="image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                <x-input-error class="mt-2" :messages="$errors->get('image')" />
                            </div>

                            <!-- Additional Images -->
                            <div>
                                <x-input-label for="additional_images" :value="__('Additional Images (Optional)')" />
                                <div class="mt-1 flex items-center">
                                    <input type="file" id="additional_images" name="additional_images[]" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" multiple />
                                </div>
                                <p class="mt-1 text-sm text-gray-500">You can select multiple images. PNG, JPG, GIF up to 2MB each</p>
                                <x-input-error class="mt-2" :messages="$errors->get('additional_images.*')" />
                            </div>

                            <!-- Preview Container -->
                            <div id="imagePreviewContainer" class="hidden mt-4 space-y-4">
                                <h4 class="font-medium text-gray-900">Image Previews</h4>
                                <div id="mainImagePreview" class="mt-2"></div>
                                <div id="additionalImagesPreview" class="grid grid-cols-2 gap-4"></div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <x-primary-button>
                                    {{ __('Create Product') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recent Products List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Products</h3>
                        <div class="space-y-4">
                            @forelse($products as $product)
                                <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 w-24 h-24">
                                        @if($product->image_path)
                                            <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->product_name }}" 
                                                 class="w-full h-full object-cover rounded-lg">
                                        @else
                                            <div class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $product->product_name }}</h4>
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($product->is_approved) bg-green-100 text-green-800
                                                @elseif($product->is_rejected) bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ $product->is_approved ? 'Approved' : ($product->is_rejected ? 'Rejected' : 'Pending') }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $product->description }}</p>
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900">LKR {{ number_format($product->price, 2) }}</span>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('seller.products.edit', $product->id) }}" 
                                                   class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Edit
                                                </a>
                                                <a href="{{ route('seller.products.index') }}" 
                                                   class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    View All
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-sm text-gray-500">No products yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainImageInput = document.getElementById('image');
            const additionalImagesInput = document.getElementById('additional_images');
            const mainImagePreview = document.getElementById('mainImagePreview');
            const additionalImagesPreview = document.getElementById('additionalImagesPreview');
            const previewContainer = document.getElementById('imagePreviewContainer');

            // Function to create image preview
            function createImagePreview(file, container, isMain = false) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = isMain ? 'relative w-full h-48' : 'relative w-full aspect-square';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-full object-contain rounded-lg border border-gray-200';
                    
                    div.appendChild(img);
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            }

            // Handle main image preview
            mainImageInput.addEventListener('change', function(e) {
                mainImagePreview.innerHTML = '';
                if (this.files && this.files[0]) {
                    previewContainer.classList.remove('hidden');
                    createImagePreview(this.files[0], mainImagePreview, true);
                }
            });

            // Handle additional images preview
            additionalImagesInput.addEventListener('change', function(e) {
                additionalImagesPreview.innerHTML = '';
                if (this.files.length > 0) {
                    previewContainer.classList.remove('hidden');
                    Array.from(this.files).forEach(file => {
                        createImagePreview(file, additionalImagesPreview);
                    });
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-semibold mb-6">Edit Product</h2>

                    <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="product_name" class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" name="product_name" id="product_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required value="{{ old('product_name', $product->product_name) }}">
                            @error('product_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="price" class="block text-sm font-medium text-gray-700">Price (LKR)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">LKR</span>
                                </div>
                                <input type="number" name="price" id="price" step="0.01" class="pl-16 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required value="{{ old('price', $product->price) }}">
                            </div>
                            @error('price')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category" id="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select a category</option>
                                <option value="Textbooks & Reference Books" {{ old('category', $product->category) == 'Textbooks & Reference Books' ? 'selected' : '' }}>Textbooks & Reference Books</option>
                                <option value="Literature & Story Books" {{ old('category', $product->category) == 'Literature & Story Books' ? 'selected' : '' }}>Literature & Story Books</option>
                                <option value="Study Materials & Notes" {{ old('category', $product->category) == 'Study Materials & Notes' ? 'selected' : '' }}>Study Materials & Notes</option>
                                <option value="School Bags & Supplies" {{ old('category', $product->category) == 'School Bags & Supplies' ? 'selected' : '' }}>School Bags & Supplies</option>
                                <option value="Educational Technology" {{ old('category', $product->category) == 'Educational Technology' ? 'selected' : '' }}>Educational Technology</option>
                            </select>
                            @error('category')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Main Image -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Main Product Image</label>
                            @if($product->image_path)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->product_name }}" class="w-48 h-48 object-cover rounded-lg">
                                </div>
                            @endif
                            <input type="file" name="image" id="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*">
                            <p class="text-sm text-gray-500 mt-1">Leave empty to keep the current main image</p>
                            @error('image')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Additional Images -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Additional Images</label>
                            
                            <!-- Existing Additional Images -->
                            @if($product->images->isNotEmpty())
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                    @foreach($product->images as $image)
                                        <div class="relative group">
                                            <img src="{{ Storage::url($image->image_path) }}" 
                                                 alt="Additional image" 
                                                 class="w-full h-32 object-cover rounded-lg">
                                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg flex items-center justify-center">
                                                <button type="button" 
                                                        onclick="deleteImage({{ $image->id }})"
                                                        class="text-white bg-red-600 px-3 py-1 rounded-md text-sm hover:bg-red-700">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Upload New Additional Images -->
                            <div>
                                <input type="file" name="additional_images[]" id="additional_images" 
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" 
                                       accept="image/*" multiple>
                                <p class="text-sm text-gray-500 mt-1">You can select multiple images. PNG, JPG, GIF up to 2MB each</p>
                            </div>
                            @error('additional_images.*')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preview Container for New Images -->
                        <div id="imagePreviewContainer" class="hidden mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">New Image Previews</h4>
                            <div id="mainImagePreview" class="mb-4"></div>
                            <div id="additionalImagesPreview" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('seller.products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-700 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Update Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function deleteImage(imageId) {
            if (confirm('Are you sure you want to remove this image?')) {
                fetch(`/seller/products/images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the image element from the DOM
                        const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                        if (imageElement) {
                            imageElement.remove();
                        }
                        // Optional: Show success message
                        alert('Image removed successfully');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to remove image');
                });
            }
        }

        // Image preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mainImageInput = document.getElementById('image');
            const additionalImagesInput = document.getElementById('additional_images');
            const mainImagePreview = document.getElementById('mainImagePreview');
            const additionalImagesPreview = document.getElementById('additionalImagesPreview');
            const previewContainer = document.getElementById('imagePreviewContainer');

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

            mainImageInput.addEventListener('change', function(e) {
                mainImagePreview.innerHTML = '';
                if (this.files && this.files[0]) {
                    previewContainer.classList.remove('hidden');
                    createImagePreview(this.files[0], mainImagePreview, true);
                }
            });

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
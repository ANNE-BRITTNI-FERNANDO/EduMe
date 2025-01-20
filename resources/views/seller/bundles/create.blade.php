<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Bundle') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1497864149936-d3163f0c0f4b?q=80&w=1920&auto=format&fit=crop'); background-attachment: fixed;">
        <div class="py-12 bg-gradient-to-b from-gray-900/70 to-gray-900/90">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-white">Create New Bundle</h2>
                    <a href="{{ route('seller.bundles.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-medium rounded-lg backdrop-blur-sm transition-colors duration-150 ease-in-out border border-white/20">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Back to Bundles</span>
                    </a>
                </div>

                <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl rounded-2xl overflow-hidden">
                    <div class="p-8">
                        <form method="POST" action="{{ route('seller.bundles.store') }}" enctype="multipart/form-data" class="space-y-8">
                            @csrf

                            <!-- Bundle Name -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                                        Bundle Name
                                    </label>
                                    <input type="text" name="bundleName" 
                                        class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                                        placeholder="Enter a catchy name for your bundle"
                                        value="{{ old('bundleName') }}"
                                        required />
                                    @error('bundleName')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                                        Description
                                    </label>
                                    <textarea name="description" rows="4" 
                                        class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                                        placeholder="Describe what makes your bundle special and what students will learn"
                                        required>{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Price -->
                                <div>
                                    <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                                        Price
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                        <input type="number" name="price" step="0.01" min="0"
                                            class="block w-full pl-8 px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                                            placeholder="0.00"
                                            value="{{ old('price') }}"
                                            required />
                                    </div>
                                    @error('price')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Bundle Image -->
                                <div>
                                    <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                                        Bundle Image
                                    </label>
                                    <div class="mt-1 flex items-center">
                                        <input type="file" name="bundleImage" accept="image/*" 
                                            class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                                            required />
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">Upload a high-quality image that represents your bundle. Supported formats: JPEG, PNG, JPG, GIF (max 2MB)</p>
                                    @error('bundleImage')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Bundle Products -->
                            <div class="bg-white/50 dark:bg-gray-700/50 rounded-xl p-6 backdrop-blur-sm">
                                <div class="mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        Bundle Items
                                    </h3>
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-100/50 dark:bg-gray-700/50 p-3 rounded-lg">
                                        Add at least 2 items to your bundle. Each item should have a name and thumbnail image.
                                    </p>
                                </div>

                                <div id="products-container" class="space-y-6">
                                    <!-- First Product (Always Present) -->
                                    <div class="product-item border-b border-gray-200 dark:border-gray-600 pb-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Item 1</h4>
                                        </div>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                                                    Item Name
                                                </label>
                                                <input type="text" name="categories[]" 
                                                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                                                    placeholder="e.g., Complete Web Development Course"
                                                    required />
                                            </div>
                                            <div>
                                                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                                                    Item Thumbnail
                                                </label>
                                                <input type="file" name="categoryImages[]" accept="image/*" 
                                                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                                                    required />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <button type="button" id="add-product"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-150 ease-in-out">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Add Another Item
                                    </button>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6">
                                <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-base font-medium rounded-xl transition-all duration-150 ease-in-out transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Create Bundle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('add-product').addEventListener('click', function() {
            const container = document.getElementById('products-container');
            const itemCount = container.getElementsByClassName('product-item').length + 1;
            
            if (itemCount <= 5) {
                const newItem = `
                    <div class="product-item border-b border-gray-200 dark:border-gray-600 pb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">Item ${itemCount}</h4>
                            ${itemCount > 2 ? `
                            <button type="button" onclick="removeItem(this)" 
                                class="inline-flex items-center px-3 py-1 text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-150">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove
                            </button>` : ''}
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                                    Item Name
                                </label>
                                <input type="text" name="categories[]" 
                                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                                    placeholder="e.g., Complete Web Development Course"
                                    required />
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                                    Item Thumbnail
                                </label>
                                <input type="file" name="categoryImages[]" accept="image/*" 
                                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                                    required />
                            </div>
                        </div>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', newItem);
                
                if (itemCount === 5) {
                    this.disabled = true;
                    this.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        });

        // Add second item by default
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('add-product').click();
        });

        function removeItem(button) {
            const container = document.getElementById('products-container');
            button.closest('.product-item').remove();
            
            // Re-enable add button if we're below 5 items
            const addButton = document.getElementById('add-product');
            if (container.getElementsByClassName('product-item').length < 5) {
                addButton.disabled = false;
                addButton.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            // Renumber remaining items
            const items = container.getElementsByClassName('product-item');
            for (let i = 0; i < items.length; i++) {
                items[i].querySelector('h4').textContent = `Item ${i + 1}`;
            }

            // Validate minimum items
            if (items.length < 2) {
                document.getElementById('add-product').click();
            }
        }
    </script>
    @endpush
</x-app-layout>
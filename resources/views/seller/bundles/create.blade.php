<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Create Bundle Offer') }}
            </h2>
            <a href="{{ route('seller.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-150 ease-in-out flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('seller.bundles.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Bundle Name -->
                        <div>
                            <x-input-label for="bundleName" :value="__('Bundle Name')" />
                            <x-text-input id="bundleName" name="bundleName" type="text" class="mt-1 block w-full" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('bundleName')" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <!-- Price -->
                        <div>
                            <x-input-label for="price" :value="__('Price')" />
                            <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('price')" />
                        </div>

                        <!-- Bundle Image -->
                        <div>
                            <x-input-label for="bundleImage" :value="__('Bundle Image')" />
                            <input id="bundleImage" name="bundleImage" type="file" accept="image/*" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('bundleImage')" />
                        </div>

                        <!-- Categories Section -->
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 p-3 rounded">
                                <strong>Note:</strong> You must add between 2 and 5 categories for your bundle. This helps create meaningful bundles for buyers.
                            </p>
                        </div>
                        <div id="categories-container" class="space-y-4">
                            <div class="category-entry border-b pb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-lg font-medium">Category 1</h3>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label :value="__('Category Name')" />
                                        <x-text-input name="categories[]" type="text" class="mt-1 block w-full" required />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Category Image')" />
                                        <input name="categoryImages[]" type="file" accept="image/*" class="mt-1 block w-full" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Category Button -->
                        <div>
                            <button type="button" id="add-category" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Add Another Category
                            </button>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Create Bundle') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoriesContainer = document.getElementById('categories-container');
            const addCategoryButton = document.getElementById('add-category');
            let categoryCount = 1;

            addCategoryButton.addEventListener('click', function() {
                categoryCount++;
                if (categoryCount >= 5) {
                    addCategoryButton.style.display = 'none';
                }

                const newCategory = document.createElement('div');
                newCategory.className = 'category-entry border-b pb-4';
                newCategory.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-medium">Category ${categoryCount}</h3>
                        ${categoryCount > 2 ? `<button type="button" class="text-red-600 hover:text-red-800" onclick="removeCategory(this)">Remove</button>` : ''}
                    </div>
                    <div class="space-y-4">
                        <div>
                            <x-input-label :value="__('Category Name')" />
                            <x-text-input name="categories[]" type="text" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label :value="__('Category Image')" />
                            <input name="categoryImages[]" type="file" accept="image/*" class="mt-1 block w-full" required />
                        </div>
                    </div>
                `;
                categoriesContainer.appendChild(newCategory);
            });

            window.removeCategory = function(button) {
                button.closest('.category-entry').remove();
                categoryCount--;
                if (categoryCount < 5) {
                    addCategoryButton.style.display = 'inline-flex';
                }
            };

            // Add a second category by default since minimum is 2
            addCategoryButton.click();
        });
    </script>
    @endpush
</x-app-layout>

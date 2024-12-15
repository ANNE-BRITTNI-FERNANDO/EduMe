<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Bundle') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($bundle->status === 'rejected')
                        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Bundle Rejected</strong>
                            <p class="mt-2">Reason: {{ $bundle->rejection_reason }}</p>
                            @if($bundle->rejection_details)
                                <p class="mt-1">Details: {{ $bundle->rejection_details }}</p>
                            @endif
                        </div>
                    @endif

                    <form method="POST" action="{{ route('seller.bundles.update', $bundle) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Bundle Name -->
                        <div>
                            <x-input-label for="bundle_name" :value="__('Bundle Name')" />
                            <x-text-input id="bundle_name" name="bundle_name" type="text" class="mt-1 block w-full" 
                                        :value="old('bundle_name', $bundle->bundle_name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('bundle_name')" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" 
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500" 
                                    required>{{ old('description', $bundle->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <!-- Price -->
                        <div>
                            <x-input-label for="price" :value="__('Price')" />
                            <x-text-input id="price" name="price" type="number" step="0.01" class="mt-1 block w-full" 
                                        :value="old('price', $bundle->price)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('price')" />
                        </div>

                        <!-- Bundle Image -->
                        <div>
                            <x-input-label for="bundle_image" :value="__('Bundle Image')" />
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $bundle->bundle_image) }}" alt="Current Bundle Image" class="w-32 h-32 object-cover rounded-lg">
                            </div>
                            <input type="file" id="bundle_image" name="bundle_image" 
                                   class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <x-input-error class="mt-2" :messages="$errors->get('bundle_image')" />
                        </div>

                        <!-- Categories -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium">Bundle Categories</h3>
                            @foreach($bundle->categories as $category)
                                <div class="border dark:border-gray-700 rounded-lg p-4">
                                    @if($category->status === 'rejected')
                                        <div class="mb-4 text-red-600 dark:text-red-400">
                                            <p>Rejected: {{ $category->rejection_reason }}</p>
                                            @if($category->rejection_details)
                                                <p class="mt-1 text-sm">{{ $category->rejection_details }}</p>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="space-y-4">
                                        <div>
                                            <x-input-label :for="'categories['.$category->id.'][category]'" :value="__('Category Name')" />
                                            <x-text-input :id="'categories['.$category->id.'][category]'" 
                                                        :name="'categories['.$category->id.'][category]'" 
                                                        type="text" 
                                                        class="mt-1 block w-full" 
                                                        :value="old('categories.'.$category->id.'.category', $category->category)" 
                                                        required />
                                        </div>

                                        <div>
                                            <x-input-label :for="'categories['.$category->id.'][image]'" :value="__('Category Image')" />
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $category->category_image) }}" 
                                                     alt="Current Category Image" 
                                                     class="w-32 h-32 object-cover rounded-lg">
                                            </div>
                                            <input type="file" 
                                                   :id="'categories['.$category->id.'][image]'" 
                                                   :name="'categories['.$category->id.'][image]'" 
                                                   class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Bundle') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

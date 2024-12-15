<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Bundles') }}
            </h2>
            <a href="{{ route('seller.bundles.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">
                Create New Bundle
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="space-y-6">
                @forelse ($bundles as $bundle)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <img src="{{ asset('storage/' . $bundle->bundle_image) }}" 
                                         alt="{{ $bundle->bundle_name }}" 
                                         class="w-20 h-20 object-cover rounded-lg">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $bundle->bundle_name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Price: LKR {{ number_format($bundle->price, 2) }}
                                        </p>
                                        <div class="mt-2">
                                            @switch($bundle->status)
                                                @case('approved')
                                                    <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-800">
                                                        Approved
                                                    </span>
                                                    @break
                                                @case('rejected')
                                                    <span class="px-2 py-1 text-sm rounded-full bg-red-100 text-red-800">
                                                        Rejected
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending Review
                                                    </span>
                                            @endswitch
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <a href="{{ route('seller.bundles.edit', $bundle->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        Edit Bundle
                                    </a>
                                </div>
                            </div>

                            @if($bundle->status === 'rejected')
                                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                    <h4 class="font-medium text-red-800 dark:text-red-400">Rejection Details</h4>
                                    @if($bundle->rejection_reason)
                                        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                            Reason: {{ $bundle->rejection_reason }}
                                        </p>
                                    @endif
                                    @if($bundle->rejection_details)
                                        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                            Additional Details: {{ $bundle->rejection_details }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <!-- Categories -->
                            <div class="mt-4">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Categories</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($bundle->categories as $category)
                                        <div class="flex items-center space-x-3">
                                            <img src="{{ asset('storage/' . $category->category_image) }}" 
                                                 alt="{{ $category->category }}" 
                                                 class="w-12 h-12 object-cover rounded">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $category->category }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                            <p>You haven't created any bundles yet.</p>
                            <a href="{{ route('seller.bundles.create') }}" 
                               class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Create Your First Bundle
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

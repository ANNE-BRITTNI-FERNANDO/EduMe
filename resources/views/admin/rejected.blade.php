<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Rejected Products
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Back to Dashboard
                </a>
                <a href="{{ route('admin.approved') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    View Approved Products
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($products->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No rejected products found.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($products as $product)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden">
                                    <div class="relative h-48">
                                        @if($product->images->isNotEmpty())
                                            <img src="{{ Storage::url($product->images->first()->image_path) }}" 
                                                alt="{{ $product->product_name }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                <span class="text-gray-400">No image</span>
                                            </div>
                                        @endif
                                        <div class="absolute top-2 right-2">
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                Rejected
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                            {{ $product->product_name }}
                                        </h3>
                                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ $product->user->name }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $product->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                                Rs. {{ number_format($product->price, 2) }}
                                            </span>
                                            <form action="{{ route('admin.products.approve', $product->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                                                    Approve
                                                </button>
                                            </form>
                                        </div>
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

@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="mb-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold">{{ $product->product_name }}</h2>
                        <div class="flex space-x-4">
                            @if($product->status === 'rejected')
                                <a href="{{ route('seller.products.resubmit.form', $product->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Resubmit
                                </a>
                            @endif
                            <a href="{{ route('seller.products.edit', $product->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold">Product Images</h3>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($product->productImages as $image)
                                <div class="relative">
                                    <img src="{{ Storage::url($image->image_path) }}" alt="Product image" class="w-full h-48 object-cover rounded-lg">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold">Product Details</h3>
                            <div class="mt-2 space-y-2">
                                <p><span class="font-medium">Price:</span> LKR {{ number_format($product->price, 2) }}</p>
                                <p><span class="font-medium">Status:</span> 
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($product->status === 'approved') bg-green-100 text-green-800
                                        @elseif($product->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </p>
                                <p><span class="font-medium">Category:</span> {{ $product->category }}</p>
                                <p><span class="font-medium">Created:</span> {{ $product->created_at->format('M d, Y') }}</p>
                                <p><span class="font-medium">Last Updated:</span> {{ $product->updated_at->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold">Description</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $product->description }}</p>
                        </div>

                        @if($product->status === 'rejected')
                            <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">Rejection Details</h3>
                                <p class="mt-2 text-red-600 dark:text-red-400">{{ $product->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

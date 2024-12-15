@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-2xl font-semibold mb-6">Checkout</h2>

                <div class="space-y-6 mb-8">
                    @foreach($cartItems as $item)
                        <div class="flex items-center space-x-6 p-4 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 w-20 h-20">
                                @if($item->item_type === 'product' && $item->product)
                                    <img src="{{ asset('storage/' . $item->product->image) }}" 
                                         alt="{{ $item->product->product_name }}" 
                                         class="w-full h-full object-cover rounded-lg">
                                @elseif($item->item_type === 'bundle' && $item->bundle)
                                    <img src="{{ asset('storage/' . $item->bundle->bundle_image) }}" 
                                         alt="{{ $item->bundle->bundle_name }}" 
                                         class="w-full h-full object-cover rounded-lg">
                                @endif
                            </div>

                            <div class="flex-1">
                                <h3 class="text-lg font-medium">
                                    @if($item->item_type === 'product' && $item->product)
                                        {{ $item->product->product_name }}
                                    @elseif($item->item_type === 'bundle' && $item->bundle)
                                        {{ $item->bundle->bundle_name }}
                                    @endif
                                </h3>
                                <p class="text-gray-600">
                                    @if($item->item_type === 'product' && $item->product)
                                        ₹{{ number_format($item->product->price, 2) }}
                                    @elseif($item->item_type === 'bundle' && $item->bundle)
                                        ₹{{ number_format($item->bundle->price, 2) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t pt-6">
                    <div class="flex justify-between text-lg font-semibold mb-8">
                        <span>Total:</span>
                        <span>₹{{ number_format($total, 2) }}</span>
                    </div>

                    <form action="{{ route('orders.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="delivery_address" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                            <textarea id="delivery_address" name="delivery_address" rows="3" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>{{ old('delivery_address', auth()->user()->address) }}</textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Order Notes (Optional)</label>
                            <textarea id="notes" name="notes" rows="2" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

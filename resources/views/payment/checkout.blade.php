@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold mb-6">Checkout</h2>

                <!-- Cart Items Summary -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                    @foreach($cartItems as $item)
                        <div class="flex items-center justify-between py-4 border-b">
                            <div class="flex items-center">
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" 
                                     class="w-16 h-16 object-cover rounded-lg mr-4">
                                <div>
                                    <h4 class="font-medium">{{ $item->name }}</h4>
                                    <p class="text-sm text-gray-600">Seller: {{ $item->seller }}</p>
                                    <span class="text-xs px-2 py-1 bg-gray-100 rounded-full">
                                        {{ ucfirst($item->type) }}
                                    </span>
                                </div>
                            </div>
                            <p class="font-medium">₹{{ number_format($item->price, 2) }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- Price Breakdown -->
                <div class="bg-gray-50 p-6 rounded-lg mb-8">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">₹{{ number_format($cartItems->sum('price'), 2) }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Delivery Fee</span>
                        <span class="font-medium">₹{{ number_format($cartItems->sum('delivery_fee'), 2) }}</span>
                    </div>
                    <div class="border-t border-gray-200 mt-4 pt-4">
                        <div class="flex justify-between">
                            <span class="font-semibold">Total</span>
                            <span class="font-bold text-lg">₹{{ number_format($cartItems->sum('price') + $cartItems->sum('delivery_fee'), 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form action="{{ route('stripe.checkout') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="amount" value="{{ $cartItems->sum('price') + $cartItems->sum('delivery_fee') }}">
                    
                    <!-- Delivery Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                        <textarea name="address" id="address" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ auth()->user()->address }}</textarea>
                    </div>

                    <!-- Payment Button -->
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Pay ₹{{ number_format($cartItems->sum('price') + $cartItems->sum('delivery_fee'), 2) }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-semibold mb-6">Your Orders</h2>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @if($orders->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-500">No orders found.</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($orders as $order)
                            <div class="border rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold">
                                            {{ $order->item_type === 'product' ? $order->product->name : $order->bundle->name }}
                                        </h3>
                                        <p class="text-gray-600 mt-1">
                                            Order #{{ $order->id }}
                                        </p>
                                        <div class="mt-2 space-y-1">
                                            <p class="text-sm text-gray-600">
                                                Type: {{ ucfirst($order->item_type) }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                Payment Method: {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                Status: 
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $order->payment_status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($order->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-indigo-600">
                                            ${{ number_format($order->amount, 2) }}
                                        </p>
                                        @if($order->payment_method === 'bank_transfer' && $order->payment_status === 'pending')
                                            <div class="mt-2 text-sm text-gray-600">
                                                <p class="font-semibold">Bank Details:</p>
                                                <p>Bank: Example Bank</p>
                                                <p>Account: 1234-5678-9012</p>
                                                <p>Reference: ORD-{{ $order->id }}</p>
                                            </div>
                                        @endif
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
@endsection

@extends('layouts.seller')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Request Payout</h1>
            <p class="text-gray-600">Available Balance: ₹{{ number_format($sellerBalance->available_balance, 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('seller.payouts.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" name="amount" id="amount" min="100" step="0.01" max="{{ $sellerBalance->available_balance }}"
                            class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md"
                            placeholder="0.00" required>
                    </div>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                    <input type="text" name="bank_name" id="bank_name" 
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        required>
                    @error('bank_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                    <input type="text" name="account_number" id="account_number" 
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        required>
                    @error('account_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="account_holder_name" class="block text-sm font-medium text-gray-700">Account Holder Name</label>
                    <input type="text" name="account_holder_name" id="account_holder_name" 
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        required>
                    @error('account_holder_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('seller.payouts.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

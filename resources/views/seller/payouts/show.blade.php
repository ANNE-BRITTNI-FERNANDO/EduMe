@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Payout Request Details</h1>
            <p class="text-gray-600">Request ID: #{{ $payoutRequest->id }}</p>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h2 class="text-lg font-semibold mb-4">Request Information</h2>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $payoutRequest->status_badge }}-100 text-{{ $payoutRequest->status_badge }}-800">
                                        {{ ucfirst($payoutRequest->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                <dd class="mt-1 text-sm text-gray-900">₹{{ number_format($payoutRequest->amount, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Requested On</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $payoutRequest->created_at->format('M d, Y H:i A') }}</dd>
                            </div>
                            @if($payoutRequest->processed_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Processed On</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $payoutRequest->processed_at->format('M d, Y H:i A') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold mb-4">Bank Details</h2>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Bank Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $payoutRequest->bank_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $payoutRequest->account_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Account Holder</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $payoutRequest->account_holder_name }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if($payoutRequest->rejection_reason)
                <div class="mt-6 p-4 bg-red-50 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Rejection Reason</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>{{ $payoutRequest->rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($payoutRequest->notes)
                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-500">Additional Notes</h3>
                    <div class="mt-2 text-sm text-gray-900">
                        {{ $payoutRequest->notes }}
                    </div>
                </div>
                @endif

                @if($payoutRequest->status === 'approved')
                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-4">Upload Payment Receipt</h3>
                    <form action="{{ route('seller.payouts.upload-receipt', $payoutRequest) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="receipt" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="receipt" name="receipt" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, PNG, JPG up to 2MB</p>
                            </div>
                        </div>
                        @error('receipt')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-4">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                Upload Receipt
                            </button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <div class="mt-6 flex justify-between">
            <a href="{{ route('seller.payouts.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Back to Payouts
            </a>
            @if($payoutRequest->status === 'pending')
            <form action="{{ route('seller.payouts.cancel', $payoutRequest) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded" onclick="return confirm('Are you sure you want to cancel this request?')">
                    Cancel Request
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

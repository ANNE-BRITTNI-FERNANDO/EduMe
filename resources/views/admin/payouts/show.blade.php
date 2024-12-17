@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">Payout Request #{{ $payoutRequest->id }}</h2>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        @if($payoutRequest->status === 'completed') bg-green-100 text-green-800
                        @elseif($payoutRequest->status === 'pending') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($payoutRequest->status) }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Payout Details -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Payout Details</h3>
                        <dl class="grid grid-cols-1 gap-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                <dd class="mt-1 text-sm text-gray-900">LKR {{ number_format($payoutRequest->amount, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $payoutRequest->payment_method)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Details</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if(is_array($payoutRequest->payment_details) || is_object($payoutRequest->payment_details))
                                        @foreach($payoutRequest->payment_details as $key => $value)
                                            <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}<br>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Timestamps -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Timeline</h3>
                        <dl class="grid grid-cols-1 gap-3">
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
                </div>

                @if($payoutRequest->status === 'pending')
                <div class="mt-8 flex justify-end space-x-4">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.remove('hidden')" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Reject
                    </button>
                    <form action="{{ route('admin.payouts.approve', $payoutRequest) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            Approve
                        </button>
                    </form>
                </div>
                @endif

                @if($payoutRequest->status === 'rejected')
                <div class="mt-4 p-4 bg-red-50 rounded-md">
                    <h4 class="text-lg font-medium text-red-800 mb-2">Rejection Reason</h4>
                    <p class="text-sm text-red-700">{{ $payoutRequest->rejection_reason }}</p>
                </div>
                @endif

                @if($payoutRequest->status === 'approved' || $payoutRequest->status === 'completed')
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Upload Payment Receipt</h3>
                    <form action="{{ route('admin.payouts.upload-receipt', $payoutRequest) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label for="receipt" class="block text-sm font-medium text-gray-700">Receipt File</label>
                            <input type="file" name="receipt" id="receipt" accept=".pdf,.jpg,.jpeg,.png" 
                                class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100">
                            <p class="mt-1 text-sm text-gray-500">Upload PDF, JPG, JPEG, or PNG file (max 2MB)</p>
                        </div>
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Upload Receipt
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('admin.payouts.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Back to Payouts
            </a>
        </div>
    </div>
</div>
@endsection

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Payout Request Details</h2>
                        <a href="{{ route('seller.earnings.history') }}" class="text-indigo-600 hover:text-indigo-900">
                            ← Back to History
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">General Information</h3>
                            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                                <dl>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                        <dt class="text-sm font-medium text-gray-500">Request ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">#{{ $payoutRequest->id }}</dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">${{ number_format($payoutRequest->amount, 2) }}</dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($payoutRequest->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($payoutRequest->status === 'approved') bg-green-100 text-green-800
                                                @elseif($payoutRequest->status === 'completed') bg-blue-100 text-blue-800
                                                @else bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($payoutRequest->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ucfirst(str_replace('_', ' ', $payoutRequest->payment_method)) }}</dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                        <dt class="text-sm font-medium text-gray-500">Requested On</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payoutRequest->created_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payoutRequest->updated_at->format('M d, Y H:i') }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-4">Payment Details</h3>
                            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                                <dl>
                                    @if($payoutRequest->payment_method === 'bank_transfer')
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                            <dt class="text-sm font-medium text-gray-500">Bank Name</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payoutRequest->payment_details['bank_name'] }}</dd>
                                        </div>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payoutRequest->payment_details['account_number'] }}</dd>
                                        </div>
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                            <dt class="text-sm font-medium text-gray-500">Account Holder</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payoutRequest->payment_details['account_holder_name'] }}</dd>
                                        </div>
                                    @elseif($payoutRequest->payment_method === 'paypal')
                                        <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                                            <dt class="text-sm font-medium text-gray-500">PayPal Email</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $payoutRequest->payment_details['paypal_email'] }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>

                    @if($payoutRequest->status === 'rejected')
                        <div class="mt-6">
                            <div class="alert alert-danger">
                                <h5><i class="icon fas fa-ban"></i> Payout Request Rejected</h5>
                                <p>{{ $payoutRequest->rejection_reason ?? 'No reason provided.' }}</p>
                            </div>
                        </div>
                    @endif

                    @if($payoutRequest->status === 'completed')
                        <div class="mt-6">
                            <div class="alert alert-success">
                                <h5><i class="icon fas fa-check"></i> Payout Completed</h5>
                                <p>The payout has been processed and sent to your account.</p>
                                @if($payoutRequest->transaction_id)
                                <p>Transaction ID: {{ $payoutRequest->transaction_id }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

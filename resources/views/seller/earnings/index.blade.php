<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Earnings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Balance</h3>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">LKR {{ number_format($balance->available_balance, 2) }}</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Available for withdrawal</p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Recent Payout Requests</h3>
                        @if($payoutRequests->isEmpty())
                            <p class="text-gray-600 dark:text-gray-400">No payout requests found.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</span>
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</span>
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</span>
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payoutRequests as $payout)
                                            <tr>
                                                <td class="px-6 py-4">{{ $payout->created_at->format('M d, Y') }}</td>
                                                <td class="px-6 py-4">LKR {{ number_format($payout->amount, 2) }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($payout->status === 'completed') bg-green-100 text-green-800
                                                        @elseif($payout->status === 'pending') bg-yellow-100 text-yellow-800
                                                        @else bg-red-100 text-red-800 @endif">
                                                        {{ ucfirst($payout->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <button onclick="document.getElementById('toggle-form-{{ $payout->id }}').submit();" class="text-blue-600 hover:text-blue-800">
                                                        {{ session('expanded_payout_'.$payout->id) ? 'Hide Details' : 'View Details' }}
                                                    </button>
                                                    <form id="toggle-form-{{ $payout->id }}" method="POST" action="{{ route('seller.earnings.toggle-details', $payout->id) }}" class="hidden">
                                                        @csrf
                                                    </form>
                                                </td>
                                            </tr>
                                            @if(session('expanded_payout_'.$payout->id))
                                            <tr class="bg-gray-50">
                                                <td colspan="4" class="px-6 py-4">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div>
                                                            <h4 class="font-semibold mb-2">Bank Details</h4>
                                                            <p><span class="text-gray-600">Bank Name:</span> {{ $payout->bank_name }}</p>
                                                            <p><span class="text-gray-600">Account Number:</span> {{ $payout->account_number }}</p>
                                                            <p><span class="text-gray-600">Account Holder:</span> {{ $payout->account_holder_name }}</p>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold mb-2">Status Information</h4>
                                                            <p><span class="text-gray-600">Status:</span> {{ ucfirst($payout->status) }}</p>
                                                            <p><span class="text-gray-600">Requested:</span> {{ $payout->created_at->format('M d, Y') }}</p>
                                                            @if($payout->receipt_path)
                                                            <div class="mt-2">
                                                                <a href="{{ route('seller.earnings.download-receipt', $payout->id) }}" 
                                                                   class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                                                                    Download Receipt
                                                                </a>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Recent Transactions</h3>
                        @if($recentTransactions->isEmpty())
                            <p class="text-gray-600 dark:text-gray-400">No transactions found.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</span>
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order ID</span>
                                            </th>
                                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentTransactions as $transaction)
                                            <tr>
                                                <td class="px-6 py-4">{{ $transaction->created_at->format('M d, Y') }}</td>
                                                <td class="px-6 py-4">#{{ $transaction->id }}</td>
                                                <td class="px-6 py-4">LKR {{ number_format($transaction->total_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Request Payout</h3>
                        <form action="{{ route('seller.earnings.request-payout') }}" method="POST" class="max-w-md">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount (LKR)</label>
                                    <input type="number" name="amount" id="amount" step="0.01" min="1" 
                                           value="{{ old('amount') }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                    @error('amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Name</label>
                                    <input type="text" name="bank_name" id="bank_name" 
                                           value="{{ old('bank_name') }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                    @error('bank_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="account_holder_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" id="account_holder_name" 
                                           value="{{ old('account_holder_name') }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                    @error('account_holder_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account Number</label>
                                    <input type="text" name="account_number" id="account_number" 
                                           value="{{ old('account_number') }}" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                    @error('account_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <button type="submit" 
                                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Request Payout
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
@endpush

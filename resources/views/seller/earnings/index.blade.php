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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Earnings</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    LKR {{ number_format($balance->total_earned, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Balance</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    LKR {{ number_format($balance->available_balance, 2) }}
                                </div>
                                @if($pendingPayouts > 0)
                                    <div class="mt-1 text-sm text-red-600">
                                        (LKR {{ number_format($pendingPayouts, 2) }} in pending payouts)
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Available for Withdrawal</div>
                                <div class="mt-1 text-2xl font-semibold text-green-600">
                                    LKR {{ number_format(max($balance->available_balance, 0), 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($payouts->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rejection Reason
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payouts as $payout)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            LKR {{ number_format($payout->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($payout->status === 'completed') bg-green-100 text-green-800
                                                @elseif($payout->status === 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($payout->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payout->created_at->format('M d, Y H:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <button onclick="document.getElementById('toggle-form-{{ $payout->id }}').submit();" 
                                                    class="text-blue-600 hover:text-blue-800">
                                                {{ session('expanded_payout_'.$payout->id) ? 'Hide Details' : 'View Details' }}
                                            </button>
                                            <form id="toggle-form-{{ $payout->id }}" method="POST" action="{{ route('seller.earnings.toggle-details') }}" class="hidden">
                                                @csrf
                                                <input type="hidden" name="payout_id" value="{{ $payout->id }}">
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($payout->status === 'rejected')
                                                <span class="text-red-600">{{ $payout->rejection_reason }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @if(session('expanded_payout_'.$payout->id))
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 bg-gray-50">
                                                <div class="text-sm">
                                                    <p class="mb-2"><strong>Bank Name:</strong> {{ $payout->bank_name }}</p>
                                                    <p class="mb-2"><strong>Account Number:</strong> {{ $payout->account_number }}</p>
                                                    <p class="mb-2"><strong>Account Holder:</strong> {{ $payout->account_holder_name }}</p>
                                                    <p class="mb-2"><strong>Status:</strong> 
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                            @if($payout->status === 'completed') bg-green-100 text-green-800
                                                            @elseif($payout->status === 'pending') bg-yellow-100 text-yellow-800
                                                            @elseif($payout->status === 'rejected') bg-red-100 text-red-800
                                                            @else bg-gray-100 text-gray-800 @endif">
                                                            {{ ucfirst($payout->status) }}
                                                        </span>
                                                    </p>
                                                    <p class="mb-2"><strong>Requested On:</strong> {{ $payout->created_at->format('M d, Y H:i A') }}</p>
                                                    <p><strong>Amount:</strong> LKR {{ number_format($payout->amount, 2) }}</p>
                                                    @if($payout->receipt_path)
                                                        <div class="mt-3">
                                                            <a href="{{ route('seller.earnings.download-receipt', $payout->id) }}" 
                                                               class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500">
                                                                Download Receipt
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500">No payout requests found.</p>
                        </div>
                    @endif

                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Recent Transactions</h3>
                        @if($recentTransactions->isEmpty())
                            <p class="text-gray-600 dark:text-gray-400">No recent transactions found.</p>
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
                                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($recentTransactions as $transaction)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $transaction->created_at->format('M d, Y H:i A') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    #{{ $transaction->order_id }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    LKR {{ number_format($transaction->price * $transaction->quantity, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($transaction->order->delivery_status === 'completed') bg-green-100 text-green-800
                                                        @elseif($transaction->order->delivery_status === 'delivered') bg-green-100 text-green-800
                                                        @else bg-blue-100 text-blue-800 @endif">
                                                        {{ ucfirst($transaction->order->delivery_status) }}
                                                    </span>
                                                </td>
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

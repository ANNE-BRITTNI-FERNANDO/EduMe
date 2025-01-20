<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Earnings Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Earnings Card -->
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-indigo-100 text-sm font-medium">Total Earnings</p>
                                <p class="text-white text-2xl font-bold mt-2">
                                    LKR {{ number_format($balance->total_earned, 2) }}
                                </p>
                                <p class="text-indigo-100 text-xs mt-1">
                                    Total earnings from all orders
                                </p>
                            </div>
                            <div class="bg-indigo-400 bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance to be Paid Card -->
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-emerald-100 text-sm font-medium">Balance to be Paid</p>
                                <p class="text-white text-2xl font-bold mt-2">
                                    LKR {{ number_format($balance->balance_to_be_paid, 2) }}
                                </p>
                                @if($balance->pending_balance > 0)
                                    <p class="text-emerald-100 text-xs mt-1">
                                        (LKR {{ number_format($balance->pending_balance, 2) }} pending)
                                    </p>
                                @endif
                            </div>
                            <div class="bg-emerald-400 bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available for Withdrawal Card -->
                <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-violet-100 text-sm font-medium">Pending Balance</p>
                                <p class="text-white text-2xl font-bold mt-2">
                                    LKR {{ number_format($balance->pending_balance, 2) }}
                                </p>
                                <p class="text-violet-100 text-xs mt-1">
                                    Current pending payouts
                                </p>
                            </div>
                            <div class="bg-violet-400 bg-opacity-30 rounded-full p-3">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payouts History -->
            @if($payouts->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payout History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                            @foreach($payouts as $payout)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        LKR {{ number_format($payout->amount, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($payout->status === 'completed') bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100
                                        @elseif($payout->status === 'pending') bg-amber-100 text-amber-800 dark:bg-amber-800 dark:text-amber-100
                                        @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif">
                                        {{ ucfirst($payout->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $payout->created_at->format('M d, Y h:i A') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="document.getElementById('toggle-form-{{ $payout->id }}').submit();" 
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 transition-colors duration-200">
                                        {{ session('expanded_payout_'.$payout->id) ? 'Hide Details' : 'View Details' }}
                                    </button>
                                    <form id="toggle-form-{{ $payout->id }}" method="POST" action="{{ route('seller.earnings.toggle-details') }}" class="hidden">
                                        @csrf
                                        <input type="hidden" name="payout_id" value="{{ $payout->id }}">
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($payout->status === 'rejected')
                                        <span class="text-sm text-red-600 dark:text-red-400">{{ $payout->rejection_reason }}</span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @if(session('expanded_payout_'.$payout->id))
                            <tr>
                                <td colspan="5" class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Bank Name</label>
                                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $payout->bank_name }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Account Number</label>
                                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $payout->account_number }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Account Holder</label>
                                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $payout->account_holder_name }}</p>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                                                <p class="mt-1">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        @if($payout->status === 'completed') bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100
                                                        @elseif($payout->status === 'pending') bg-amber-100 text-amber-800 dark:bg-amber-800 dark:text-amber-100
                                                        @else bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 @endif">
                                                        {{ ucfirst($payout->status) }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Requested On</label>
                                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $payout->created_at->format('M d, Y h:i A') }}</p>
                                            </div>
                                            @if($payout->receipt_path)
                                            <div class="mt-4">
                                                <a href="{{ route('seller.earnings.download-receipt', $payout->id) }}" 
                                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
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
            </div>
            @endif

            <!-- Recent Transactions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Transactions</h3>
                </div>

                <div class="p-6">
                    @if($recentTransactions->isEmpty())
                        <div class="text-center py-4">
                            <div class="inline-block p-4 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400">No recent transactions found.</p>
                        </div>
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
                                <tbody class="bg-white divide-y divide-gray-200 dark:divide-gray-700 dark:bg-gray-800">
                                    @foreach($recentTransactions as $transaction)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8 flex items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                                                        <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $transaction->created_at->format('M d, Y') }}
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $transaction->created_at->format('h:i A') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                                    #{{ $transaction->order_id }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    LKR {{ number_format($transaction->price * $transaction->quantity, 2) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($transaction->order->delivery_status === 'completed') bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100
                                                    @elseif($transaction->order->delivery_status === 'delivered') bg-emerald-100 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-100
                                                    @else bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100 @endif">
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
            </div>

            <!-- Request Payout -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Request Payout</h3>
                </div>
                <div class="p-6">
                    <div class="max-w-3xl mx-auto">
                        <!-- Current Balance Display -->
                        <div class="mb-8 text-center">
                            <div class="inline-block bg-indigo-500 p-1 rounded-xl">
                                <div class="bg-white dark:bg-gray-800 rounded-lg px-8 py-4">
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Available for Withdrawal</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        LKR {{ number_format($balance->balance_to_be_paid, 2) }}
                                    </p>
                                    @if($balance->pending_balance > 0)
                                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                            (LKR {{ number_format($balance->pending_balance, 2) }} pending)
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Withdrawal Form -->
                        <form action="{{ route('seller.earnings.request-payout') }}" method="POST" class="space-y-6">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Amount Input -->
                                <div class="col-span-2">
                                    <div class="relative">
                                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Withdrawal Amount (LKR)
                                        </label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="number" 
                                                   name="amount" 
                                                   id="amount" 
                                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-4 pr-12 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white" 
                                                   placeholder="0.00"
                                                   step="0.01" 
                                                   min="1" 
                                                   max="{{ $balance->available_balance }}"
                                                   value="{{ old('amount') }}" 
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Details Section -->
                                <div class="col-span-2">
                                    <div class="relative">
                                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                                        </div>
                                        <div class="relative flex justify-center">
                                            <span class="px-2 bg-white dark:bg-gray-800 text-sm text-gray-500 dark:text-gray-400">
                                                Bank Details
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Name -->
                                <div class="relative group">
                                    <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Bank Name
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </div>
                                        <input type="text" name="bank_name" id="bank_name"
                                               class="block w-full pl-10 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 dark:bg-gray-700 dark:text-gray-100 transition-colors duration-200"
                                               value="{{ old('bank_name') }}"
                                               required>
                                    </div>
                                    @error('bank_name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Account Number -->
                                <div class="relative group">
                                    <label for="account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Account Number
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 10h.01M12 10h.01M9 10h.01" />
                                            </svg>
                                        </div>
                                        <input type="text" name="account_number" id="account_number"
                                               class="block w-full pl-10 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 dark:bg-gray-700 dark:text-gray-100 transition-colors duration-200"
                                               value="{{ old('account_number') }}"
                                               required>
                                    </div>
                                    @error('account_number')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Account Holder Name -->
                                <div class="col-span-2">
                                    <label for="account_holder_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Account Holder Name
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <input type="text" name="account_holder_name" id="account_holder_name"
                                               class="block w-full pl-10 border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-indigo-500 dark:focus:border-indigo-400 dark:bg-gray-700 dark:text-gray-100 transition-colors duration-200"
                                               value="{{ old('account_holder_name') }}"
                                               required>
                                    </div>
                                    @error('account_holder_name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Submit Button -->
                                <div class="col-span-2">
                                    <button type="submit" 
                                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transform transition-all duration-200 hover:scale-[1.02]">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
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

<x-app-layout>
    <x-slot name="header">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Payout Management</h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.dashboard') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-200 ease-in-out">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Pending Requests -->
                <div class="bg-yellow-100 dark:bg-yellow-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-800 dark:text-yellow-100 text-sm font-medium">Pending Requests</p>
                            <p class="text-yellow-900 dark:text-yellow-50 text-2xl font-bold">{{ $statsArray['pending_count'] ?? 0 }}</p>
                        </div>
                        <div class="text-yellow-500">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>

                <!-- Pending Amount -->
                <div class="bg-blue-100 dark:bg-blue-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-800 dark:text-blue-100 text-sm font-medium">Pending Amount</p>
                            <p class="text-blue-900 dark:text-blue-50 text-2xl font-bold">LKR {{ number_format($statsArray['pending_amount'] ?? 0, 2) }}</p>
                        </div>
                        <div class="text-blue-500">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>

                <!-- Completed Payouts -->
                <div class="bg-green-100 dark:bg-green-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-800 dark:text-green-100 text-sm font-medium">Completed Payouts</p>
                            <p class="text-green-900 dark:text-green-50 text-2xl font-bold">{{ $statsArray['completed_count'] ?? 0 }}</p>
                        </div>
                        <div class="text-green-500">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Paid -->
                <div class="bg-purple-100 dark:bg-purple-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-800 dark:text-purple-100 text-sm font-medium">Total Paid</p>
                            <p class="text-purple-900 dark:text-purple-50 text-2xl font-bold">LKR {{ number_format($statsArray['completed_amount'] ?? 0, 2) }}</p>
                        </div>
                        <div class="text-purple-500">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <ul class="flex border-b border-gray-200 dark:border-gray-700 mb-6" role="tablist">
                        <li class="mr-1">
                            <button class="px-4 py-2 font-semibold text-gray-600 dark:text-gray-300 bg-transparent rounded-t-lg hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent hover:border-gray-300 active" id="pending-tab" data-tab="pending">
                                Pending Payouts
                            </button>
                        </li>
                        <li class="mr-1">
                            <button class="px-4 py-2 font-semibold text-gray-600 dark:text-gray-300 bg-transparent rounded-t-lg hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent hover:border-gray-300" id="approved-tab" data-tab="approved">
                                Approved Payouts
                            </button>
                        </li>
                        <li class="mr-1">
                            <button class="px-4 py-2 font-semibold text-gray-600 dark:text-gray-300 bg-transparent rounded-t-lg hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent hover:border-gray-300" id="completed-tab" data-tab="completed">
                                Completed Payouts
                            </button>
                        </li>
                        <li class="mr-1">
                            <button class="px-4 py-2 font-semibold text-gray-600 dark:text-gray-300 bg-transparent rounded-t-lg hover:text-gray-900 dark:hover:text-white border-b-2 border-transparent hover:border-gray-300" id="rejected-tab" data-tab="rejected">
                                Rejected Payouts
                            </button>
                        </li>
                    </ul>

                    <!-- Pending Payouts Table -->
                    <div id="pending-content" class="tab-content block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Request ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Seller</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Balance
                                            <div class="text-xxs font-normal">
                                                <span class="text-green-500">Available</span> / 
                                                <span class="text-yellow-500">Pending</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Request Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Paid</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bank Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Orders</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($pendingPayouts as $payout)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">#{{ $payout->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-300">{{ $payout->user->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $payout->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm">
                                                <span class="text-green-500">LKR {{ number_format($payout->seller->sellerBalance->available_balance ?? 0, 2) }}</span>
                                                <span class="text-gray-400">/</span>
                                                <span class="text-yellow-500">LKR {{ number_format($payout->seller->sellerBalance->pending_balance ?? 0, 2) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            LKR {{ number_format($payout->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            LKR {{ number_format($sellerTotalPaid[$payout->seller_id] ?? 0, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button onclick="showBankDetails('{{ $payout->bank_name }}', '{{ $payout->account_number }}', '{{ $payout->account_holder_name }}')" 
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                View Details
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button onclick="viewOrderDetails({{ $payout->id }})" 
                                                    class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-1 px-2 rounded">
                                                View Orders
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <button onclick="verifyDeliveryStatus({{ $payout->id }})"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                Verify Delivery
                                            </button>
                                            <button onclick="showCompletePayoutModal({{ $payout->id }}, {{ $payout->amount }})"
                                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                Process
                                            </button>
                                            <button onclick="rejectPayout({{ $payout->id }})"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                Reject
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $pendingPayouts->links() }}
                            </div>
                        </div>
                    </div>

                    <!-- Approved Payouts Table -->
                    <div id="approved-content" class="tab-content hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Request ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bank Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($approvedPayouts as $payout)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">#{{ $payout->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            <div>{{ $payout->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $payout->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">LKR {{ number_format($payout->amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            <button onclick="showBankDetails('{{ $payout->bank_name }}', '{{ $payout->account_number }}', '{{ $payout->account_holder_name }}')" class="text-blue-600 hover:text-blue-800">
                                                View Details
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ $payout->created_at->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="completeApprovedPayout({{ $payout->id }}, {{ $payout->amount }})" class="text-green-600 hover:text-green-900">
                                                Complete Payout
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $approvedPayouts->appends(['approved_page' => $approvedPayouts->currentPage()])->links() }}
                            </div>
                        </div>
                    </div>

                    <!-- Completed Payouts Table -->
                    <div id="completed-content" class="tab-content hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Request ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Transaction ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Completed At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($completedPayouts as $payout)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">#{{ $payout->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            <div>{{ $payout->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $payout->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">LKR {{ number_format($payout->amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ $payout->transaction_id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ $payout->completed_at ? $payout->completed_at->format('M d, Y H:i A') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="showBankDetails('{{ $payout->bank_name }}', '{{ $payout->account_number }}', '{{ $payout->account_holder_name }}')" class="text-blue-600 hover:text-blue-800">
                                                View Details
                                            </button>
                                            @if($payout->receipt_path && !empty(trim($payout->receipt_path)))
                                                <a href="{{ asset('storage/' . $payout->receipt_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                                    View Receipt
                                                </a>
                                            @else
                                                <span class="text-gray-400">No Receipt</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $completedPayouts->appends(['completed_page' => $completedPayouts->currentPage()])->links() }}
                            </div>
                        </div>
                    </div>

                    <!-- Rejected Payouts Table -->
                    <div id="rejected-content" class="tab-content hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Request ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reason
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($rejectedPayouts as $payout)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">#{{ $payout->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                            <div>{{ $payout->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $payout->user->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">LKR {{ number_format($payout->amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($payout->status === 'rejected') bg-red-100 text-red-800
                                                @endif">
                                                {{ ucfirst($payout->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($payout->status === 'rejected' && $payout->rejection_reason)
                                                <span class="text-red-600">{{ $payout->rejection_reason }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payout->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="showBankDetails('{{ $payout->bank_name }}', '{{ $payout->account_number }}', '{{ $payout->account_holder_name }}')" class="text-blue-600 hover:text-blue-800">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Details Modal -->
    <div id="bankDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-2">Bank Details</h3>
                <div class="mt-2 px-7 py-3">
                    <div class="mb-2">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Bank Name:</span>
                        <span id="bankName" class="ml-2 text-gray-600 dark:text-gray-400"></span>
                    </div>
                    <div class="mb-2">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Account Number:</span>
                        <span id="accountNumber" class="ml-2 text-gray-600 dark:text-gray-400"></span>
                    </div>
                    <div class="mb-2">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Account Holder:</span>
                        <span id="accountHolder" class="ml-2 text-gray-600 dark:text-gray-400"></span>
                    </div>
                </div>
                <div class="px-4 py-3 text-right">
                    <button type="button" onclick="closeBankDetailsModal()" 
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Reject Payout Request</h3>
                <form id="rejectForm" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rejection Reason</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideRejectModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                            Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Payout Modal -->
    <div id="completePayoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Complete Payout</h3>
                <form id="completePayoutForm" method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <div class="mb-4">
                        <label for="transaction_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction ID</label>
                        <input type="text" name="transaction_id" id="transaction_id" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="mb-4">
                        <label for="receipt" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Receipt</label>
                        <input type="file" name="receipt" id="receipt" required accept=".pdf,.jpg,.jpeg,.png"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload a PDF or image file (max 2MB)</p>
                    </div>
                    <div class="flex justify-end mt-6">
                        <button type="button" onclick="closeCompletePayoutModal()" class="mr-3 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Complete Payout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Order Details</h3>
                <button onclick="closeOrderDetailsModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="orderDetailsContent" class="mt-4">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Delivery Verification Modal -->
    <div id="deliveryVerificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Verify Delivery Status</h3>
                <button onclick="closeDeliveryVerificationModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="deliveryVerificationContent" class="mt-4">
                <!-- Delivery verification details will be loaded here -->
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Tab functionality
        document.querySelectorAll('[data-tab]').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all tabs
                document.querySelectorAll('[data-tab]').forEach(btn => {
                    btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    btn.classList.add('border-transparent', 'text-gray-500');
                });

                // Add active class to clicked tab
                button.classList.add('active', 'border-blue-500', 'text-blue-600');
                button.classList.remove('border-transparent', 'text-gray-500');

                // Hide all content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });

                // Show selected content
                const contentId = button.getAttribute('data-tab') + '-content';
                document.getElementById(contentId).classList.remove('hidden');
            });
        });

        // Bank details modal functions
        function showBankDetails(bankName, accountNumber, accountHolder) {
            document.getElementById('bankName').textContent = bankName;
            document.getElementById('accountNumber').textContent = accountNumber;
            document.getElementById('accountHolder').textContent = accountHolder;
            document.getElementById('bankDetailsModal').classList.remove('hidden');
        }

        function closeBankDetailsModal() {
            document.getElementById('bankDetailsModal').classList.add('hidden');
        }

        // Order details modal functions
        function viewOrderDetails(payoutId) {
            // Show loading state
            const modal = document.getElementById('orderDetailsModal');
            const content = document.getElementById('orderDetailsContent');
            content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
            modal.classList.remove('hidden');
            
            // Fetch order details
            fetch(`/admin/payouts/${payoutId}/orders`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = '<div class="text-red-500">Error loading order details</div>';
                });
        }

        function closeOrderDetailsModal() {
            document.getElementById('orderDetailsModal').classList.add('hidden');
        }

        // Delivery verification modal functions
        function verifyDeliveryStatus(payoutId) {
            // Show loading state
            const modal = document.getElementById('deliveryVerificationModal');
            const content = document.getElementById('deliveryVerificationContent');
            content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
            modal.classList.remove('hidden');
            
            // Fetch delivery verification details
            fetch(`/admin/payouts/${payoutId}/verify-delivery`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = '<div class="text-red-500">Error loading delivery verification details</div>';
                });
        }

        function closeDeliveryVerificationModal() {
            document.getElementById('deliveryVerificationModal').classList.add('hidden');
        }

        // Filter functions
        function filterPayouts() {
            const deliveryStatus = document.getElementById('deliveryStatus').value;
            const paymentStatus = document.getElementById('paymentStatus').value;
            
            // Refresh the page with new filters
            window.location.href = `/admin/payouts?delivery_status=${deliveryStatus}&payment_status=${paymentStatus}`;
        }

        // Process payout function (approve)
        function showCompletePayoutModal(payoutId, amount) {
            if (confirm('Are you sure you want to approve this payout request?')) {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                
                fetch(`/admin/payouts/${payoutId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to approve payout request');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to approve payout request. Please try again.');
                });
            }
        }

        // Complete approved payout function
        function completeApprovedPayout(payoutId, amount) {
            // Show the modal
            const modal = document.getElementById('completePayoutModal');
            modal.classList.remove('hidden');
            
            // Set up the form
            const form = document.getElementById('completePayoutForm');
            form.action = `/admin/payouts/${payoutId}/complete`;
            
            // Add event listener for form submission
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                const token = document.querySelector('meta[name="csrf-token"]').content;
                
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to complete payout');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to complete payout. Please try again.');
                });
            };
        }

        // Reject payout function
        function rejectPayout(payoutId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/admin/payouts/${payoutId}/reject`;
            modal.classList.remove('hidden');

            form.onsubmit = function(e) {
                e.preventDefault();
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to reject payout request');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to reject payout request. Please try again.');
                });
            };
        }

        function hideRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
            document.getElementById('rejectForm').reset();
        }

        function closeCompletePayoutModal() {
            const modal = document.getElementById('completePayoutModal');
            modal.classList.add('hidden');
            document.getElementById('completePayoutForm').reset();
        }
    </script>
    @endpush
</x-app-layout>

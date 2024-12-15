@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Payout Requests</h1>
        <a href="{{ route('seller.payouts.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Request Payout
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Balance Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-gray-50 rounded">
                <p class="text-gray-600">Available Balance</p>
                <p class="text-2xl font-bold">₹{{ number_format($sellerBalance->available_balance, 2) }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded">
                <p class="text-gray-600">Pending Balance</p>
                <p class="text-2xl font-bold">₹{{ number_format($sellerBalance->pending_balance, 2) }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded">
                <p class="text-gray-600">Total Earned</p>
                <p class="text-2xl font-bold">₹{{ number_format($sellerBalance->total_earned, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($payoutRequests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $request->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            ₹{{ number_format($request->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $request->status_badge }}-100 text-{{ $request->status_badge }}-800">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $request->bank_name }} - {{ substr($request->account_number, -4) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('seller.payouts.show', $request) }}" class="text-blue-600 hover:text-blue-900">View</a>
                            @if($request->status === 'pending')
                                <form action="{{ route('seller.payouts.cancel', $request) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-2 text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to cancel this request?')">
                                        Cancel
                                    </button>
                                </form>
                            @endif
                            @if(auth()->user()->role === 'admin' && $request->status === 'pending')
                                <form action="{{ route('admin.payouts.approve', $request) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="ml-2 text-green-600 hover:text-green-900" onclick="return confirm('Are you sure you want to approve this request?')">
                                        Approve
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No payout requests found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payoutRequests->links() }}
    </div>
</div>
@endsection

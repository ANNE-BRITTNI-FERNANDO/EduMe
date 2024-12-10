@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payout History</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payouts as $payout)
                        <tr>
                            <td>#{{ $payout->id }}</td>
                            <td>${{ number_format($payout->amount, 2) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payout->payment_method)) }}</td>
                            <td>
                                <span class="badge badge-{{ $payout->status_badge }}">
                                    {{ ucfirst($payout->status) }}
                                </span>
                            </td>
                            <td>{{ $payout->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $payout->updated_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('seller.earnings.show-payout', $payout) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No payout requests found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payouts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

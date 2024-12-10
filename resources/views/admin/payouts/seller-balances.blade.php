@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Seller Balances</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.payouts.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-list"></i> Pending Requests
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Seller</th>
                                <th>Available Balance</th>
                                <th>Pending Balance</th>
                                <th>Total Earned</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sellers as $seller)
                            <tr>
                                <td>{{ $seller->id }}</td>
                                <td>
                                    <div>{{ $seller->name }}</div>
                                    <small class="text-muted">{{ $seller->email }}</small>
                                </td>
                                <td>${{ number_format($seller->sellerBalance->available_balance ?? 0, 2) }}</td>
                                <td>${{ number_format($seller->sellerBalance->pending_balance ?? 0, 2) }}</td>
                                <td>${{ number_format($seller->sellerBalance->total_earned ?? 0, 2) }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-info" onclick="viewPayoutHistory({{ $seller->id }})">
                                        <i class="fas fa-history"></i> Payout History
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No sellers found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $sellers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewPayoutHistory(sellerId) {
    // You can implement this to show a modal with the seller's payout history
    // or redirect to a filtered history page
    window.location.href = `{{ url('admin/payouts/history') }}?seller_id=${sellerId}`;
}
</script>
@endpush

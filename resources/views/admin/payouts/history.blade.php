@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payout History</h3>
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
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Rejection Reason</th>
                                <th>Processed By</th>
                                <th>Processed At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($processedPayouts as $payout)
                            <tr>
                                <td>{{ $payout->id }}</td>
                                <td>{{ $payout->user->name }}</td>
                                <td>${{ number_format($payout->amount, 2) }}</td>
                                <td>
                                    @if($payout->status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                        <br>
                                        <small>{{ $payout->rejection_reason }}</small>
                                    @else
                                        <span class="badge badge-info">{{ ucfirst($payout->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $payout->rejection_reason ?? 'N/A' }}</td>
                                <td>{{ $payout->processor ? $payout->processor->name : 'N/A' }}</td>
                                <td>{{ $payout->processed_at ? $payout->processed_at->format('M d, Y H:i') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.payouts.show', $payout) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No processed payouts found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $processedPayouts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

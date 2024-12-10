@extends('admin.layouts.app')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Payout Request Details</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.payouts.index') }}">Payouts</a></li>
            <li class="breadcrumb-item active">Request #{{ $payoutRequest->id ?? 'N/A' }}</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Payout Details -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Payout Information</h3>
                <div class="card-tools">
                    @if($payoutRequest->status === 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($payoutRequest->status === 'approved')
                        <span class="badge badge-success">Approved</span>
                    @elseif($payoutRequest->status === 'completed')
                        <span class="badge badge-primary">Completed</span>
                    @else
                        <span class="badge badge-danger">Rejected</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Request ID</dt>
                            <dd class="col-sm-8">#{{ $payoutRequest->id ?? 'N/A' }}</dd>

                            <dt class="col-sm-4">Amount</dt>
                            <dd class="col-sm-8">${{ number_format($payoutRequest->amount ?? 0, 2) }}</dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                @if($payoutRequest->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($payoutRequest->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($payoutRequest->status === 'completed')
                                    <span class="badge badge-primary">Completed</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </dd>

                            <dt class="col-sm-4">Requested On</dt>
                            <dd class="col-sm-8">{{ $payoutRequest->created_at ? $payoutRequest->created_at->format('M d, Y H:i') : 'N/A' }}</dd>

                            <dt class="col-sm-4">Last Updated</dt>
                            <dd class="col-sm-8">{{ $payoutRequest->updated_at ? $payoutRequest->updated_at->format('M d, Y H:i') : 'N/A' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4">Payment Method</dt>
                            <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $payoutRequest->payment_method ?? 'N/A')) }}</dd>

                            <dt class="col-sm-4">Payment Details</dt>
                            <dd class="col-sm-8">
                                @if(is_array($payoutRequest->payment_details) || is_object($payoutRequest->payment_details))
                                    @foreach($payoutRequest->payment_details as $key => $value)
                                        <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}<br>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </dd>

                            @if($payoutRequest->processed_at)
                                <dt class="col-sm-4">Processed At</dt>
                                <dd class="col-sm-8">{{ $payoutRequest->processed_at->format('M d, Y H:i') }}</dd>

                                <dt class="col-sm-4">Processed By</dt>
                                <dd class="col-sm-8">{{ $payoutRequest->processor->name }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>

                @if($payoutRequest->notes)
                <div class="alert alert-info mt-4">
                    <h5><i class="icon fas fa-info"></i> Notes</h5>
                    {{ $payoutRequest->notes }}
                </div>
                @endif

                <!-- Action Buttons -->
                @if($payoutRequest->status === 'pending')
                <div class="mt-4">
                    <form action="{{ route('admin.payouts.approve', $payoutRequest) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this payout request?')">
                            <i class="fas fa-check"></i> Approve Request
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                        <i class="fas fa-times"></i> Reject Request
                    </button>
                </div>
                @elseif($payoutRequest->status === 'approved')
                <div class="mt-4">
                    <form action="{{ route('admin.payouts.complete', $payoutRequest) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to mark this payout as completed? This indicates that you have processed the payment.')">
                            <i class="fas fa-check-double"></i> Mark as Completed
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Seller Information -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Seller Information</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $payoutRequest->user->name ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $payoutRequest->user->email ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">Joined</dt>
                    <dd class="col-sm-8">{{ $payoutRequest->user->created_at ? $payoutRequest->user->created_at->format('M d, Y') : 'N/A' }}</dd>

                    <dt class="col-sm-4">Total Sales</dt>
                    <dd class="col-sm-8">${{ number_format($payoutRequest->user->sellerBalance->total_earned ?? 0, 2) }}</dd>

                    <dt class="col-sm-4">Available</dt>
                    <dd class="col-sm-8">${{ number_format($payoutRequest->user->sellerBalance->available_balance ?? 0, 2) }}</dd>

                    <dt class="col-sm-4">Pending</dt>
                    <dd class="col-sm-8">${{ number_format($payoutRequest->user->sellerBalance->pending_balance ?? 0, 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
@if($payoutRequest->status === 'pending')
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.payouts.reject', $payoutRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payout Request</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="notes">Rejection Reason</label>
                        <textarea name="notes" class="form-control" rows="3" required></textarea>
                        <small class="form-text text-muted">Please provide a reason for rejecting this payout request. This will be shown to the seller.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payout Request Details</h3>
            <div class="card-tools">
                <a href="{{ route('seller.earnings.history') }}" class="btn btn-tool">
                    <i class="fas fa-arrow-left"></i> Back to History
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>General Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Request ID</th>
                            <td>#{{ $payoutRequest->id }}</td>
                        </tr>
                        <tr>
                            <th>Amount</th>
                            <td>${{ number_format($payoutRequest->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge badge-{{ $payoutRequest->status_badge }}">
                                    {{ ucfirst($payoutRequest->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Method</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $payoutRequest->payment_method)) }}</td>
                        </tr>
                        <tr>
                            <th>Requested On</th>
                            <td>{{ $payoutRequest->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $payoutRequest->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Payment Details</h5>
                    <table class="table table-bordered">
                        @if($payoutRequest->payment_method === 'bank_transfer')
                            <tr>
                                <th>Account Holder</th>
                                <td>{{ $payoutRequest->payment_details['account_holder_name'] }}</td>
                            </tr>
                            <tr>
                                <th>Bank Name</th>
                                <td>{{ $payoutRequest->payment_details['bank_name'] }}</td>
                            </tr>
                            <tr>
                                <th>Account Number</th>
                                <td>{{ $payoutRequest->payment_details['account_number'] }}</td>
                            </tr>
                        @elseif($payoutRequest->payment_method === 'paypal')
                            <tr>
                                <th>PayPal Email</th>
                                <td>{{ $payoutRequest->payment_details['paypal_email'] }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($payoutRequest->status === 'rejected')
            <div class="mt-4">
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Payout Request Rejected</h5>
                    <p>{{ $payoutRequest->rejection_reason ?? 'No reason provided.' }}</p>
                </div>
            </div>
            @endif

            @if($payoutRequest->status === 'completed')
            <div class="mt-4">
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
@endsection

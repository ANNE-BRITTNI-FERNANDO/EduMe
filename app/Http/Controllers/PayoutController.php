<?php

namespace App\Http\Controllers;

use App\Models\PayoutRequest;
use App\Models\SellerBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\PayoutStatusChanged;
use App\Notifications\PayoutApproved;
use App\Notifications\PayoutCompleted;

class PayoutController extends Controller
{
    public function index()
    {
        $payoutRequests = auth()->user()->payoutRequests()
            ->latest()
            ->paginate(10);

        $sellerBalance = auth()->user()->sellerBalance;

        return view('seller.payouts.index', compact('payoutRequests', 'sellerBalance'));
    }

    public function create()
    {
        $sellerBalance = auth()->user()->sellerBalance;
        return view('seller.payouts.create', compact('sellerBalance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:50',
                'regex:/^\d*\.?\d{0,2}$/' // Allow only 2 decimal places
            ],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^[0-9]+$/' // Only numbers allowed
            ],
            'account_holder_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s]+$/' // Only letters and spaces allowed
            ],
        ], [
            'amount.min' => 'Withdrawal amount must be at least 50.',
            'amount.regex' => 'Amount can only have up to 2 decimal places.',
            'account_number.regex' => 'Account number must contain only numbers.',
            'account_number.min' => 'Account number must be at least 8 digits.',
            'account_number.max' => 'Account number cannot exceed 20 digits.',
            'account_holder_name.regex' => 'Account holder name can only contain letters and spaces.'
        ]);
    
        $sellerBalance = auth()->user()->sellerBalance;
    
        if ($sellerBalance->available_balance < $request->amount) {
            return back()->with('error', 'Insufficient available balance');
        }
    
        DB::transaction(function () use ($request, $sellerBalance) {
            // Move amount to pending
            $sellerBalance->moveToPending($request->amount);
    
            // Create payout request
            PayoutRequest::create([
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'status' => PayoutRequest::STATUS_PENDING,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name,
                'bank_details' => json_encode([
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_holder_name' => $request->account_holder_name,
                ]),
            ]);
        });
    
        return redirect()->route('seller.payouts.index')
            ->with('success', 'Payout request submitted successfully');
    }

    public function show(PayoutRequest $payoutRequest)
    {
        $this->authorize('view', $payoutRequest);
        return view('seller.payouts.show', compact('payoutRequest'));
    }

    public function approve(PayoutRequest $payoutRequest)
    {
        $this->authorize('approve', $payoutRequest);

        if ($payoutRequest->status !== PayoutRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending requests can be approved');
        }

        try {
            DB::transaction(function () use ($payoutRequest) {
                $payoutRequest->update([
                    'status' => PayoutRequest::STATUS_APPROVED,
                    'approved_at' => now(),
                    'processed_by' => auth()->id()
                ]);

                // Approve the payout in SellerBalance
                $sellerBalance = $payoutRequest->user->sellerBalance;
                $sellerBalance->approvePayout($payoutRequest->amount);

                // Notify user
                $payoutRequest->user->notify(new PayoutApproved($payoutRequest));
            });

            return back()->with('success', 'Payout request approved successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve payout request. Please try again.');
        }
    }

    public function uploadReceipt(Request $request, PayoutRequest $payoutRequest)
    {
        $this->authorize('uploadReceipt', $payoutRequest);
        
        if ($payoutRequest->status !== PayoutRequest::STATUS_APPROVED) {
            return back()->with('error', 'Only approved requests can have receipts uploaded');
        }

        $request->validate([
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'transaction_id' => ['required', 'string'],
        ]);

        $path = $request->file('receipt')->store('receipts', 'public');

        $payoutRequest->update([
            'receipt_path' => $path,
            'receipt_uploaded_at' => now(),
            'transaction_id' => $request->transaction_id,
            'status' => PayoutRequest::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Notify user
        $payoutRequest->user->notify(new PayoutCompleted($payoutRequest));

        return back()->with('success', 'Receipt uploaded and payout marked as completed');
    }

    public function cancel(PayoutRequest $payoutRequest)
    {
        $this->authorize('cancel', $payoutRequest);

        if ($payoutRequest->status !== PayoutRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending requests can be cancelled');
        }

        DB::transaction(function () use ($payoutRequest) {
            // Move amount back to available balance
            $payoutRequest->user->sellerBalance->moveFromPending($payoutRequest->amount);

            // Update status
            $payoutRequest->update([
                'status' => PayoutRequest::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);
        });

        return back()->with('success', 'Payout request cancelled successfully');
    }
}

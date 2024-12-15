<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function index()
    {
        $pendingPayouts = PayoutRequest::with(['user'])
            ->where('status', PayoutRequest::STATUS_PENDING)
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        $approvedPayouts = PayoutRequest::with(['user'])
            ->where('status', PayoutRequest::STATUS_APPROVED)
            ->latest()
            ->paginate(10, ['*'], 'approved_page');

        $completedPayouts = PayoutRequest::with(['user'])
            ->where('status', PayoutRequest::STATUS_COMPLETED)
            ->latest()
            ->paginate(10, ['*'], 'completed_page');

        $rejectedPayouts = PayoutRequest::with(['user'])
            ->where('status', PayoutRequest::STATUS_REJECTED)
            ->latest()
            ->paginate(10, ['*'], 'rejected_page');

        $stats = [
            'pending_count' => PayoutRequest::where('status', PayoutRequest::STATUS_PENDING)->count(),
            'pending_amount' => PayoutRequest::where('status', PayoutRequest::STATUS_PENDING)->sum('amount'),
            'approved_count' => PayoutRequest::where('status', PayoutRequest::STATUS_APPROVED)->count(),
            'approved_amount' => PayoutRequest::where('status', PayoutRequest::STATUS_APPROVED)->sum('amount'),
            'completed_count' => PayoutRequest::where('status', PayoutRequest::STATUS_COMPLETED)->count(),
            'completed_amount' => PayoutRequest::where('status', PayoutRequest::STATUS_COMPLETED)->sum('amount'),
        ];

        return view('admin.payouts.index', compact('pendingPayouts', 'approvedPayouts', 'completedPayouts', 'rejectedPayouts', 'stats'));
    }

    public function show(PayoutRequest $payoutRequest)
    {
        $payoutRequest->load('user');
        return view('admin.payouts.show', compact('payoutRequest'));
    }

    public function history(Request $request)
    {
        $query = PayoutRequest::with(['user', 'processor'])
            ->when($request->status, function($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->search, function($q, $search) {
                return $q->whereHas('user', function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest();

        $payouts = $query->paginate(15)->withQueryString();
        
        return view('admin.payouts.history', compact('payouts'));
    }

    public function approve(PayoutRequest $payoutRequest)
    {
        try {
            DB::transaction(function () use ($payoutRequest) {
                // Check if payout is pending
                if ($payoutRequest->status !== PayoutRequest::STATUS_PENDING) {
                    throw new \Exception('Only pending requests can be approved');
                }

                // Update payout request
                $payoutRequest->update([
                    'status' => PayoutRequest::STATUS_APPROVED,
                    'processed_at' => now(),
                    'processed_by' => auth()->id()
                ]);

                // Send notification to user
                $payoutRequest->user->notify(new \App\Notifications\PayoutApproved($payoutRequest));
            });

            return back()->with('success', 'Payout request approved successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function complete(PayoutRequest $payoutRequest)
    {
        try {
            DB::transaction(function () use ($payoutRequest) {
                $payoutRequest->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => auth()->id(),
                ]);
            });

            return redirect()->back()->with('success', 'Payout has been marked as completed.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to complete payout.');
        }
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        try {
            DB::transaction(function () use ($payoutRequest, $request) {
                $payoutRequest->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'rejected_by' => auth()->id(),
                    'rejection_reason' => $request->input('rejection_reason'),
                ]);

                // Revert the payout amount back to the user's available balance
                $sellerBalance = $payoutRequest->user->sellerBalance;
                $sellerBalance->pending_balance -= $payoutRequest->amount;
                $sellerBalance->available_balance += $payoutRequest->amount;
                $sellerBalance->save();
            });

            return redirect()->back()->with('success', 'Payout request has been rejected.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reject payout request.');
        }
    }

    public function sellerBalances(Request $request)
    {
        $query = User::role('seller')
            ->with('sellerBalance')
            ->when($request->search, function($q, $search) {
                return $q->where(function($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                });
            });

        $sellers = $query->paginate(15)->withQueryString();

        $totalStats = [
            'total_available' => DB::table('seller_balances')->sum('available_balance'),
            'total_pending' => DB::table('seller_balances')->sum('pending_balance'),
            'total_earned' => DB::table('seller_balances')->sum('total_earned'),
        ];

        return view('admin.payouts.seller-balances', compact('sellers', 'totalStats'));
    }

    public function uploadReceipt(Request $request, PayoutRequest $payoutRequest)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'transaction_id' => 'required|string|max:255'
        ]);

        try {
            DB::transaction(function () use ($request, $payoutRequest) {
                // Check if payout is in approved status
                if ($payoutRequest->status !== PayoutRequest::STATUS_APPROVED) {
                    throw new \Exception('Only approved payouts can have receipts uploaded');
                }

                // Store the receipt
                $receiptPath = $request->file('receipt')->store('payout-receipts', 'public');

                // Update payout request
                $payoutRequest->update([
                    'status' => PayoutRequest::STATUS_COMPLETED,
                    'receipt_path' => $receiptPath,
                    'receipt_uploaded_at' => now(),
                    'transaction_id' => $request->transaction_id,
                    'completed_at' => now(),
                    'completed_by' => auth()->id()
                ]);

                // Send notification to user
                $payoutRequest->user->notify(new \App\Notifications\PayoutCompleted($payoutRequest));
            });

            return back()->with('success', 'Receipt uploaded and payout marked as completed');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

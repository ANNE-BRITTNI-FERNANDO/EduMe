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
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        $rejectedPayouts = PayoutRequest::with(['user'])
            ->where('status', 'rejected')
            ->latest()
            ->get();

        $stats = [
            'pending_count' => PayoutRequest::where('status', 'pending')->count(),
            'pending_amount' => PayoutRequest::where('status', 'pending')->sum('amount'),
            'completed_count' => PayoutRequest::where('status', 'completed')->count(),
            'completed_amount' => PayoutRequest::where('status', 'completed')->sum('amount'),
        ];

        return view('admin.payouts.index', compact('pendingPayouts', 'stats', 'rejectedPayouts'));
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
                $payoutRequest->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
            });

            return redirect()->back()->with('success', 'Payout request approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to approve payout request.');
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
}

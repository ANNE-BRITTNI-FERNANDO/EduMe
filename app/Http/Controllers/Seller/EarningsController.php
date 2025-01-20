<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\SellerBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $balance = SellerBalance::firstOrCreate(
            ['seller_id' => $user->id],
            [
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0
            ]
        );

        // Get pending payouts amount
        $pendingPayouts = PayoutRequest::where('seller_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        // Get approved payouts amount
        $approvedPayouts = PayoutRequest::where('seller_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');

        $recentPayouts = PayoutRequest::where('seller_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Recalculate balance to ensure it's accurate
        $balance->recalculateBalance();

        return view('seller.earnings.index', compact('balance', 'pendingPayouts', 'approvedPayouts', 'recentPayouts'));
    }

    public function history()
    {
        $payouts = PayoutRequest::where('seller_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('seller.earnings.history', compact('payouts'));
    }

    public function requestPayout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_holder_name' => 'required|string',
        ]);

        $user = auth()->user();
        $sellerBalance = SellerBalance::where('seller_id', $user->id)->first();

        if (!$sellerBalance || $sellerBalance->available_balance < $request->amount) {
            return back()->with('error', 'Insufficient available balance.');
        }

        try {
            DB::transaction(function () use ($request, $user, $sellerBalance) {
                // Create payout request
                $payoutRequest = new PayoutRequest([
                    'seller_id' => $user->id,
                    'amount' => $request->amount,
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_holder_name' => $request->account_holder_name,
                    'status' => 'pending'
                ]);
                $payoutRequest->save();

                // Move amount to pending
                $sellerBalance->moveToPending($request->amount);
            });

            return redirect()
                ->route('seller.earnings.history')
                ->with('success', 'Payout request submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to submit payout request: ' . $e->getMessage());
        }
    }

    public function showPayout(PayoutRequest $payoutRequest)
    {
        if ($payoutRequest->seller_id !== auth()->id()) {
            abort(403);
        }

        return view('seller.earnings.show-payout', compact('payoutRequest'));
    }
}

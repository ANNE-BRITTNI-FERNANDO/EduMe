<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutRequest;
use App\Models\SellerBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SellerEarningsController extends Controller
{
    public function index()
    {
        $seller = auth()->user();
        
        // Get or create seller balance
        $balance = SellerBalance::firstOrCreate(
            ['seller_id' => $seller->id],
            [
                'total_earned' => 0,
                'balance_to_be_paid' => 0,
                'available_balance' => 0,
                'pending_balance' => 0
            ]
        );

        // Recalculate balance to ensure it's up to date
        $balance->recalculateBalance();
        $balance->refresh(); // Refresh to get the latest values

        // Get pending payout total
        $pendingPayouts = PayoutRequest::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->sum('amount');

        // Get payout requests
        $payouts = PayoutRequest::where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent transactions
        $recentTransactions = OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', function($query) {
                $query->whereIn('delivery_status', ['processing', 'confirmed', 'delivered_to_warehouse']);
            })
            ->with(['order' => function($query) {
                $query->select('id', 'delivery_status', 'created_at');
            }])
            ->select('id', 'order_id', 'price', 'quantity', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('seller.earnings.index', compact('balance', 'pendingPayouts', 'payouts', 'recentTransactions'));
    }

    public function requestPayout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_holder_name' => 'required|string'
        ]);

        $seller = auth()->user();
        
        // Get seller balance
        $balance = SellerBalance::where('seller_id', $seller->id)->firstOrFail();

        // Check if amount is available
        if ($request->amount > $balance->available_balance) {
            return back()->withErrors(['amount' => 'Insufficient balance']);
        }

        // Create payout request
        $payoutRequest = new PayoutRequest();
        $payoutRequest->seller_id = $seller->id;
        $payoutRequest->user_id = $seller->id;
        $payoutRequest->amount = $request->amount;
        $payoutRequest->bank_name = $request->bank_name;
        $payoutRequest->account_number = $request->account_number;
        $payoutRequest->account_holder_name = $request->account_holder_name;
        $payoutRequest->status = 'pending';
        $payoutRequest->save();

        // Update seller balance
        $balance->pending_balance += $request->amount;
        $balance->save();

        return back()->with('success', 'Payout request submitted successfully');
    }

    public function toggleDetails(Request $request)
    {
        $payoutId = $request->input('payout_id');
        $key = 'expanded_payout_' . $payoutId;
        
        // Toggle the session value
        if (session()->has($key)) {
            session()->forget($key);
        } else {
            session()->put($key, true);
        }
        
        return back();
    }

    public function downloadReceipt(PayoutRequest $payout)
    {
        // Check if user owns this payout
        if ($payout->seller_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if receipt exists
        if (!$payout->receipt_path || !Storage::disk('public')->exists($payout->receipt_path)) {
            return back()->with('error', 'Receipt not found.');
        }

        return Storage::disk('public')->download($payout->receipt_path, 'payout_receipt_' . $payout->id . '.' . pathinfo($payout->receipt_path, PATHINFO_EXTENSION));
    }
}

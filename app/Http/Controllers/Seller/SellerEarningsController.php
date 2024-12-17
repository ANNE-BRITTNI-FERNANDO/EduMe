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
        
        // Get seller balance from the table
        $balance = SellerBalance::where('seller_id', $seller->id)->firstOrFail();

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
                $query->whereIn('delivery_status', ['completed', 'delivered', 'confirmed', 'delivered_to_warehouse', 'dispatched']);
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
        $availableBalance = $this->getAvailableBalance($seller->id);
        
        if ($request->amount > $availableBalance) {
            return back()->with('error', 'Requested amount (LKR ' . number_format($request->amount, 2) . ') exceeds available balance (LKR ' . number_format($availableBalance, 2) . ').');
        }

        DB::beginTransaction();
        try {
            // Create payout request
            $payoutRequest = PayoutRequest::create([
                'seller_id' => $seller->id,
                'user_id' => $seller->id,
                'amount' => $request->amount,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name,
                'status' => 'pending'
            ]);

            // Update seller balance in database
            DB::statement("
                UPDATE seller_balances 
                SET available_balance = ? 
                WHERE seller_id = ?
            ", [$availableBalance - $request->amount, $seller->id]);

            DB::commit();
            return redirect()->route('seller.earnings.index')->with('success', 'Payout request submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payout request failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit payout request. Please try again.');
        }
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

    private function getAvailableBalance($sellerId)
    {
        // Calculate total earnings from completed orders
        $totalEarnings = DB::selectOne("
            SELECT COALESCE(SUM(oi.price * oi.quantity), 0) as total
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.id
            WHERE oi.seller_id = ?
            AND o.delivery_status IN ('completed', 'delivered', 'confirmed', 'delivered_to_warehouse', 'dispatched')
        ", [$sellerId])->total;

        // Get total payouts (completed/pending)
        $totalPayouts = DB::selectOne("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM payout_requests
            WHERE seller_id = ?
            AND status IN ('completed', 'pending')
        ", [$sellerId])->total;

        // Available balance is earnings minus payouts
        return $totalEarnings - $totalPayouts;
    }
}

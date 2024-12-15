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
        $user = auth()->user();
        
        // Get or calculate seller balance
        $sellerBalance = SellerBalance::where('seller_id', $user->id)->firstOrCreate(
            ['seller_id' => $user->id],
            [
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0,
                'balance_to_be_paid' => 0,
                'total_delivery_fees_earned' => 0
            ]
        );
        
        // Get total earnings from completed orders
        $totalEarnings = OrderItem::where('seller_id', $user->id)
            ->whereHas('order', function($query) {
                $query->where('payment_status', 'paid');
            })
            ->selectRaw('SUM(price * quantity) as total_earnings')
            ->first()
            ->total_earnings ?? 0;
            
        // Get pending balance from pending orders
        $pendingBalance = OrderItem::where('seller_id', $user->id)
            ->whereHas('order', function($query) {
                $query->whereIn('payment_status', ['pending', 'processing']);
            })
            ->selectRaw('SUM(price * quantity) as pending_balance')
            ->first()
            ->pending_balance ?? 0;
            
        // Update the balance
        $sellerBalance->update([
            'available_balance' => $totalEarnings - $pendingBalance,
            'pending_balance' => $pendingBalance,
            'total_earned' => $totalEarnings,
            'balance_to_be_paid' => $totalEarnings
        ]);

        // Get payout requests
        $payoutRequests = PayoutRequest::where('seller_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent transactions
        $recentTransactions = OrderItem::where('seller_id', $user->id)
            ->whereHas('order', function($query) {
                $query->where('payment_status', 'paid');
            })
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($item) {
                return (object)[
                    'id' => $item->order->id,
                    'created_at' => $item->created_at,
                    'total_amount' => $item->price * $item->quantity
                ];
            });

        return view('seller.earnings.index', [
            'balance' => $sellerBalance,
            'totalEarnings' => $totalEarnings,
            'pendingBalance' => $pendingBalance,
            'payoutRequests' => $payoutRequests,
            'recentTransactions' => $recentTransactions
        ]);
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
        $balance = SellerBalance::where('seller_id', $user->id)->firstOrFail();

        // Check if user has sufficient balance
        if ($balance->available_balance < $request->amount) {
            return back()->with('error', 'Insufficient balance for payout request.');
        }

        DB::beginTransaction();
        try {
            // Create payout request
            PayoutRequest::create([
                'seller_id' => $user->id,
                'amount' => $request->amount,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name,
                'status' => 'pending'
            ]);

            // Update seller balance
            $balance->available_balance -= $request->amount;
            $balance->pending_balance += $request->amount;
            $balance->save();

            DB::commit();
            return redirect()->route('seller.earnings.index')->with('success', 'Payout request submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit payout request. Please try again.');
        }
    }

    public function toggleDetails(PayoutRequest $payout)
    {
        // Toggle the expanded state in session
        $sessionKey = 'expanded_payout_' . $payout->id;
        if (session($sessionKey)) {
            session()->forget($sessionKey);
        } else {
            session([$sessionKey => true]);
        }
        
        return back();
    }

    public function downloadReceipt(PayoutRequest $payout)
    {
        // Check if user owns this payout request
        if ($payout->seller_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        // Check if receipt exists
        if (!$payout->receipt_path) {
            return back()->with('error', 'No receipt has been uploaded yet.');
        }

        // Get the file from storage
        $path = $payout->receipt_path;
        if (!Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Receipt file not found. Please contact support.');
        }

        // Get the original file extension
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        // Create a proper filename with the extension
        $filename = sprintf('payout_receipt_%d.%s', $payout->id, $extension);

        // Return the file directly from the storage disk
        return Storage::disk('public')->download($path, $filename);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\SellerBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PayoutController extends Controller
{
    public function index()
    {
        // Get all payout requests with their relationships
        $pendingPayouts = PayoutRequest::with(['user', 'seller', 'processor'])
            ->where('status', PayoutRequest::STATUS_PENDING)
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        $approvedPayouts = PayoutRequest::with(['user', 'seller', 'processor'])
            ->where('status', PayoutRequest::STATUS_APPROVED)
            ->latest()
            ->paginate(10, ['*'], 'approved_page');

        $completedPayouts = PayoutRequest::with(['user', 'seller', 'processor'])
            ->where('status', PayoutRequest::STATUS_COMPLETED)
            ->latest()
            ->paginate(10, ['*'], 'completed_page');

        $rejectedPayouts = PayoutRequest::with(['user', 'seller', 'processor'])
            ->where('status', PayoutRequest::STATUS_REJECTED)
            ->latest()
            ->paginate(10, ['*'], 'rejected_page');

        // Calculate stats using raw SQL for better performance
        $stats = DB::select("
            SELECT 
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as completed_amount,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as approved_amount
            FROM payout_requests
        ", [
            PayoutRequest::STATUS_PENDING,
            PayoutRequest::STATUS_PENDING,
            PayoutRequest::STATUS_COMPLETED,
            PayoutRequest::STATUS_COMPLETED,
            PayoutRequest::STATUS_APPROVED,
            PayoutRequest::STATUS_APPROVED
        ]);

        // Convert stats to array format
        $statsArray = [
            'pending_count' => (int)$stats[0]->pending_count,
            'pending_amount' => (float)$stats[0]->pending_amount,
            'completed_count' => (int)$stats[0]->completed_count,
            'completed_amount' => (float)$stats[0]->completed_amount,
            'approved_count' => (int)$stats[0]->approved_count,
            'approved_amount' => (float)$stats[0]->approved_amount,
        ];

        return view('admin.payouts.index', [
            'pendingPayouts' => $pendingPayouts,
            'approvedPayouts' => $approvedPayouts,
            'completedPayouts' => $completedPayouts,
            'rejectedPayouts' => $rejectedPayouts,
            'stats' => $statsArray
        ]);
    }

    public function show(PayoutRequest $payout)
    {
        return view('admin.payouts.show', [
            'payout' => $payout->load('user')
        ]);
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

    public function approve(PayoutRequest $payout)
    {
        if ($payout->status !== PayoutRequest::STATUS_PENDING) {
            return back()->with('error', 'This payout request has already been processed.');
        }

        DB::beginTransaction();
        try {
            // Update payout status to approved
            $payout->update([
                'status' => PayoutRequest::STATUS_APPROVED,
                'processed_at' => now(),
                'processed_by' => auth()->id()
            ]);

            DB::commit();
            return back()->with('success', 'Payout request approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payout approval failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve payout request.');
        }
    }

    public function complete(Request $request, PayoutRequest $payout)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'receipt' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        if ($payout->status !== PayoutRequest::STATUS_APPROVED) {
            return back()->with('error', 'Only approved payout requests can be completed.');
        }

        DB::beginTransaction();
        try {
            // Store receipt
            $receiptPath = $request->file('receipt')->store('receipts', 'public');

            // Update payout request
            $payout->update([
                'status' => PayoutRequest::STATUS_COMPLETED,
                'transaction_id' => $request->transaction_id,
                'receipt_path' => $receiptPath,
                'receipt_uploaded_at' => now(),
                'completed_at' => now()
            ]);

            DB::commit();
            return back()->with('success', 'Payout has been marked as completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payout completion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to complete payout.');
        }
    }

    public function reject(Request $request, PayoutRequest $payout)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255'
        ]);

        if ($payout->status !== 'pending') {
            return back()->with('error', 'This payout request has already been processed.');
        }

        DB::beginTransaction();
        try {
            // Update payout status to rejected with reason
            $payout->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'processed_at' => now()
            ]);

            // Get current available balance
            $currentBalance = DB::selectOne("
                SELECT available_balance 
                FROM seller_balances 
                WHERE seller_id = ?
            ", [$payout->seller_id])->available_balance;

            // Restore the amount to available balance
            DB::statement("
                UPDATE seller_balances 
                SET available_balance = ? 
                WHERE seller_id = ?
            ", [$currentBalance + $payout->amount, $payout->seller_id]);

            DB::commit();
            return redirect()->back()->with('success', 'Payout request rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payout rejection failed: ' . $e->getMessage());
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

    public function uploadReceipt(Request $request, PayoutRequest $payout)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($payout->status !== 'approved') {
            return back()->with('error', 'Can only upload receipt for approved payouts.');
        }

        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($payout->receipt_path) {
                Storage::delete($payout->receipt_path);
            }

            // Store new receipt
            $path = $request->file('receipt')->store('receipts', 'public');
            
            $payout->update([
                'receipt_path' => $path,
                'receipt_uploaded_at' => now()
            ]);

            return back()->with('success', 'Receipt uploaded successfully.');
        }

        return back()->with('error', 'No receipt file provided.');
    }
}

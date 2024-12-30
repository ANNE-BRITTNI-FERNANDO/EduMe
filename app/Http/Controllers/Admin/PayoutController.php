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
            ->whereIn('status', ['pending', null])
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        $approvedPayouts = PayoutRequest::with(['user', 'seller', 'processor'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(10, ['*'], 'approved_page');

        $completedPayouts = PayoutRequest::with(['user', 'seller', 'processor'])
            ->where('status', 'completed')
            ->latest()
            ->paginate(10, ['*'], 'completed_page');

        $rejectedPayouts = PayoutRequest::with(['user', 'seller', 'processor'])
            ->where('status', 'rejected')
            ->latest()
            ->paginate(10, ['*'], 'rejected_page');

        // Calculate stats using raw SQL for better performance
        $stats = \Cache::remember('payout_stats', now()->addMinutes(5), function () {
            return DB::select("
                SELECT 
                    SUM(CASE WHEN (status = 'pending' OR status IS NULL) THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN (status = 'pending' OR status IS NULL) THEN amount ELSE 0 END) as pending_amount,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_amount,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved_amount,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                    SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END) as rejected_amount
                FROM payout_requests
            ")[0];
        });

        // Convert stats to array format
        $statsArray = [
            'pending_count' => $stats->pending_count ?? 0,
            'pending_amount' => $stats->pending_amount ?? 0,
            'completed_count' => $stats->completed_count ?? 0,
            'completed_amount' => $stats->completed_amount ?? 0,
            'approved_count' => $stats->approved_count ?? 0,
            'approved_amount' => $stats->approved_amount ?? 0,
            'rejected_count' => $stats->rejected_count ?? 0,
            'rejected_amount' => $stats->rejected_amount ?? 0,
        ];

        return view('admin.payouts.index', compact(
            'pendingPayouts',
            'approvedPayouts',
            'completedPayouts',
            'rejectedPayouts',
            'statsArray'
        ));
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
        try {
            DB::beginTransaction();
            \Log::info('Attempting to approve payout request: ' . $payout->id);

            // Get the payout ID before any operations
            $payoutId = $payout->id;

            // Update using query builder
            $updated = DB::table('payout_requests')
                ->whereId($payoutId)
                ->whereIn('status', ['pending', null])
                ->update([
                    'status' => 'approved',
                    'processed_at' => now(),
                    'processed_by' => auth()->id(),
                    'approved_at' => now(),
                    'updated_at' => now()
                ]);

            \Log::info('Update result: ' . ($updated ? 'success' : 'failed'));

            if (!$updated) {
                DB::rollback();
                \Log::error('Failed to update payout request in database');
                return back()->with('error', 'Failed to update payout request status.');
            }

            // Update seller balance
            $sellerBalance = SellerBalance::where('seller_id', $payout->seller_id)->first();
            if ($sellerBalance) {
                // Deduct from pending balance since it's approved
                $sellerBalance->pending_balance -= $payout->amount;
                $sellerBalance->save();
            }

            // Clear cache
            \Cache::forget('payout_requests');
            \Cache::forget('payout_stats');
            
            DB::commit();
            return redirect()->route('admin.payouts.index')
                ->with('success', 'Payout request approved successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Payout approval failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve payout request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, PayoutRequest $payout)
    {
        try {
            DB::beginTransaction();
            \Log::info('Attempting to reject payout request: ' . $payout->id);

            // Get the payout ID before any operations
            $payoutId = $payout->id;

            // Update using query builder
            $updated = DB::table('payout_requests')
                ->whereId($payoutId)
                ->whereIn('status', ['pending', null])
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                    'processed_at' => now(),
                    'processed_by' => auth()->id(),
                    'updated_at' => now()
                ]);

            \Log::info('Update result: ' . ($updated ? 'success' : 'failed'));

            if (!$updated) {
                DB::rollback();
                \Log::error('Failed to update payout request in database');
                return back()->with('error', 'Failed to update payout request status.');
            }

            // Update seller balance
            $sellerBalance = SellerBalance::where('seller_id', $payout->seller_id)->first();
            if ($sellerBalance) {
                // Add back to available balance since it's rejected
                $sellerBalance->available_balance += $payout->amount;
                $sellerBalance->save();
            }

            // Clear cache
            \Cache::forget('payout_requests');
            \Cache::forget('payout_stats');

            DB::commit();
            return redirect()->route('admin.payouts.index')
                ->with('success', 'Payout request rejected successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Payout rejection failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to reject payout request: ' . $e->getMessage());
        }
    }

    public function complete(Request $request, PayoutRequest $payout)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'receipt' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        DB::beginTransaction();
        try {
            // Get fresh instance from database
            $payout = PayoutRequest::where('id', $payout->id)
                ->whereStatus('approved')
                ->lockForUpdate()
                ->first();

            if (!$payout) {
                DB::rollback();
                return back()->with('error', 'Only approved payout requests can be completed.');
            }

            // Store receipt
            $receiptPath = $request->file('receipt')->store('receipts', 'public');

            // Update payout request
            $payout->update([
                'status' => 'completed',
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

    public function sellerBalances(Request $request)
    {
        $query = User::where('is_seller', true)
            ->withSum('payoutRequests as total_payouts', 'amount')
            ->withCount([
                'payoutRequests as pending_payouts_count' => function($query) {
                    $query->where('status', 'pending');
                }
            ]);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $sellers = $query->paginate(15)->withQueryString();

        return view('admin.payouts.seller-balances', compact('sellers'));
    }

    public function uploadReceipt(Request $request, PayoutRequest $payout)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        // Get fresh instance from database
        $payout = PayoutRequest::where('id', $payout->id)->lockForUpdate()->first();

        if (!$payout || $payout->status !== 'approved') {
            return back()->with('error', 'Receipt can only be uploaded for approved payouts.');
        }

        try {
            // Delete old receipt if exists
            if ($payout->receipt_path && Storage::disk('public')->exists($payout->receipt_path)) {
                Storage::disk('public')->delete($payout->receipt_path);
            }

            // Store new receipt
            $receiptPath = $request->file('receipt')->store('receipts', 'public');

            // Update payout request
            $payout->update([
                'receipt_path' => $receiptPath,
                'receipt_uploaded_at' => now()
            ]);

            return back()->with('success', 'Receipt uploaded successfully.');
        } catch (\Exception $e) {
            \Log::error('Receipt upload failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to upload receipt.');
        }
    }
}

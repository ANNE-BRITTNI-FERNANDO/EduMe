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
    public function index(Request $request)
    {
        // Get all payout requests with their relationships
        $query = PayoutRequest::with(['user', 'seller.sellerBalance', 'processor']);

        // Get total paid amounts for each seller
        $sellerTotalPaid = PayoutRequest::select('seller_id', DB::raw('SUM(amount) as total_paid'))
            ->where('status', 'completed')
            ->groupBy('seller_id')
            ->pluck('total_paid', 'seller_id')
            ->toArray();

        // Apply delivery status filter
        if ($request->filled('delivery_status')) {
            $query->whereHas('orderItems', function($q) use ($request) {
                $q->where('delivery_status', $request->delivery_status);
            });
        }

        // Apply payment status filter
        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'available') {
                $query->whereHas('seller.sellerBalance', function($q) {
                    $q->where('available_balance', '>', 0);
                });
            } elseif ($request->payment_status === 'pending') {
                $query->whereHas('seller.sellerBalance', function($q) {
                    $q->where('pending_balance', '>', 0);
                });
            }
        }

        $pendingPayouts = $query->whereIn('status', ['pending', null])
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
            'statsArray',
            'sellerTotalPaid'
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

    public function approve(PayoutRequest $payoutRequest)
    {
        try {
            DB::beginTransaction();
            
            // Log payout details
            \Log::info('Attempting to approve payout request: ' . $payoutRequest->id);
            \Log::info('Current payout status: ' . $payoutRequest->status);
            \Log::info('Seller ID: ' . $payoutRequest->seller_id);

            // Get fresh instance from database
            $payout = PayoutRequest::where('id', $payoutRequest->id)
                ->whereIn('status', ['pending', null])
                ->lockForUpdate()
                ->first();

            if (!$payout) {
                DB::rollback();
                \Log::error('Payout request not found or not in pending status. ID: ' . $payoutRequest->id);
                return response()->json(['success' => false, 'message' => 'Payout request not found or not in pending status']);
            }

            // Update using query builder
            $updated = DB::table('payout_requests')
                ->whereId($payout->id)
                ->whereIn('status', ['pending', null])
                ->update([
                    'status' => 'approved',
                    'processed_at' => now(),
                    'processed_by' => auth()->id(),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                DB::rollback();
                \Log::error('Failed to update payout request in database');
                return response()->json(['success' => false, 'message' => 'Failed to update payout request status.']);
            }

            // Clear cache
            \Cache::forget('payout_requests');
            \Cache::forget('payout_stats');
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payout request approved successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Payout approval failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to approve payout request: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        try {
            DB::beginTransaction();
            
            // Log payout details
            \Log::info('Attempting to reject payout request: ' . $payoutRequest->id);
            \Log::info('Current payout status: ' . $payoutRequest->status);
            \Log::info('Seller ID: ' . $payoutRequest->seller_id);

            // Get fresh instance from database
            $payout = PayoutRequest::where('id', $payoutRequest->id)
                ->whereIn('status', ['pending', 'approved'])
                ->lockForUpdate()
                ->first();

            if (!$payout) {
                DB::rollback();
                \Log::error('Payout request not found or not in valid status. ID: ' . $payoutRequest->id);
                return response()->json(['success' => false, 'message' => 'Payout request not found or not in valid status']);
            }

            if (!$payout->seller_id) {
                DB::rollback();
                \Log::error('Invalid seller ID for payout: ' . $payoutRequest->id);
                return response()->json(['success' => false, 'message' => 'Invalid seller ID for payout request']);
            }

            // Update using query builder
            $updated = DB::table('payout_requests')
                ->whereId($payout->id)
                ->whereIn('status', ['pending', 'approved'])
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                    'processed_at' => now(),
                    'processed_by' => auth()->id(),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                DB::rollback();
                \Log::error('Failed to update payout request in database');
                return response()->json(['success' => false, 'message' => 'Failed to update payout request status.']);
            }

            // Update seller balance
            $sellerBalance = SellerBalance::where('seller_id', $payout->seller_id)->first();
            if ($sellerBalance) {
                \Log::info('Current seller balance - Available: ' . $sellerBalance->available_balance . ', Pending: ' . $sellerBalance->pending_balance . ' for seller ID: ' . $payout->seller_id);
                
                // Add back to available balance since it's rejected
                $sellerBalance->available_balance += $payout->amount;
                $sellerBalance->pending_balance -= $payout->amount;
                $sellerBalance->save();
                
                \Log::info('Updated seller balance - Available: ' . $sellerBalance->available_balance . ', Pending: ' . $sellerBalance->pending_balance . ' for seller ID: ' . $payout->seller_id);
            } else {
                \Log::warning('Seller balance not found for seller ID: ' . $payout->seller_id);
            }

            // Clear cache
            \Cache::forget('payout_requests');
            \Cache::forget('payout_stats');
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payout request rejected successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Payout rejection failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Failed to reject payout request: ' . $e->getMessage()]);
        }
    }

    public function complete(Request $request, PayoutRequest $payout)
    {
        try {
            DB::beginTransaction();
            \Log::info('Attempting to complete payout request: ' . $payout->id);

            // Get the payout ID before any operations
            $payoutId = $payout->id;

            // Validate proof of payment if provided
            if ($request->hasFile('receipt')) {
                $path = $request->file('receipt')->store('payout-receipts', 'public');
            } else {
                $path = null;
            }

            // Update using query builder
            $updated = DB::table('payout_requests')
                ->whereId($payoutId)
                ->whereIn('status', ['pending', 'approved'])
                ->update([
                    'status' => 'completed',
                    'receipt_path' => $path,
                    'transaction_id' => $request->transaction_id,
                    'completed_at' => now(),
                    'processed_by' => auth()->id(),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                DB::rollback();
                \Log::error('Failed to update payout request in database');
                return response()->json(['success' => false, 'message' => 'Failed to update payout request status.']);
            }

            // Update seller balance
            $sellerBalance = SellerBalance::where('seller_id', $payout->seller_id)->first();
            if ($sellerBalance) {
                // Deduct from pending balance since it's completed
                $sellerBalance->pending_balance -= $payout->amount;
                $sellerBalance->save();
            }

            // Clear cache
            \Cache::forget('payout_requests');
            \Cache::forget('payout_stats');
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payout request completed successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Payout completion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to complete payout request: ' . $e->getMessage()]);
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

    /**
     * Get order details for a payout request
     */
    public function getOrderDetails(PayoutRequest $payout)
    {
        $orderItems = $payout->orderItems()
            ->with(['order', 'item'])
            ->get();

        // Debug info
        \Log::info('Order Items Debug:', $orderItems->map(function($item) {
            return [
                'id' => $item->id,
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'item' => $item->item,
                'raw_item' => $item->getOriginal('item_type')
            ];
        })->toArray());

        $groupedItems = $orderItems->groupBy(function($item) {
            return str_replace(' ', '_', strtolower($item->order->delivery_status ?? 'pending'));
        });

        return view('admin.payouts.partials.order-details', [
            'orderItems' => $groupedItems,
            'payout' => $payout
        ]);
    }

    /**
     * Get delivery verification details
     */
    public function verifyDelivery(PayoutRequest $payout)
    {
        $orderItems = $payout->orderItems()
            ->with(['order', 'item'])
            ->get();

        return view('admin.payouts.partials.delivery-verification', [
            'orderItems' => $orderItems,
            'payout' => $payout
        ]);
    }

    /**
     * Update delivery status for an order
     */
    public function updateDeliveryStatus(Request $request, PayoutRequest $payout)
    {
        $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'status' => 'required|in:pending,warehouse,delivered,disputed'
        ]);

        $orderItem = $payout->orderItems()
            ->findOrFail($request->order_item_id);
            
        // Update the order's delivery status
        $orderItem->order->delivery_status = $request->status;
        $orderItem->order->save();

        // If status is disputed, create a dispute record
        if ($request->status === 'disputed') {
            // Assuming you have a disputes table
            DB::table('disputes')->insert([
                'order_id' => $orderItem->order_id,
                'reason' => $request->reason,
                'reported_by' => auth()->id(),
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['message' => 'Delivery status updated successfully']);
    }
}

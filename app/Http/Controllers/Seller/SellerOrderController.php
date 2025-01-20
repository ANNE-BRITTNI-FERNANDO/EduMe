<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerOrderController extends Controller
{
    public function index()
    {
        $seller = Auth::user();
        
        $orders = Order::whereHas('items', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->with(['user', 'items' => function($query) use ($seller) {
            $query->where('seller_id', $seller->id)
                  ->with('item');
        }])
        ->latest()
        ->paginate(10);

        return view('seller.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $seller = Auth::user();
        
        // Check if the order belongs to this seller
        if (!$order->items()->where('seller_id', $seller->id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        // Load order items for this seller only
        $order->load(['items' => function($query) use ($seller) {
            $query->where('seller_id', $seller->id)
                  ->with('item');
        }, 'user']);

        return view('seller.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $seller = Auth::user();
        
        // Validate seller owns items in this order
        if (!$order->items()->where('seller_id', $seller->id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Order status updated successfully.');
    }

    public function updateDeliveryStatus(Request $request, Order $order)
    {
        try {
            DB::beginTransaction();
            
            $seller = Auth::user();
            
            // Validate seller owns items in this order
            if (!$order->items()->where('seller_id', $seller->id)->exists()) {
                abort(403, 'Unauthorized action.');
            }

            $status = $request->input('status');
            
            // Define valid status transitions
            $validStatuses = [
                'pending' => ['delivered_to_warehouse'],
                'confirmed' => ['delivered_to_warehouse'],
                'processing' => ['delivered_to_warehouse'],
                'delivered_to_warehouse' => ['dispatched'],
                'dispatched' => ['delivered']
            ];

            // Check if the requested status is valid
            if (!isset($validStatuses[$order->delivery_status]) || 
                !in_array($status, $validStatuses[$order->delivery_status])) {
                return back()->with('error', 'Invalid status transition');
            }

            // Get or create seller balance
            $sellerBalance = $seller->sellerBalance;
            if (!$sellerBalance) {
                $sellerBalance = \App\Models\SellerBalance::create([
                    'seller_id' => $seller->id,
                    'available_balance' => 0,
                    'pending_balance' => 0,
                    'total_earned' => 0
                ]);
            }

            // Calculate seller's earnings for this order if not already calculated
            $sellerItems = $order->items()->where('seller_id', $seller->id)->get();
            $earnings = 0;
            foreach ($sellerItems as $item) {
                $earnings += $item->quantity * $item->price;
            }

            // Update the order status and set the corresponding timestamp
            $oldStatus = $order->delivery_status;
            $order->delivery_status = $status;
            
            // Set the corresponding timestamp and update balance
            switch ($status) {
                case 'delivered_to_warehouse':
                    if (!$order->warehouse_confirmed_at) {
                        $order->warehouse_confirmed_at = now();

                        // Update seller's balance only if this is the first time
                        DB::statement("
                            UPDATE seller_balances 
                            SET 
                                available_balance = available_balance + ?,
                                total_earned = total_earned + ?
                            WHERE seller_id = ?
                        ", [$earnings, $earnings, $seller->id]);
                    }
                    break;

                case 'dispatched':
                    $order->dispatched_at = now();
                    break;

                case 'delivered':
                    if (!$order->delivered_at) {
                        $order->delivered_at = now();
                    }
                    break;
            }

            $order->save();
            DB::commit();

            return back()->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error updating delivery status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }
}

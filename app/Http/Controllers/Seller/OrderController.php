<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function updateStatus(Request $request, Order $order)
    {
        $user = auth()->user();
        
        // Validate the status
        $status = $request->input('status', 'delivered_to_warehouse');
        $validStatuses = ['delivered_to_warehouse', 'dispatched', 'delivered'];

        if (!in_array($status, $validStatuses)) {
            return redirect()->back()->with('error', 'Invalid status selected: ' . $status);
        }

        // Check if user is the seller of any items in this order
        if (!$order->items()->where('seller_id', $user->id)->exists()) {
            return redirect()->back()->with('error', 'You are not authorized to update this order.');
        }

        try {
            DB::transaction(function () use ($order, $status, $user) {
                // Update order delivery status
                $order->update([
                    'delivery_status' => $status,
                    'warehouse_confirmed_at' => $status === 'delivered_to_warehouse' ? now() : $order->warehouse_confirmed_at,
                    'dispatched_at' => $status === 'dispatched' ? now() : $order->dispatched_at,
                    'delivered_at' => $status === 'delivered' ? now() : $order->delivered_at
                ]);

                // Create delivery tracking entry
                $order->deliveryTracking()->create([
                    'status' => $status,
                    'description' => "Order status updated to {$status}",
                    'location' => 'Warehouse',
                    'tracked_at' => now()
                ]);

                // Send notification to buyer
                $order->user->notify(new OrderStatusUpdated($order));
            });

            return redirect()->back()->with('success', "Order has been marked as {$status}.");
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\SellerBalanceService;

class OrderController extends Controller
{
    protected $sellerBalanceService;

    public function __construct(SellerBalanceService $sellerBalanceService)
    {
        $this->sellerBalanceService = $sellerBalanceService;
    }

    public function index()
    {
        $orders = Order::whereHas('items', function($query) {
            $query->where('seller_id', auth()->id());
        })
        ->with(['user', 'items' => function($query) {
            $query->where('seller_id', auth()->id())
                  ->with(['item' => function($query) {
                      $query->withoutGlobalScopes();
                  }, 'seller']);
        }])
        ->latest()
        ->paginate(10);

        // Debug the first order's items
        if ($orders->isNotEmpty()) {
            $firstOrder = $orders->first();
            \Log::info('First Order Items:', [
                'order_id' => $firstOrder->id,
                'items' => $firstOrder->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'item_type' => $item->item_type,
                        'item_id' => $item->item_id,
                        'item' => $item->item,
                    ];
                })
            ]);
        }

        return view('seller.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if (!$order->items()->where('seller_id', auth()->id())->exists()) {
            abort(403);
        }

        $order->load(['user', 'items' => function($query) {
            $query->where('seller_id', auth()->id())
                  ->with(['item' => function($query) {
                      $query->withoutGlobalScopes();
                  }, 'seller']);
        }]);

        return view('seller.orders.show', compact('order'));
    }

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
            $previousStatus = $order->delivery_status;

            DB::transaction(function () use ($order, $status, $user, $previousStatus) {
                // Update order delivery status
                $order->update([
                    'delivery_status' => $status,
                    'warehouse_confirmed_at' => $status === 'delivered_to_warehouse' ? now() : $order->warehouse_confirmed_at,
                    'dispatched_at' => $status === 'dispatched' ? now() : $order->dispatched_at,
                    'delivered_at' => $status === 'delivered' ? now() : $order->delivered_at
                ]);

                // Update seller balance
                $this->sellerBalanceService->updateBalanceForOrderStatus($order, $status, $previousStatus);

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

<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Update the order status and set the corresponding timestamp
        $order->delivery_status = $status;
        
        // Set the corresponding timestamp
        switch ($status) {
            case 'delivered_to_warehouse':
                $order->warehouse_confirmed_at = now();
                break;
            case 'dispatched':
                $order->dispatched_at = now();
                break;
            case 'delivered':
                $order->delivered_at = now();
                break;
        }

        $order->save();

        return back()->with('success', 'Order status updated successfully');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.item', 'items.seller'])
            ->select([
                'orders.id',
                'orders.user_id',
                'orders.delivery_status',
                'orders.payment_status',
                DB::raw('ROUND(orders.total_amount - orders.delivery_fee, 0) as total_amount'), // Exclude delivery fee
                'orders.created_at',
                'orders.updated_at'
            ]);

        // Apply filters
        if ($request->filled('seller_id')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->where('seller_id', $request->seller_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('delivery_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Get statistics
        $totalOrders = Order::count();
        // Update pending orders to include all non-completed orders
        $pendingOrders = Order::whereNotIn('delivery_status', ['completed', 'delivered'])->count();
        $completedOrders = Order::whereIn('delivery_status', ['completed', 'delivered'])->count();
        // Calculate total revenue excluding delivery fees
        $totalRevenue = Order::sum(DB::raw('total_amount - delivery_fee'));

        // Get all sellers for the dropdown
        $sellers = User::where('role', 'seller')->get();

        $orders = $query->latest()->paginate(10);

        return view('admin.orders.index', compact(
            'orders',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'totalRevenue',
            'sellers'
        ));
    }

    public function show($id)
    {
        $order = Order::with(['items.item', 'items.seller', 'user'])
            ->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'delivery_status' => 'required|in:pending,processing,completed,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->delivery_status = $request->delivery_status;
        $order->save();

        return redirect()->back()->with('success', 'Order status updated successfully');
    }

    /**
     * Update the status of an order
     */
    public function updateStatusApi(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled,refunded'
        ]);

        $order->update([
            'status' => $request->status
        ]);

        // Notify the user about the status change
        $order->user->notify(new OrderStatusUpdated($order));

        return response()->json([
            'message' => 'Order status updated successfully',
            'status' => $order->status
        ]);
    }
}

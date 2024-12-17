<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\SellerBalanceService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $sellerBalanceService;

    public function __construct(SellerBalanceService $sellerBalanceService)
    {
        $this->sellerBalanceService = $sellerBalanceService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.item' => function($query) {
            $query->withTrashed();
        }, 'items.seller']);

        // Apply filters
        if ($request->filled('seller_id')) {
            $query->whereHas('items', function ($q) use ($request) {
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
            $query->where(function ($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $orders = $query->latest()->paginate(10);
        
        // Get all sellers for the filter dropdown
        $sellers = \App\Models\User::role('seller')->get();

        // Calculate statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('delivery_status', 'pending')->count();
        $completedOrders = Order::whereIn('delivery_status', ['completed', 'delivered'])->count();
        $totalRevenue = Order::whereIn('delivery_status', ['completed', 'delivered'])
            ->sum('total_amount');

        return view('admin.orders.index', compact(
            'orders', 
            'sellers', 
            'totalOrders', 
            'pendingOrders', 
            'completedOrders', 
            'totalRevenue'
        ));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.item' => function($query) {
            $query->withTrashed();
        }, 'items.seller']);
        
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $previousStatus = $order->status;
        
        $order->update([
            'status' => $request->status
        ]);

        try {
            $this->sellerBalanceService->updateBalanceForOrderStatus($order, $request->status, $previousStatus);
            return back()->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to update seller balance: ' . $e->getMessage());
            return back()->with('error', 'Order status updated but failed to update seller balance. Please contact support.');
        }
    }
}

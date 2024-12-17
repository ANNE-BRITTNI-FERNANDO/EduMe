<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Check if user is a seller
        if (!$user->is_seller) {
            return redirect()->route('home')->with('error', 'You need to be a seller to access this page.');
        }

        // Get seller's balance from SellerBalance model
        $sellerBalance = SellerBalance::firstOrCreate(
            ['seller_id' => $user->id],
            [
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0,
                'balance_to_be_paid' => 0,
                'total_delivery_fees_earned' => 0
            ]
        );

        // Get recent orders
        $recentOrders = OrderItem::with(['order', 'item'])
            ->whereHas('order', function($query) {
                $query->whereNotIn('delivery_status', ['cancelled']);
            })
            ->where('seller_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Get order statistics
        $orderStats = OrderItem::where('seller_id', $user->id)
            ->whereHas('order', function($query) {
                $query->whereNotIn('delivery_status', ['cancelled']);
            })
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "pending" THEN 1 ELSE 0 END) as pending_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "processing" THEN 1 ELSE 0 END) as processing_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "completed" THEN 1 ELSE 0 END) as completed_orders')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->first();

        return view('seller.dashboard', compact('sellerBalance', 'recentOrders', 'orderStats'));
    }
}

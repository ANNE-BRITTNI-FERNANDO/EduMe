<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SellerBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get seller balance
        $sellerBalance = SellerBalance::where('seller_id', $user->id)
            ->firstOrCreate(
                ['seller_id' => $user->id],
                [
                    'available_balance' => 0,
                    'pending_balance' => 0,
                    'total_earned' => 0,
                    'balance_to_be_paid' => 0,
                    'total_delivery_fees_earned' => 0
                ]
            );

        // Get total earnings (total_earned from seller_balances)
        $totalEarnings = $sellerBalance->total_earned;

        // Get available balance
        $availableBalance = $sellerBalance->available_balance;

        // Get pending balance
        $pendingBalance = $sellerBalance->pending_balance;

        // Get total products
        $totalProducts = Product::where('user_id', $user->id)->count();

        // Get recent orders through order items, ensuring we get orders for this specific seller
        $recentOrders = Order::select('orders.*')
            ->distinct()
            ->join('order_items', function($join) use ($user) {
                $join->on('orders.id', '=', 'order_items.order_id')
                     ->where('order_items.seller_id', '=', $user->id);
            })
            ->with(['items' => function($query) use ($user) {
                // Only load items belonging to this seller
                $query->where('order_items.seller_id', $user->id)
                      ->with(['item' => function($query) {
                          $query->withTrashed();
                      }]);
            }, 'user'])
            ->orderBy('orders.created_at', 'desc')
            ->take(5)
            ->get();

        // Get order stats for this seller only
        $orderStats = DB::table('order_items')
            ->join('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereNull('orders.deleted_at')
                     ->whereNotIn('orders.delivery_status', ['cancelled']);
            })
            ->where('order_items.seller_id', $user->id)  // Explicitly get from order_items
            ->select([
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "pending" THEN 1 ELSE 0 END) as pending_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "processing" THEN 1 ELSE 0 END) as processing_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "completed" THEN 1 ELSE 0 END) as completed_orders')
            ])
            ->first();

        // Convert to object if null
        $orderStats = $orderStats ?: (object)[
            'total_orders' => 0,
            'pending_orders' => 0,
            'processing_orders' => 0,
            'completed_orders' => 0
        ];

        // Convert currency symbols
        $currencySymbol = 'LKR';

        return view('seller.dashboard', compact(
            'sellerBalance',
            'recentOrders',
            'orderStats',
            'totalEarnings',
            'availableBalance',
            'pendingBalance',
            'totalProducts',
            'currencySymbol'
        ));
    }
}

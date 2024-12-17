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
        $seller = auth()->user();
        
        // Check if user has any products (this makes them a seller)
        $hasProducts = Product::where('user_id', $seller->id)->exists();
        
        if (!$hasProducts) {
            return redirect()->route('home')->with('error', 'You need to add products to become a seller.');
        }

        // Get seller's balance from SellerBalance model
        $sellerBalance = SellerBalance::firstOrCreate(
            ['seller_id' => $seller->id],
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
            ->where('seller_id', $seller->id)
            ->latest()
            ->take(5)
            ->get();

        // Get order statistics
        $orderStats = OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', function($query) {
                $query->whereNotIn('delivery_status', ['cancelled']);
            })
            ->selectRaw('
                COUNT(*) as total_orders,
                COUNT(CASE WHEN orders.delivery_status = "pending" THEN 1 END) as pending_orders,
                COUNT(CASE WHEN orders.delivery_status IN ("completed", "delivered", "confirmed") THEN 1 END) as completed_orders
            ')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->first();

        return view('seller.dashboard', compact('sellerBalance', 'recentOrders', 'orderStats'));
    }
}

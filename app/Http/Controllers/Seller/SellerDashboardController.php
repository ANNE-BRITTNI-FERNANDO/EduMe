<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerBalance;
use App\Models\PayoutRequest;
use App\Models\SellerRating;
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

        // Get or create seller balance
        $sellerBalance = SellerBalance::firstOrCreate(
            ['seller_id' => $user->id],
            [
                'total_earned' => 0,
                'balance_to_be_paid' => 0,
                'available_balance' => 0,
                'pending_balance' => 0
            ]
        );

        // Recalculate balance to ensure it's up to date
        $sellerBalance->recalculateBalance();
        $sellerBalance->refresh();

        // Get recent orders
        $recentOrders = OrderItem::with(['order.user', 'item'])
            ->where('order_items.seller_id', $user->id)
            ->whereHas('order', function($query) {
                $query->whereNotIn('delivery_status', ['cancelled'])
                      ->whereNull('deleted_at');
            })
            ->latest('order_items.created_at')
            ->take(5)
            ->get()
            ->unique('order_id');

        // Get order statistics
        $orderStats = DB::table('order_items')
            ->join('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereNull('orders.deleted_at')
                     ->whereNotIn('orders.delivery_status', ['cancelled']);
            })
            ->where('order_items.seller_id', $user->id)
            ->select([
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "pending" THEN 1 ELSE 0 END) as pending_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "processing" THEN 1 ELSE 0 END) as processing_orders'),
                DB::raw('SUM(CASE WHEN orders.delivery_status = "completed" THEN 1 ELSE 0 END) as completed_orders')
            ])
            ->first();

        // Handle null stats
        $orderStats = $orderStats ?: (object)[
            'total_orders' => 0,
            'pending_orders' => 0,
            'processing_orders' => 0,
            'completed_orders' => 0
        ];

        // Get seller rating stats
        $ratingStats = [
            'average' => SellerRating::where('seller_id', $user->id)->avg('rating') ?? 0,
            'total' => SellerRating::where('seller_id', $user->id)->count(),
            'distribution' => [
                5 => SellerRating::where('seller_id', $user->id)->where('rating', 5)->count(),
                4 => SellerRating::where('seller_id', $user->id)->where('rating', 4)->count(),
                3 => SellerRating::where('seller_id', $user->id)->where('rating', 3)->count(),
                2 => SellerRating::where('seller_id', $user->id)->where('rating', 2)->count(),
                1 => SellerRating::where('seller_id', $user->id)->where('rating', 1)->count(),
            ]
        ];

        // Get recent reviews
        $recentReviews = SellerRating::with(['user', 'order.items'])
            ->where('seller_id', $user->id)
            ->latest()
            ->paginate(5);

        // Add currency symbol
        $currencySymbol = 'LKR';

        return view('seller.dashboard', compact(
            'sellerBalance',
            'recentOrders',
            'orderStats',
            'ratingStats',
            'currencySymbol',
            'recentReviews'
        ));
    }
}

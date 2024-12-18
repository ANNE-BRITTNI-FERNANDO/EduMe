<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\SellerBalance;
use Illuminate\Support\Facades\Auth;

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

        // Get recent orders through order items
        $recentOrders = Order::whereHas('items', function($query) use ($user) {
            $query->where('seller_id', $user->id);
        })
        ->with(['user', 'items' => function($query) use ($user) {
            $query->where('seller_id', $user->id)
                  ->with(['item' => function($query) {
                      $query->withTrashed(); // Include soft deleted items
                  }]);
        }])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

        // Get order stats
        $orderStats = (object)[
            'total_orders' => Order::whereHas('items', function($query) use ($user) {
                $query->where('seller_id', $user->id);
            })->count()
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

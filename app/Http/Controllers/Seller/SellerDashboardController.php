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
        
        // Get seller balance
        $balance = SellerBalance::where('seller_id', $seller->id)
            ->firstOrCreate(
                ['seller_id' => $seller->id],
                [
                    'available_balance' => 0,
                    'pending_balance' => 0,
                    'total_earned' => 0,
                    'balance_to_be_paid' => 0,
                    'total_delivery_fees_earned' => 0
                ]
            );
        
        // Get recent orders for this seller only
        $recentOrders = Order::whereHas('items', function($query) use ($seller) {
            $query->where('seller_id', $seller->id);
        })
        ->with(['user', 'items' => function($query) use ($seller) {
            $query->where('seller_id', $seller->id)
                  ->with('item');
        }])
        ->latest()
        ->take(5)
        ->get();

        // Calculate total earnings using the seller's order items
        $totalEarnings = OrderItem::where('seller_id', $seller->id)
            ->sum(\DB::raw('price * quantity'));

        // Get total products
        $totalProducts = $seller->products()->count();

        return view('seller.dashboard', compact('recentOrders', 'totalEarnings', 'totalProducts', 'balance'));
    }
}

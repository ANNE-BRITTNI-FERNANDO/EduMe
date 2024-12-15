<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PayoutRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        try {
            // Get pending products
            $products = Product::query()
                ->where(function($query) {
                    $query->where('is_approved', false)
                          ->where('is_rejected', false);
                })
                ->with('user')
                ->latest()
                ->get();

            // Get rejected payouts
            $rejectedPayouts = PayoutRequest::where('status', 'rejected')
                ->with('user')
                ->latest()
                ->get();

            // Initialize order statistics with default values
            $totalOrders = 0;
            $pendingOrders = 0;
            $completedOrders = 0;
            $totalRevenue = 0;
            $recentOrders = collect();

            // Get order statistics
            try {
                $totalOrders = Order::count();
                $pendingOrders = Order::whereIn('delivery_status', ['pending', 'processing'])->count();
                $completedOrders = Order::where('delivery_status', 'completed')->count();
                $totalRevenue = Order::where('delivery_status', 'completed')->sum('total_amount');

                // Get recent orders
                $recentOrders = Order::with(['user', 'items.item'])
                    ->latest()
                    ->take(10)
                    ->get();
            } catch (\Exception $e) {
                // Log the error but continue with default values
                \Log::error('Error fetching order statistics: ' . $e->getMessage());
            }

            return view('admin.dashboard', compact(
                'products',
                'rejectedPayouts',
                'totalOrders',
                'pendingOrders',
                'completedOrders',
                'totalRevenue',
                'recentOrders'
            ));
        } catch (\Exception $e) {
            \Log::error('Error in admin dashboard: ' . $e->getMessage());
            return view('admin.dashboard')->with('error', 'Error loading dashboard data');
        }
    }

    public function approved()
    {
        $products = Product::where('is_approved', true)
            ->with('user')
            ->latest()
            ->get();
        
        return view('admin.approved', ['products' => $products]);
    }
}

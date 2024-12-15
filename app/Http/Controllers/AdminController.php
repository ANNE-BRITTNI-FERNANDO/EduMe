<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get order statistics
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');

        // Get recent orders
        $recentOrders = Order::with(['user', 'seller'])
            ->latest()
            ->take(5)
            ->get();

        // Get pending products
        $pendingProducts = Product::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'totalRevenue',
            'recentOrders',
            'pendingProducts'
        ));
    }

    public function approve($id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'approved';  // Change status to 'approved'
        $product->save();

        return redirect()->route('admin.dashboard')->with('success', 'Product approved!');
    }

    public function reject($id)
    {
        $product = Product::findOrFail($id);
        $product->is_approved = false;
        $product->is_rejected = true;
        $product->save();

        return redirect()->route('admin.dashboard')->with('status', 'Product rejected successfully.');
    }
}

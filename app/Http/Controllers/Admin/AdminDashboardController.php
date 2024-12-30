<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PayoutRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
                
                // Calculate total revenue from all order items regardless of delivery status
                $totalRevenue = DB::table('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->sum(DB::raw('order_items.price * order_items.quantity'));

                // Get recent orders
                $recentOrders = Order::with(['user', 'items.item'])
                    ->latest()
                    ->take(5)
                    ->get();

                // Get monthly revenue data for the past 6 months
                $monthlyRevenue = DB::table('orders')
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.created_at', '>=', Carbon::now()->subMonths(6))
                    ->select(
                        DB::raw('MONTH(orders.created_at) as month'),
                        DB::raw('YEAR(orders.created_at) as year'),
                        DB::raw('SUM(order_items.price * order_items.quantity) as total')
                    )
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->get();

                // Get order status counts
                $orderStatusCounts = Order::select('delivery_status', DB::raw('count(*) as count'))
                    ->groupBy('delivery_status')
                    ->get()
                    ->pluck('count', 'delivery_status')
                    ->toArray();

            } catch (\Exception $e) {
                \Log::error('Error fetching order statistics: ' . $e->getMessage());
            }

            return view('admin.dashboard', compact(
                'products',
                'rejectedPayouts',
                'totalOrders',
                'pendingOrders',
                'completedOrders',
                'totalRevenue',
                'recentOrders',
                'monthlyRevenue',
                'orderStatusCounts'
            ));
        } catch (\Exception $e) {
            \Log::error('Error in admin dashboard: ' . $e->getMessage());
            return view('admin.dashboard')->with('error', 'Error loading dashboard data');
        }
    }

    public function approved()
    {
        // Only get products that have been explicitly approved
        $products = Product::where('is_approved', true)
            ->where('is_rejected', false)
            ->where('status', '=', 'approved')  // Only get products with status 'approved'
            ->with(['user', 'productImages'])
            ->latest()
            ->get();
        
        return view('admin.approved', ['products' => $products]);
    }

    public function rejected()
    {
        $products = Product::where('is_rejected', true)
            ->with('user')
            ->latest()
            ->get();
        
        return view('admin.rejected', ['products' => $products]);
    }

    public function pending()
    {
        $query = Product::where(function($query) {
            $query->where(function($q) {
                // Get products that are pending initial review
                $q->where('status', 'pending')
                  ->where('is_approved', false)
                  ->where('is_rejected', false);
            })->orWhere(function($q) {
                // Get resubmitted products
                $q->where('status', 'resubmitted') // Products that have been resubmitted will have this status
                  ->where('is_approved', false)
                  ->where('is_rejected', false);
            });
        })->with(['user', 'productImages']);

        // Add some debugging
        \Log::info('Pending Products Query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => $query->count()
        ]);

        // Apply sorting
        $sort = request('sort', 'latest');
        switch ($sort) {
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // Apply category filter if provided
        if (request()->has('category') && request('category') !== 'all') {
            $query->where('category', request('category'));
        }

        $products = $query->paginate(10);
        
        // Debug the results
        \Log::info('Pending Products:', [
            'total' => $products->total(),
            'products' => $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'status' => $product->status,
                    'is_approved' => $product->is_approved,
                    'is_rejected' => $product->is_rejected,
                    'rejection_reason' => $product->rejection_reason
                ];
            })
        ]);

        $categories = Product::distinct()->pluck('category');

        return view('admin.pending', [
            'products' => $products,
            'categories' => $categories,
            'currentSort' => $sort,
            'currentCategory' => request('category', 'all')
        ]);
    }

    public function approveProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Update product status
            $product->status = 'approved';
            $product->is_approved = true;
            $product->is_rejected = false;
            $product->rejection_reason = null;
            $product->rejection_note = null;
            $product->approved_at = now();
            $product->save();

            // Notify the seller
            $product->user->notify(new \App\Notifications\ProductApproved($product));

            return redirect()->back()->with('success', 'Product has been approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error approving product: ' . $e->getMessage());
        }
    }

    public function rejectProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Validate rejection reason
            request()->validate([
                'rejection_reason' => 'required|string',
                'rejection_note' => 'nullable|string'
            ]);

            // Update product status
            $product->status = 'rejected';
            $product->is_approved = false;
            $product->is_rejected = true;
            $product->rejection_reason = request('rejection_reason');
            $product->rejection_note = request('rejection_note');
            $product->save();

            // Notify the seller
            try {
                $product->user->notify(new \App\Notifications\ProductRejected($product));
            } catch (\Exception $e) {
                \Log::error('Failed to send product rejection notification: ' . $e->getMessage());
            }

            return redirect()->back()->with('success', 'Product has been rejected.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error rejecting product: ' . $e->getMessage());
        }
    }
}

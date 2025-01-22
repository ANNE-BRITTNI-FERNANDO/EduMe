<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                return redirect()->route('login');
            }
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
            $totalOrders = Order::count();
            $totalProducts = Product::count();
            $totalUsers = User::count();
            $pendingOrders = Order::where('status', 'pending')->count();
            $pendingDonations = DonationItem::where('status', 'pending')->count();
            $recentOrders = Order::with(['user', 'items.item'])
                ->latest()
                ->take(5)
                ->get();

            $recentActivities = collect();
            
            // Get recent orders
            $recentOrderActivities = Order::select('id', 'created_at')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($order) {
                    return (object)[
                        'type' => 'order',
                        'description' => "New order #$order->id created",
                        'created_at' => $order->created_at
                    ];
                });
            $recentActivities = $recentActivities->concat($recentOrderActivities);

            // Get recent products
            $recentProductActivities = Product::select('id', 'product_name', 'created_at')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($product) {
                    return (object)[
                        'type' => 'product',
                        'description' => "New product '$product->product_name' added",
                        'created_at' => $product->created_at
                    ];
                });
            $recentActivities = $recentActivities->concat($recentProductActivities);

            // Get recent users
            $recentUserActivities = User::select('id', 'name', 'created_at')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($user) {
                    return (object)[
                        'type' => 'user',
                        'description' => "New user '$user->name' registered",
                        'created_at' => $user->created_at
                    ];
                });
            $recentActivities = $recentActivities->concat($recentUserActivities);

            // Sort all activities by created_at
            $recentActivities = $recentActivities->sortByDesc('created_at')->take(10);

            $rejectedPayouts = PayoutRequest::where('status', 'rejected')
                ->with('user')
                ->latest()
                ->take(5)
                ->get();

            return view('admin.dashboard', compact(
                'totalRevenue',
                'totalOrders',
                'totalProducts',
                'totalUsers',
                'pendingOrders',
                'pendingDonations',
                'recentOrders',
                'recentActivities',
                'rejectedPayouts'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading dashboard: ' . $e->getMessage());
        }
    }

    public function approveProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'approved';
        $product->rejection_reason = null;
        $product->rejection_note = null;
        $product->approved_at = now();
        $product->save();

        return redirect()->back()->with('success', 'Product approved successfully!');
    }

    /**
     * Display pending products (legacy route).
     */
    public function pending(Request $request)
    {
        $currentSort = $request->query('sort', 'latest');
        $categories = [
            'textbooks' => 'Textbooks',
            'stationery' => 'Stationery',
            'devices' => 'Electronic Devices',
            'other' => 'Other'
        ];
        
        $query = Product::where('status', 'pending')
                       ->with('user');
        
        // Apply category filter
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        // Apply sorting
        switch ($currentSort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default: // 'latest'
                $query->latest();
                break;
        }
        
        $products = $query->paginate(10);
    
        return view('admin.pending', compact('products', 'currentSort', 'categories'));
    }

    /**
     * Display pending products.
     */
    public function pendingProducts()
    {
        return $this->pending();
    }
    
    /**
     * Display approved products.
     */
    public function approved(Request $request)
    {
        $currentSort = $request->query('sort', 'latest');
        $categories = Product::CATEGORIES;
        
        // Get all sellers who have approved products
        $sellers = User::where('is_seller', true)
            ->whereHas('products', function($query) {
                $query->where('status', 'approved');
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        $query = Product::where('status', 'approved')
                       ->with('user');
        
        // Apply category filter
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        // Apply seller filter
        if ($request->has('seller') && $request->seller !== 'all') {
            $query->where('user_id', $request->seller);
        }
        
        // Apply sorting
        switch ($currentSort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default: // 'latest'
                $query->latest();
                break;
        }
        
        $products = $query->paginate(10)->withQueryString();
    
        return view('admin.approved', compact('products', 'currentSort', 'categories', 'sellers'));
    }

    /**
     * Display approved products (legacy route).
     */
    public function approvedProducts(Request $request)
    {
        return $this->approved($request);
    }
    
    /**
     * Display rejected products.
     */
    public function rejected(Request $request)
    {
        $currentSort = $request->query('sort', 'latest');
        $categories = [
            'textbooks' => 'Textbooks',
            'stationery' => 'Stationery',
            'devices' => 'Electronic Devices',
            'other' => 'Other'
        ];
        
        $query = Product::where('status', 'rejected')
                       ->with('user');
        
        // Apply category filter
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        // Apply sorting
        switch ($currentSort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default: // 'latest'
                $query->latest();
                break;
        }
        
        $products = $query->paginate(10);
    
        return view('admin.rejected', compact('products', 'currentSort', 'categories'));
    }

    /**
     * Display rejected products (new route).
     */
    public function rejectedProducts(Request $request)
    {
        return $this->rejected($request);
    }
    
    /**
     * Reject a product.
     */
    public function rejectProduct(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
            'rejection_note' => 'nullable|string'
        ]);

        $product = Product::findOrFail($id);
        $product->status = 'rejected';
        $product->is_approved = false;
        $product->is_rejected = true;
        $product->rejection_reason = $request->rejection_reason;
        $product->rejection_note = $request->rejection_note;
        $product->approved_at = null;
        $product->save();

        return redirect()->back()->with('success', 'Product rejected successfully!');
    }



    public function viewDonations()
    {
        $itemDonations = DonationItem::with('user')->latest()->get();

        return view('admin.donations.index', compact('itemDonations'));
    }

    public function donationDetails($id)
    {
        $donation = DonationItem::with('user')->findOrFail($id);
        
        return view('admin.donations.show', compact('donation'));
    }
}

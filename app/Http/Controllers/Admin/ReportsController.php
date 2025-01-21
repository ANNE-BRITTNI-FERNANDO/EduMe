<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Bundle;
use App\Models\Visit;
use App\Models\Cart;
use App\Models\Message;
use Carbon\Carbon;
use App\Models\SellerRating as Rating;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function userActivity()
    {
        $data = [
            'total_users' => User::count(),
            'active_users' => User::whereHas('buyerOrders', function($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })->count(),
            'cart_items' => Cart::count(),
            'wishlist_items' => 0, // Removed as Wishlist is not implemented
            'total_orders' => Product::where('is_sold', true)->count(),
            'recent_orders' => Product::with('buyer')
                ->where('is_sold', true)
                ->latest('updated_at')
                ->take(10)
                ->get(),
            'monthly_orders' => Product::select(DB::raw('MONTH(updated_at) as month'), DB::raw('COUNT(*) as count'))
                ->whereYear('updated_at', date('Y'))
                ->where('is_sold', true)
                ->groupBy('month')
                ->get()
        ];

        return view('admin.reports.user-activity', $data);
    }

    public function productListings(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        $data = [
            'products_by_location' => Product::select('users.location', DB::raw('COUNT(*) as count'))
                ->join('users', 'products.user_id', '=', 'users.id')
                ->whereBetween('products.created_at', [$start_date, $end_date])
                ->groupBy('users.location')
                ->get(),
            'top_sellers' => Product::select('seller_id', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy('seller_id')
                ->orderBy('count', 'desc')
                ->take(5)
                ->get(),
            'total_products' => Product::whereBetween('created_at', [$start_date, $end_date])->count(),
            'approved_products' => Product::where('is_approved', true)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count(),
            'pending_products' => Product::where('is_approved', false)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count(),
            'rejected_products' => Product::where('is_rejected', true)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count(),
            'recent_products' => Product::with('seller')
                ->whereBetween('created_at', [$start_date, $end_date])
                ->latest()
                ->take(10)
                ->get(),
            'monthly_products' => Product::select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy('month')
                ->get(),
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

        return view('admin.reports.product-listings', $data);
    }

    public function incomeSummary()
    {
        $data = [
            'total_revenue' => Product::where('is_sold', true)->sum('price'),
            'monthly_revenue' => Product::select(
                    DB::raw('MONTH(updated_at) as month'),
                    DB::raw('SUM(price) as revenue')
                )
                ->where('is_sold', true)
                ->whereYear('updated_at', date('Y'))
                ->groupBy('month')
                ->get(),
            'recent_transactions' => Product::with(['buyer', 'seller'])
                ->where('is_sold', true)
                ->latest('updated_at')
                ->take(10)
                ->get(),
            'average_order_value' => Product::where('is_sold', true)
                ->avg('price')
        ];

        return view('admin.reports.income-summary', $data);
    }

    public function sellerReviews()
    {
        $data = [
            'average_rating' => SellerRating::avg('rating'),
            'rating_distribution' => SellerRating::select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->get(),
            'top_sellers' => SellerRating::select('seller_id', DB::raw('AVG(rating) as average_rating'))
                ->groupBy('seller_id')
                ->orderByDesc('average_rating')
                ->take(10)
                ->with('seller')
                ->get(),
            'recent_reviews' => SellerRating::with(['user', 'seller'])
                ->latest()
                ->take(10)
                ->get()
        ];

        $pdf = Pdf::loadView('admin.reports.seller-reviews', $data);
        return $pdf->download('seller-reviews-report.pdf');
    }

    public function userSummary()
    {
        $data = [
            'user_roles' => User::select('role', DB::raw('COUNT(*) as count'))
                ->groupBy('role')
                ->get(),
            'top_buyers' => User::whereHas('buyerOrders')
                ->withCount('buyerOrders')
                ->orderBy('buyerOrders_count', 'desc')
                ->take(10)
                ->get(),
            'top_sellers' => User::whereHas('orders_as_seller')
                ->withCount('orders_as_seller')
                ->orderBy('orders_as_seller_count', 'desc')
                ->take(10)
                ->get(),
            'new_users' => User::where('created_at', '>=', now()->subDays(30))
                ->count(),
            'monthly_registrations' => User::select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get()
        ];

        return view('admin.reports.user-summary', $data);
    }

    public function sales(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        try {
            // Get revenue trend data combining products and bundles
            $products_revenue = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(price) as revenue')
                )
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy(DB::raw('DATE(created_at)'));

            // Debug log products revenue query
            \Log::info('Products Revenue Query', [
                'sql' => $products_revenue->toSql(),
                'bindings' => $products_revenue->getBindings()
            ]);

            $bundles_revenue = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(price) as revenue')
                )
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy(DB::raw('DATE(created_at)'));

            // Debug log bundles revenue query
            \Log::info('Bundles Revenue Query', [
                'sql' => $bundles_revenue->toSql(),
                'bindings' => $bundles_revenue->getBindings()
            ]);

            $revenue_trend = $bundles_revenue->union($products_revenue)
                ->orderBy('date')
                ->get();

            // Debug log revenue trend results
            \Log::info('Revenue Trend Results', [
                'count' => $revenue_trend->count(),
                'data' => $revenue_trend->toArray()
            ]);

            // Group and sum the revenues by date
            $revenue_trend = collect($revenue_trend)->groupBy('date')
                ->map(function ($items) {
                    return [
                        'date' => $items[0]['date'],
                        'revenue' => $items->sum('revenue')
                    ];
                })
                ->values()
                ->collect();

            // Debug log after grouping
            \Log::info('Revenue Trend After Grouping', [
                'data' => $revenue_trend->toArray()
            ]);

            // Get location-based sales data combining products and bundles
            $products_by_location = Product::select(
                'users.location',
                DB::raw('COUNT(products.id) as count'),
                DB::raw('SUM(products.price) as total')
            )
                ->join('users', 'products.user_id', '=', 'users.id')
                ->where(function($query) {
                    $query->where('products.is_sold', true)
                        ->orWhere('products.status', 'sold');
                })
                ->where('products.is_approved', true)
                ->where('products.is_rejected', false)
                ->whereNotNull('users.location')
                ->whereBetween('products.created_at', [$start_date, $end_date])
                ->groupBy('users.location');

            // Debug log products by location query
            \Log::info('Products by Location Query', [
                'sql' => $products_by_location->toSql(),
                'bindings' => $products_by_location->getBindings()
            ]);

            $bundles_by_location = Bundle::select(
                'users.location',
                DB::raw('COUNT(bundles.id) as count'),
                DB::raw('SUM(bundles.price) as total')
            )
                ->join('users', 'bundles.user_id', '=', 'users.id')
                ->where(function($query) {
                    $query->where('bundles.is_sold', true)
                        ->orWhere('bundles.status', 'sold');
                })
                ->whereNotNull('users.location')
                ->whereBetween('bundles.created_at', [$start_date, $end_date])
                ->groupBy('users.location');

            // Debug log bundles by location query
            \Log::info('Bundles by Location Query', [
                'sql' => $bundles_by_location->toSql(),
                'bindings' => $bundles_by_location->getBindings()
            ]);

            $location_sales = $bundles_by_location->union($products_by_location)
                ->orderBy('total', 'desc')
                ->get();

            // Debug log location sales results
            \Log::info('Location Sales Results', [
                'count' => $location_sales->count(),
                'data' => $location_sales->toArray()
            ]);

            // Group and sum the location sales
            $location_sales = collect($location_sales)->groupBy('location')
                ->map(function ($items) {
                    return [
                        'location' => $items[0]['location'],
                        'count' => $items->sum('count'),
                        'total' => $items->sum('total')
                    ];
                })
                ->values()
                ->collect();

            // Debug log after grouping
            \Log::info('Location Sales After Grouping', [
                'data' => $location_sales->toArray()
            ]);

            // Get recent orders combining products and bundles
            $recent_products = Product::with('user')
                ->where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->select('id', 'user_id', 'price', 'created_at as updated_at', DB::raw("'product' as type"));

            $recent_bundles = Bundle::with('user')
                ->where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$start_date, $end_date])
                ->select('id', 'user_id', 'price', 'created_at as updated_at', DB::raw("'bundle' as type"));

            $recent_orders = $recent_bundles->union($recent_products)
                ->latest('updated_at')
                ->take(10)
                ->get();

            // Debug log recent orders
            \Log::info('Recent Orders', [
                'count' => $recent_orders->count(),
                'data' => $recent_orders->toArray()
            ]);

            // Calculate total revenue and order counts
            $products_revenue = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('price');

            $bundles_revenue = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('price');

            $total_revenue = $products_revenue + $bundles_revenue;

            $products_count = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $bundles_count = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $total_orders = $products_count + $bundles_count;

            // Debug log totals
            \Log::info('Totals', [
                'products_revenue' => $products_revenue,
                'bundles_revenue' => $bundles_revenue,
                'total_revenue' => $total_revenue,
                'products_count' => $products_count,
                'bundles_count' => $bundles_count,
                'total_orders' => $total_orders
            ]);

            // Get previous period for comparison
            $days_diff = $end_date->diffInDays($start_date);
            $previous_start = $start_date->copy()->subDays($days_diff);
            $previous_end = $start_date->copy()->subDay();

            $previous_products_revenue = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->sum('price');

            $previous_bundles_revenue = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->sum('price');

            $previous_revenue = $previous_products_revenue + $previous_bundles_revenue;

            $previous_products_count = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->count();

            $previous_bundles_count = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->count();

            $previous_orders = $previous_products_count + $previous_bundles_count;

            // Calculate growth percentages
            $revenue_growth = $previous_revenue > 0 ? (($total_revenue - $previous_revenue) / $previous_revenue) * 100 : 0;
            $orders_growth = $previous_orders > 0 ? (($total_orders - $previous_orders) / $previous_orders) * 100 : 0;

            // Debug log growth
            \Log::info('Growth', [
                'previous_revenue' => $previous_revenue,
                'previous_orders' => $previous_orders,
                'revenue_growth' => $revenue_growth,
                'orders_growth' => $orders_growth
            ]);

            return view('admin.reports.sales', compact(
                'start_date',
                'end_date',
                'total_revenue',
                'total_orders',
                'revenue_growth',
                'orders_growth',
                'revenue_trend',
                'location_sales',
                'recent_orders'
            ));

        } catch (\Exception $e) {
            // Log any errors
            \Log::error('Sales Report Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return view with error
            return view('admin.reports.sales', [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_revenue' => 0,
                'total_orders' => 0,
                'revenue_growth' => 0,
                'orders_growth' => 0,
                'revenue_trend' => collect(),
                'location_sales' => collect(),
                'recent_orders' => collect(),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function downloadSalesPDF(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        try {
            // Get revenue trend data
            $products_revenue = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(price) as revenue')
                )
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy(DB::raw('DATE(created_at)'));

            $bundles_revenue = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(price) as revenue')
                )
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy(DB::raw('DATE(created_at)'));

            $revenue_trend = $bundles_revenue->union($products_revenue)
                ->orderBy('date')
                ->get();

            // Group and sum the revenues by date
            $revenue_trend = collect($revenue_trend)->groupBy('date')
                ->map(function ($items) {
                    return [
                        'date' => $items[0]['date'],
                        'revenue' => $items->sum('revenue')
                    ];
                })
                ->values();

            // Get location-based sales data
            $products_by_location = Product::select(
                'users.location',
                DB::raw('COUNT(products.id) as count'),
                DB::raw('SUM(products.price) as total')
            )
                ->join('users', 'products.user_id', '=', 'users.id')
                ->where(function($query) {
                    $query->where('products.is_sold', true)
                        ->orWhere('products.status', 'sold');
                })
                ->where('products.is_approved', true)
                ->where('products.is_rejected', false)
                ->whereNotNull('users.location')
                ->whereBetween('products.created_at', [$start_date, $end_date])
                ->groupBy('users.location');

            $bundles_by_location = Bundle::select(
                'users.location',
                DB::raw('COUNT(bundles.id) as count'),
                DB::raw('SUM(bundles.price) as total')
            )
                ->join('users', 'bundles.user_id', '=', 'users.id')
                ->where(function($query) {
                    $query->where('bundles.is_sold', true)
                        ->orWhere('bundles.status', 'sold');
                })
                ->whereNotNull('users.location')
                ->whereBetween('bundles.created_at', [$start_date, $end_date])
                ->groupBy('users.location');

            $location_sales = $bundles_by_location->union($products_by_location)
                ->orderBy('total', 'desc')
                ->get();

            // Group and sum the location sales
            $location_sales = collect($location_sales)->groupBy('location')
                ->map(function ($items) {
                    return [
                        'location' => $items[0]['location'],
                        'count' => $items->sum('count'),
                        'total' => $items->sum('total')
                    ];
                })
                ->values();

            // Get recent orders
            $recent_products = Product::with('user')
                ->where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->select('id', 'user_id', 'price', 'created_at as updated_at', DB::raw("'product' as type"));

            $recent_bundles = Bundle::with('user')
                ->where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$start_date, $end_date])
                ->select('id', 'user_id', 'price', 'created_at as updated_at', DB::raw("'bundle' as type"));

            $recent_orders = $recent_bundles->union($recent_products)
                ->latest('updated_at')
                ->take(10)
                ->get();

            // Calculate totals
            $products_revenue = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('price');

            $bundles_revenue = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('price');

            $total_revenue = $products_revenue + $bundles_revenue;

            $products_count = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $bundles_count = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $total_orders = $products_count + $bundles_count;

            // Get previous period for comparison
            $days_diff = $end_date->diffInDays($start_date);
            $previous_start = $start_date->copy()->subDays($days_diff);
            $previous_end = $start_date->copy()->subDay();

            $previous_products_revenue = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->sum('price');

            $previous_bundles_revenue = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->sum('price');

            $previous_revenue = $previous_products_revenue + $previous_bundles_revenue;

            $previous_products_count = Product::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->count();

            $previous_bundles_count = Bundle::where(function($query) {
                    $query->where('is_sold', true)
                        ->orWhere('status', 'sold');
                })
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->count();

            $previous_orders = $previous_products_count + $previous_bundles_count;

            // Calculate growth percentages
            $revenue_growth = $previous_revenue > 0 ? (($total_revenue - $previous_revenue) / $previous_revenue) * 100 : 0;
            $orders_growth = $previous_orders > 0 ? (($total_orders - $previous_orders) / $previous_orders) * 100 : 0;

            $pdf = PDF::loadView('admin.reports.pdf.sales', compact(
                'start_date',
                'end_date',
                'total_revenue',
                'total_orders',
                'revenue_growth',
                'orders_growth',
                'revenue_trend',
                'location_sales',
                'recent_orders'
            ));

            return $pdf->download('sales_report.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    public function users(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        // Calculate active users more comprehensively
        $active_users = User::where(function($query) use ($start_date, $end_date) {
            // Users who made a purchase
            $query->whereHas('buyerOrders', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            })
            // Users who listed products
            ->orWhereHas('products', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            })
            // Users who created bundles
            ->orWhereHas('bundles', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            })
            // Users who added items to cart
            ->orWhereHas('cart', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            })
            // Users who rated sellers
            ->orWhereHas('ratings', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            });
        })->count();

        // Get previous period metrics for comparison
        $days_diff = $end_date->diffInDays($start_date);
        $previous_start = $start_date->copy()->subDays($days_diff);
        $previous_end = $start_date->copy()->subDay();

        $previous_active_users = User::where(function($query) use ($previous_start, $previous_end) {
            $query->whereHas('buyerOrders', function($q) use ($previous_start, $previous_end) {
                $q->whereBetween('created_at', [$previous_start, $previous_end]);
            })
            ->orWhereHas('products', function($q) use ($previous_start, $previous_end) {
                $q->whereBetween('created_at', [$previous_start, $previous_end]);
            })
            ->orWhereHas('bundles', function($q) use ($previous_start, $previous_end) {
                $q->whereBetween('created_at', [$previous_start, $previous_end]);
            })
            ->orWhereHas('cart', function($q) use ($previous_start, $previous_end) {
                $q->whereBetween('created_at', [$previous_start, $previous_end]);
            })
            ->orWhereHas('ratings', function($q) use ($previous_start, $previous_end) {
                $q->whereBetween('created_at', [$previous_start, $previous_end]);
            });
        })->count();

        $active_users_growth = $previous_active_users > 0 ? 
            (($active_users - $previous_active_users) / $previous_active_users) * 100 : 0;

        // Get detailed breakdown of user activity
        $activity_breakdown = [
            'buyers' => User::whereHas('buyerOrders', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            })->count(),
            'sellers' => User::whereHas('products', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            })->count(),
            'cart_users' => User::whereHas('cart', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            })->count(),
            'raters' => User::whereHas('ratings', function($q) use ($start_date, $end_date) {
                $q->whereBetween('created_at', [$start_date, $end_date]);
            })->count()
        ];

        $total_users = User::count();
        $new_users = User::whereBetween('created_at', [$start_date, $end_date])->count();
        
        // Get previous period for comparison
        $days_diff = $end_date->diffInDays($start_date);
        $previous_start = $start_date->copy()->subDays($days_diff);
        $previous_end = $start_date->copy()->subDay();
        
        $previous_new_users = User::whereBetween('created_at', [$previous_start, $previous_end])->count();
        $user_growth = $previous_new_users > 0 ? (($new_users - $previous_new_users) / $previous_new_users) * 100 : 0;

        $daily_new_users = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $total_active = User::has('products')->count();
        $period_active = Product::whereBetween('created_at', [$start_date, $end_date])
            ->select('user_id')
            ->distinct()
            ->count();
        
        $engagement_rate = $total_users > 0 ? ($period_active / $total_users) * 100 : 0;

        return view('admin.reports.users', compact(
            'start_date',
            'end_date',
            'total_users',
            'new_users',
            'user_growth',
            'active_users',
            'active_users_growth',
            'daily_new_users',
            'engagement_rate',
            'total_active',
            'period_active',
            'activity_breakdown'
        ));
    }

    public function products(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        try {
            // Get total products and bundles
            $total_products = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->count();

            $total_bundles = Bundle::count();

            // Get new products and bundles in period
            $new_products = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $new_bundles = Bundle::whereBetween('created_at', [$start_date, $end_date])
                ->count();

            // Get previous period for comparison
            $days_diff = $end_date->diffInDays($start_date);
            $previous_start = $start_date->copy()->subDays($days_diff);
            $previous_end = $start_date->copy()->subDay();

            $previous_new_products = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->count();

            $previous_new_bundles = Bundle::whereBetween('created_at', [$previous_start, $previous_end])
                ->count();

            // Calculate growth
            $products_growth = $previous_new_products > 0 ? (($new_products - $previous_new_products) / $previous_new_products) * 100 : 0;
            $bundles_growth = $previous_new_bundles > 0 ? (($new_bundles - $previous_new_bundles) / $previous_new_bundles) * 100 : 0;

            // Get sold items
            $sold_products = Product::where('is_sold', true)
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $sold_bundles = Bundle::where('is_sold', true)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            // Get revenue
            $total_product_revenue = Product::where('is_sold', true)
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('price');

            $total_bundle_revenue = Bundle::where('is_sold', true)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('price');

            // Get daily new products
            $daily_new_products = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $daily_new_bundles = Bundle::whereBetween('created_at', [$start_date, $end_date])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get category distribution
            $category_distribution = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->select('category', DB::raw('COUNT(*) as count'))
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->get();

            // Get recent products and bundles
            $recent_products = Product::with('user')
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->latest()
                ->take(5)
                ->get(['id', 'product_name', 'category', 'price', 'is_sold', 'user_id']);

            $recent_bundles = Bundle::with('seller')
                ->whereBetween('created_at', [$start_date, $end_date])
                ->latest()
                ->take(5)
                ->get(['id', 'bundle_name', 'price', 'is_sold', 'user_id']);

            return view('admin.reports.products', compact(
                'start_date',
                'end_date',
                'total_products',
                'total_bundles',
                'new_products',
                'new_bundles',
                'products_growth',
                'bundles_growth',
                'sold_products',
                'sold_bundles',
                'total_product_revenue',
                'total_bundle_revenue',
                'daily_new_products',
                'daily_new_bundles',
                'category_distribution',
                'recent_products',
                'recent_bundles'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating report: ' . $e->getMessage());
        }
    }

    public function sellers(Request $request)
    {
        [$startDate, $endDate] = $this->getDateRange($request);
        $dateRange = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => $endDate->diffInDays($startDate)
        ];

        // Get daily new sellers data for the chart based on first product upload
        $daily_new_sellers = DB::table('products')
            ->select(DB::raw('DATE(MIN(created_at)) as date'), DB::raw('COUNT(DISTINCT user_id) as count'))
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Basic seller metrics - now based on products table
        $total_sellers = DB::table('products')
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->distinct('user_id')
            ->count('user_id');

        $active_sellers = DB::table('products')
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        // Calculate average products per seller
        $total_products = Product::where('is_approved', true)
            ->where('is_rejected', false)
            ->count();
        $avg_products_per_seller = $total_sellers > 0 ? $total_products / $total_sellers : 0;

        // New sellers in period - based on first product upload
        $new_sellers = DB::table('products')
            ->select('user_id')
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('user_id')
            ->havingRaw('MIN(created_at) >= ?', [$startDate])
            ->count();

        // Calculate seller growth based on first product upload
        $previous_period_start = Carbon::parse($startDate)->subDays($dateRange['days']);
        $previous_new_sellers = DB::table('products')
            ->select('user_id')
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->whereBetween('created_at', [$previous_period_start, $startDate])
            ->groupBy('user_id')
            ->havingRaw('MIN(created_at) >= ?', [$previous_period_start])
            ->count();
        
        $sellers_growth = $previous_new_sellers > 0 ? 
            (($new_sellers - $previous_new_sellers) / $previous_new_sellers) * 100 : 0;

        // Get top performing sellers
        $top_sellers = User::where('is_seller', true)
            ->withCount(['products as sales' => function($query) use ($startDate, $endDate) {
                $query->where('is_sold', true)
                    ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['products as revenue' => function($query) use ($startDate, $endDate) {
                $query->where('is_sold', true)
                    ->whereBetween('created_at', [$startDate, $endDate]);
            }], 'price')
            ->withAvg(['ratings as rating' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'rating')
            ->withCount(['ratings as total_ratings' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->having('sales', '>', 0)
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        // Get recent reviews
        $recent_reviews = Rating::with(['user', 'seller'])
            ->whereHas('seller', function($query) {
                $query->where('is_seller', true);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->take(5)
            ->get();

        // Seller engagement metrics
        $highly_active_threshold = 5; // sellers with more than 5 products
        $moderately_active_threshold = 2; // sellers with 2-5 products

        $seller_engagement = [
            'highly_active' => User::where('is_seller', true)
                ->withCount(['products' => function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->having('products_count', '>=', $highly_active_threshold)
                ->count(),
            'moderately_active' => User::where('is_seller', true)
                ->withCount(['products' => function($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }])
                ->having('products_count', '>=', $moderately_active_threshold)
                ->having('products_count', '<', $highly_active_threshold)
                ->count()
        ];

        // Calculate average seller rating and total reviews
        $ratings = Rating::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('seller', function($query) {
                $query->where('is_seller', true);
            })
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        $avg_seller_rating = $ratings->avg_rating ?? 0;
        $total_seller_reviews = $ratings->total_reviews ?? 0;

        // Calculate rating distribution
        $total_ratings = Rating::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('seller', function($query) {
                $query->where('is_seller', true);
            })
            ->count();

        $rating_distribution = Rating::whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('seller', function($query) {
                $query->where('is_seller', true);
            })
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->map(function($item) use ($total_ratings) {
                $item->percentage = $total_ratings > 0 ? ($item->count / $total_ratings) * 100 : 0;
                return $item;
            });

        // Calculate average revenue per seller
        $avg_revenue_per_seller = $this->formatCurrency(
            DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.created_at', '>=', $startDate)
                ->where('orders.created_at', '<=', $endDate)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('order_items.seller_id', 'users.id')
                        ->where('users.is_seller', true);
                })
                ->selectRaw('ROUND(AVG(order_items.price * order_items.quantity), 2) as avg_revenue')
                ->value('avg_revenue') ?? 0
        );

        // Calculate average order value
        $avg_order_value = $this->formatCurrency(
            DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.created_at', '>=', $startDate)
                ->where('orders.created_at', '<=', $endDate)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('order_items.seller_id', 'users.id')
                        ->where('users.is_seller', true);
                })
                ->selectRaw('ROUND(AVG(order_items.price * order_items.quantity + order_items.delivery_fee_share), 2) as avg_value')
                ->value('avg_value') ?? 0
        );

        // Performance metrics
        $seller_metrics = [
            'response_rate' => $this->calculateResponseRate($startDate, $endDate),
            'fulfillment_rate' => $this->calculateFulfillmentRate($startDate, $endDate),
            'satisfaction_rate' => $this->calculateSatisfactionRate($startDate, $endDate),
            'ontime_delivery_rate' => $this->calculateOnTimeDeliveryRate($startDate, $endDate)
        ];

        // Get top sellers with formatted revenue
        $top_sellers = $top_sellers->map(function($seller) {
            $seller->formatted_revenue = $this->formatCurrency($seller->revenue);
            return $seller;
        });

        return view('admin.reports.sellers', compact(
            'total_sellers',
            'active_sellers',
            'new_sellers',
            'sellers_growth',
            'seller_engagement',
            'avg_seller_rating',
            'total_seller_reviews',
            'avg_revenue_per_seller',
            'avg_order_value',
            'seller_metrics',
            'dateRange',
            'avg_products_per_seller',
            'rating_distribution',
            'top_sellers',
            'recent_reviews',
            'daily_new_sellers'
        ))->with('days_in_period', $dateRange['days']);
    }

    private function calculateResponseRate($startDate, $endDate)
    {
        // Calculate response rate based on messages responded within 24 hours
        $total_messages = Message::join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->where('conversations.seller_id', '!=', 'messages.sender_id')
            ->whereBetween('messages.created_at', [$startDate, $endDate])
            ->count();

        $responded_messages = Message::join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->where('conversations.seller_id', '=', 'messages.sender_id')
            ->whereRaw('TIMESTAMPDIFF(HOUR, messages.created_at, messages.read_at) <= 24')
            ->whereBetween('messages.created_at', [$startDate, $endDate])
            ->count();

        return $total_messages > 0 ? ($responded_messages / $total_messages) * 100 : 0;
    }

    private function calculateFulfillmentRate($startDate, $endDate)
    {
        $total_orders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $fulfilled_orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        return $total_orders > 0 ? ($fulfilled_orders / $total_orders) * 100 : 0;
    }

    private function calculateSatisfactionRate($startDate, $endDate)
    {
        $total_ratings = Rating::whereBetween('created_at', [$startDate, $endDate])->count();
        $positive_ratings = Rating::whereBetween('created_at', [$startDate, $endDate])
            ->where('rating', '>=', 4)
            ->count();

        return $total_ratings > 0 ? ($positive_ratings / $total_ratings) * 100 : 0;
    }

    private function calculateOnTimeDeliveryRate($startDate, $endDate)
    {
        $total_deliveries = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('delivered_at')
            ->count();
        
        $ontime_deliveries = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('delivered_at')
            ->whereRaw('delivered_at <= DATE_ADD(created_at, INTERVAL 3 DAY)') // Assuming 3-day delivery window
            ->count();

        return $total_deliveries > 0 ? ($ontime_deliveries / $total_deliveries) * 100 : 0;
    }

    private function formatCurrency($amount)
    {
        return 'LKR ' . number_format($amount, 2);
    }

    public function downloadReport(Request $request, $type)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        switch ($type) {
            case 'sales':
                $data = [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'total_revenue' => Product::where('is_sold', true)
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->sum('price'),
                    'total_orders' => Product::where('is_sold', true)
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->count(),
                    'orders' => Product::with(['user'])
                        ->where('is_sold', true)
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->latest('created_at')
                        ->get(),
                    'location_sales' => Product::select(
                        'users.location',
                        DB::raw('COUNT(products.id) as count'),
                        DB::raw('SUM(products.price) as total')
                    )
                        ->join('users', 'products.user_id', '=', 'users.id')
                        ->where('products.is_sold', true)
                        ->whereNotNull('users.location')
                        ->whereBetween('products.created_at', [$start_date, $end_date])
                        ->groupBy('users.location')
                        ->get(),
                    'revenue_trend' => collect()
                ];

                $products_revenue = Product::where('is_sold', true)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('SUM(price) as revenue')
                    )
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->get();

                $bundles_revenue = Bundle::where('is_sold', true)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('SUM(price) as revenue')
                    )
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->get();

                // Combine and group revenue by date
                $revenue_trend = collect([...$products_revenue, ...$bundles_revenue])
                    ->groupBy('date')
                    ->map(function ($items) {
                        return [
                            'date' => $items[0]['date'],
                            'revenue' => $items->sum('revenue')
                        ];
                    })
                    ->values();

                $data['revenue_trend'] = $revenue_trend;

                $pdf = PDF::loadView('admin.reports.pdf.sales', $data);
                return $pdf->download('sales-report-' . $start_date->format('Y-m-d') . '-to-' . $end_date->format('Y-m-d') . '.pdf');

            case 'users':
                $total_users = User::count();
                $new_users = User::whereBetween('created_at', [$start_date, $end_date])->count();
                
                $days_diff = $end_date->diffInDays($start_date);
                $previous_start = $start_date->copy()->subDays($days_diff);
                $previous_end = $start_date->copy()->subDay();
                
                $previous_new_users = User::whereBetween('created_at', [$previous_start, $previous_end])->count();
                $user_growth = $previous_new_users > 0 ? (($new_users - $previous_new_users) / $previous_new_users) * 100 : 0;

                $active_users = User::where(function($query) use ($start_date, $end_date) {
                    // Users who made a purchase
                    $query->whereHas('buyerOrders', function($q) use ($start_date, $end_date) {
                        $q->whereBetween('created_at', [$start_date, $end_date]);
                    })
                    // Users who listed products
                    ->orWhereHas('products', function($q) use ($start_date, $end_date) {
                        $q->whereBetween('created_at', [$start_date, $end_date]);
                    })
                    // Users who created bundles
                    ->orWhereHas('bundles', function($q) use ($start_date, $end_date) {
                        $q->whereBetween('created_at', [$start_date, $end_date]);
                    })
                    // Users who added items to cart
                    ->orWhereHas('cart', function($q) use ($start_date, $end_date) {
                        $q->whereBetween('created_at', [$start_date, $end_date]);
                    })
                    // Users who rated sellers
                    ->orWhereHas('ratings', function($q) use ($start_date, $end_date) {
                        $q->whereBetween('created_at', [$start_date, $end_date]);
                    });
                })->count();

                $previous_active_users = User::where(function($query) use ($previous_start, $previous_end) {
                    $query->whereHas('buyerOrders', function($q) use ($previous_start, $previous_end) {
                        $q->whereBetween('created_at', [$previous_start, $previous_end]);
                    })
                    ->orWhereHas('products', function($q) use ($previous_start, $previous_end) {
                        $q->whereBetween('created_at', [$previous_start, $previous_end]);
                    })
                    ->orWhereHas('bundles', function($q) use ($previous_start, $previous_end) {
                        $q->whereBetween('created_at', [$previous_start, $previous_end]);
                    })
                    ->orWhereHas('cart', function($q) use ($previous_start, $previous_end) {
                        $q->whereBetween('created_at', [$previous_start, $previous_end]);
                    })
                    ->orWhereHas('ratings', function($q) use ($previous_start, $previous_end) {
                        $q->whereBetween('created_at', [$previous_start, $previous_end]);
                    });
                })->count();

                $active_users_growth = $previous_active_users > 0 ? 
                    (($active_users - $previous_active_users) / $previous_active_users) * 100 : 0;

                $daily_new_users = User::select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as count'))
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $total_active = User::has('products')->count();
                $period_active = Product::whereBetween('created_at', [$start_date, $end_date])
                    ->select('user_id')
                    ->distinct()
                    ->count();
                
                $engagement_rate = $total_users > 0 ? ($period_active / $total_users) * 100 : 0;

                $data = compact(
                    'start_date',
                    'end_date',
                    'total_users',
                    'new_users',
                    'user_growth',
                    'active_users',
                    'active_users_growth',
                    'daily_new_users',
                    'engagement_rate',
                    'total_active',
                    'period_active'
                );

                $pdf = PDF::loadView('admin.reports.pdf.users', $data);
                return $pdf->download('users-report-' . $start_date->format('Y-m-d') . '-to-' . $end_date->format('Y-m-d') . '.pdf');

            case 'products':
                $total_products = Product::count();
                $total_bundles = Bundle::count();
                $new_products = Product::whereBetween('created_at', [$start_date, $end_date])->count();
                $new_bundles = Bundle::whereBetween('created_at', [$start_date, $end_date])->count();
                
                $days_diff = $end_date->diffInDays($start_date);
                $previous_start = $start_date->copy()->subDays($days_diff);
                $previous_end = $start_date->copy()->subDay();
                
                $previous_new_products = Product::whereBetween('created_at', [$previous_start, $previous_end])->count();
                $previous_new_bundles = Bundle::whereBetween('created_at', [$previous_start, $previous_end])->count();
                
                $products_growth = $previous_new_products > 0 ? (($new_products - $previous_new_products) / $previous_new_products) * 100 : 0;
                $bundles_growth = $previous_new_bundles > 0 ? (($new_bundles - $previous_new_bundles) / $previous_new_bundles) * 100 : 0;

                $sold_products = Product::where('is_sold', true)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->count();

                $sold_bundles = Bundle::where('is_sold', true)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->count();

                $total_product_revenue = Product::where('is_sold', true)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->sum('price');

                $total_bundle_revenue = Bundle::where('is_sold', true)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->sum('price');

                $daily_new_products = Product::select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as count'))
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $daily_new_bundles = Bundle::select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as count'))
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $category_distribution = Product::where('is_approved', true)
                    ->where('is_rejected', false)
                    ->select('category', DB::raw('COUNT(*) as count'))
                    ->groupBy('category')
                    ->orderBy('count', 'desc')
                    ->get();

                $recent_products = Product::with('user')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->latest()
                    ->take(10)
                    ->get();

                $recent_bundles = Bundle::with('seller')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->latest()
                    ->take(10)
                    ->get();

                $data = compact(
                    'start_date',
                    'end_date',
                    'total_products',
                    'total_bundles',
                    'new_products',
                    'new_bundles',
                    'products_growth',
                    'bundles_growth',
                    'sold_products',
                    'sold_bundles',
                    'total_product_revenue',
                    'total_bundle_revenue',
                    'daily_new_products',
                    'daily_new_bundles',
                    'category_distribution',
                    'recent_products',
                    'recent_bundles'
                );

                $pdf = PDF::loadView('admin.reports.pdf.products', $data);
                return $pdf->download('products-report-' . $start_date->format('Y-m-d') . '-to-' . $end_date->format('Y-m-d') . '.pdf');

            case 'sellers':
                // Get total and new sellers
                $total_sellers = User::where('is_seller', true)->count();
                $new_sellers = User::where('is_seller', true)
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->count();

                // Calculate growth rate
                $days_diff = $end_date->diffInDays($start_date);
                $previous_start = $start_date->copy()->subDays($days_diff);
                $previous_end = $start_date->copy()->subDay();
                
                $previous_new_sellers = User::where('is_seller', true)
                    ->whereBetween('created_at', [$previous_start, $previous_end])
                    ->count();
                
                $sellers_growth = $previous_new_sellers > 0 ? 
                    (($new_sellers - $previous_new_sellers) / $previous_new_sellers) * 100 : 0;

                // Get active sellers (those with sold items)
                $active_sellers = User::where('is_seller', true)
                    ->whereHas('products', function($query) use ($start_date, $end_date) {
                        $query->where('is_sold', true)
                            ->whereBetween('updated_at', [$start_date, $end_date]);
                    })
                    ->orWhereHas('bundles', function($query) use ($start_date, $end_date) {
                        $query->where('is_sold', true)
                            ->whereBetween('updated_at', [$start_date, $end_date]);
                    })
                    ->count();

                // Get seller ratings data
                $average_rating = SellerRating::avg('rating') ?? 0;
                
                // Get rating distribution
                $rating_distribution = SellerRating::select('rating', DB::raw('COUNT(*) as count'))
                    ->groupBy('rating')
                    ->orderBy('rating')
                    ->get();

                // Get top sellers
                $top_sellers = User::where('is_seller', true)
                    ->withCount(['products as products_sold' => function($query) {
                        $query->where('is_sold', true);
                    }])
                    ->withCount(['bundles as bundles_sold' => function($query) {
                        $query->where('is_sold', true);
                    }])
                    ->withSum(['products as product_revenue' => function($query) use ($start_date, $end_date) {
                        $query->where('is_sold', true)
                            ->whereBetween('updated_at', [$start_date, $end_date]);
                    }], 'price')
                    ->withSum(['bundles as bundle_revenue' => function($query) use ($start_date, $end_date) {
                        $query->where('is_sold', true)
                            ->whereBetween('updated_at', [$start_date, $end_date]);
                    }], 'price')
                    ->orderByRaw('COALESCE(product_revenue, 0) + COALESCE(bundle_revenue, 0) DESC')
                    ->take(10)
                    ->get();

                // Get daily new sellers trend
                $daily_new_sellers = User::where('is_seller', true)
                    ->select(
                        DB::raw('DATE(created_at) as date'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                // Get recent sellers
                $recent_sellers = User::where('is_seller', true)
                    ->withCount('products')
                    ->withCount('bundles')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->latest()
                    ->take(10)
                    ->get();

                $data = compact(
                    'start_date',
                    'end_date',
                    'total_sellers',
                    'new_sellers',
                    'sellers_growth',
                    'active_sellers',
                    'average_rating',
                    'rating_distribution',
                    'top_sellers',
                    'daily_new_sellers',
                    'recent_sellers'
                );

                $pdf = PDF::loadView('admin.reports.pdf.sellers', $data);
                return $pdf->download('sellers-report-' . $start_date->format('Y-m-d') . '-to-' . $end_date->format('Y-m-d') . '.pdf');

            default:
                abort(404);
        }
    }

    public function downloadProductsPDF(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        try {
            // Get total products and bundles
            $total_products = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->count();

            $total_bundles = Bundle::count();

            // Get new products and bundles in period
            $new_products = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $new_bundles = Bundle::whereBetween('created_at', [$start_date, $end_date])
                ->count();

            // Get previous period for comparison
            $days_diff = $end_date->diffInDays($start_date);
            $previous_start = $start_date->copy()->subDays($days_diff);
            $previous_end = $start_date->copy()->subDay();

            $previous_new_products = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$previous_start, $previous_end])
                ->count();

            $previous_new_bundles = Bundle::whereBetween('created_at', [$previous_start, $previous_end])
                ->count();

            // Calculate growth
            $products_growth = $previous_new_products > 0 ? (($new_products - $previous_new_products) / $previous_new_products) * 100 : 0;
            $bundles_growth = $previous_new_bundles > 0 ? (($new_bundles - $previous_new_bundles) / $previous_new_bundles) * 100 : 0;

            // Get sold items
            $sold_products = Product::where('is_sold', true)
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $sold_bundles = Bundle::where('is_sold', true)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            // Get revenue
            $total_product_revenue = Product::where('is_sold', true)
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('price');

            $total_bundle_revenue = Bundle::where('is_sold', true)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('price');

            // Get daily new products
            $daily_new_products = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $daily_new_bundles = Bundle::whereBetween('created_at', [$start_date, $end_date])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Get category distribution
            $category_distribution = Product::where('is_approved', true)
                ->where('is_rejected', false)
                ->select('category', DB::raw('COUNT(*) as count'))
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->get();

            // Get top sellers
            $top_products = Product::with('user')
                ->where('is_sold', true)
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->orderBy('price', 'desc')
                ->take(5)
                ->get();

            $top_bundles = Bundle::with('user')
                ->where('is_sold', true)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->orderBy('price', 'desc')
                ->take(5)
                ->get();

            $pdf = PDF::loadView('admin.reports.pdf.products', compact(
                'start_date',
                'end_date',
                'total_products',
                'total_bundles',
                'new_products',
                'new_bundles',
                'products_growth',
                'bundles_growth',
                'sold_products',
                'sold_bundles',
                'total_product_revenue',
                'total_bundle_revenue',
                'daily_new_products',
                'daily_new_bundles',
                'category_distribution',
                'top_products',
                'top_bundles'
            ));

            return $pdf->download('products_analytics.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    private function getDateRange(Request $request)
    {
        $period = $request->get('period', 'today');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        if ($start_date && $end_date) {
            return [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay()
            ];
        }

        $now = Carbon::now();
        
        switch ($period) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'yesterday':
                return [
                    $now->copy()->subDay()->startOfDay(),
                    $now->copy()->subDay()->endOfDay()
                ];
            case 'this_week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'last_week':
                return [
                    $now->copy()->subWeek()->startOfWeek(),
                    $now->copy()->subWeek()->endOfWeek()
                ];
            case 'this_month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'last_month':
                return [
                    $now->copy()->subMonth()->startOfMonth(),
                    $now->copy()->subMonth()->endOfMonth()
                ];
            case 'this_year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            default:
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
        }
    }
}

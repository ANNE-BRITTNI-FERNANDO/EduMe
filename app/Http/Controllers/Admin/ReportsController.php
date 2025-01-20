<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Visit;
use App\Models\Cart;
use Carbon\Carbon;
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
            'total_orders' => Order::count(),
            'recent_orders' => Order::with('buyer')
                ->latest()
                ->take(10)
                ->get(),
            'monthly_orders' => Order::select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get()
        ];

        return view('admin.reports.user-activity', $data);
    }

    public function productListings(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        $data = [
            'products_by_category' => Product::select('category', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy('category')
                ->get(),
            'products_by_location' => Product::select('users.location', DB::raw('COUNT(*) as count'))
                ->join('users', 'products.user_id', '=', 'users.id')
                ->whereBetween('products.created_at', [$start_date, $end_date])
                ->groupBy('users.location')
                ->get(),
            'top_categories' => Product::select('category', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$start_date, $end_date])
                ->groupBy('category')
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
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'monthly_revenue' => Order::select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->where('status', 'completed')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->get(),
            'recent_transactions' => Order::with(['buyer', 'seller'])
                ->where('status', 'completed')
                ->latest()
                ->take(10)
                ->get(),
            'payment_methods' => Order::select('payment_method', DB::raw('COUNT(*) as count'))
                ->where('status', 'completed')
                ->groupBy('payment_method')
                ->get(),
            'average_order_value' => Order::where('status', 'completed')
                ->avg('total_amount')
        ];

        return view('admin.reports.income-summary', $data);
    }

    public function sellerReviews()
    {
        $data = [
            'average_rating' => Review::avg('rating'),
            'rating_distribution' => Review::select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->get(),
            'top_sellers' => Review::select('seller_id', DB::raw('AVG(rating) as average_rating'))
                ->groupBy('seller_id')
                ->orderByDesc('average_rating')
                ->take(10)
                ->with('seller')
                ->get(),
            'recent_reviews' => Review::with(['user', 'seller'])
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

        // Calculate total revenue and order counts
        $total_revenue = Order::whereBetween('created_at', [$start_date, $end_date])
            ->where('status', 'completed')
            ->sum('total_amount');

        $total_orders = Order::whereBetween('created_at', [$start_date, $end_date])->count();

        // Get previous period for comparison
        $days_diff = $end_date->diffInDays($start_date);
        $previous_start = $start_date->copy()->subDays($days_diff);
        $previous_end = $start_date->copy()->subDay();

        $previous_revenue = Order::whereBetween('created_at', [$previous_start, $previous_end])
            ->where('status', 'completed')
            ->sum('total_amount');

        $previous_orders = Order::whereBetween('created_at', [$previous_start, $previous_end])->count();

        // Calculate growth percentages
        $revenue_growth = $previous_revenue > 0 ? (($total_revenue - $previous_revenue) / $previous_revenue) * 100 : 0;
        $orders_growth = $previous_orders > 0 ? (($total_orders - $previous_orders) / $previous_orders) * 100 : 0;

        // Get revenue trend data
        $revenue_trend = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as revenue')
        )
            ->whereBetween('created_at', [$start_date, $end_date])
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get category sales data
        $category_sales = Product::select(
            'category',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(price) as total')
        )
            ->where('is_sold', true)
            ->whereBetween('updated_at', [$start_date, $end_date])
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        // Get payment methods distribution
        $payment_methods = Order::select('payment_method', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();

        // Get daily sales distribution
        $daily_sales = Order::select(
            DB::raw('DATE_FORMAT(created_at, "%a") as day'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('day')
            ->orderBy(DB::raw('FIELD(day, "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun")'))
            ->get();

        // Get recent orders
        $recent_orders = Order::with(['user'])
            ->whereBetween('created_at', [$start_date, $end_date])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.reports.sales', compact(
            'start_date',
            'end_date',
            'total_revenue',
            'total_orders',
            'revenue_growth',
            'orders_growth',
            'revenue_trend',
            'category_sales',
            'payment_methods',
            'daily_sales',
            'recent_orders'
        ));
    }

    public function users(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        $total_users = User::count();
        $new_users = User::whereBetween('created_at', [$start_date, $end_date])->count();
        
        // Get previous period for comparison
        $days_diff = $end_date->diffInDays($start_date);
        $previous_start = $start_date->copy()->subDays($days_diff);
        $previous_end = $start_date->copy()->subDay();
        
        $previous_new_users = User::whereBetween('created_at', [$previous_start, $previous_end])->count();
        $user_growth = $previous_new_users > 0 ? (($new_users - $previous_new_users) / $previous_new_users) * 100 : 0;

        // Active users (users who have placed orders)
        $active_users = Order::whereBetween('created_at', [$start_date, $end_date])
            ->distinct('user_id')
            ->count('user_id');

        $previous_active_users = Order::whereBetween('created_at', [$previous_start, $previous_end])
            ->distinct('user_id')
            ->count('user_id');

        $active_users_growth = $previous_active_users > 0 ? 
            (($active_users - $previous_active_users) / $previous_active_users) * 100 : 0;

        // Daily new users
        $daily_new_users = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get user engagement metrics
        $total_active = User::has('orders')->count();
        $period_active = Order::whereBetween('created_at', [$start_date, $end_date])
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
            'period_active'
        ));
    }

    public function products(Request $request)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        // Total products and new products
        $total_products = Product::count();
        $new_products = Product::whereBetween('created_at', [$start_date, $end_date])->count();
        
        // Get previous period for comparison
        $days_diff = $end_date->diffInDays($start_date);
        $previous_start = $start_date->copy()->subDays($days_diff);
        $previous_end = $start_date->copy()->subDay();
        
        $previous_new_products = Product::whereBetween('created_at', [$previous_start, $previous_end])->count();
        $products_growth = $previous_new_products > 0 ? (($new_products - $previous_new_products) / $previous_new_products) * 100 : 0;

        // Sales metrics
        $sold_products = Product::where('is_sold', true)
            ->whereBetween('updated_at', [$start_date, $end_date])
            ->count();

        $total_revenue = Product::where('is_sold', true)
            ->whereBetween('updated_at', [$start_date, $end_date])
            ->sum('price');

        // Category distribution
        $category_distribution = Product::select('category', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('category')
            ->orderByDesc('count')
            ->get();

        // Daily new products
        $daily_new_products = Product::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$start_date, $end_date])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent products
        $recent_products = Product::with('user')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.reports.products', compact(
            'start_date',
            'end_date',
            'total_products',
            'new_products',
            'products_growth',
            'sold_products',
            'total_revenue',
            'category_distribution',
            'daily_new_products',
            'recent_products'
        ));
    }

    public function downloadReport(Request $request, $type)
    {
        [$start_date, $end_date] = $this->getDateRange($request);

        switch ($type) {
            case 'sales':
                $data = [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'total_revenue' => Order::whereBetween('created_at', [$start_date, $end_date])
                        ->where('status', 'completed')
                        ->sum('total_amount'),
                    'total_orders' => Order::whereBetween('created_at', [$start_date, $end_date])
                        ->count(),
                    'orders' => Order::with(['user'])
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->latest()
                        ->get(),
                    'category_sales' => Product::select(
                        'category',
                        DB::raw('COUNT(*) as count'),
                        DB::raw('SUM(price) as total')
                    )
                        ->where('is_sold', true)
                        ->whereBetween('updated_at', [$start_date, $end_date])
                        ->groupBy('category')
                        ->orderBy('total', 'desc')
                        ->get(),
                    'payment_methods' => Order::select('payment_method', DB::raw('COUNT(*) as count'))
                        ->whereBetween('created_at', [$start_date, $end_date])
                        ->where('status', 'completed')
                        ->groupBy('payment_method')
                        ->get()
                ];

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

                $active_users = Order::whereBetween('created_at', [$start_date, $end_date])
                    ->distinct('user_id')
                    ->count('user_id');

                $previous_active_users = Order::whereBetween('created_at', [$previous_start, $previous_end])
                    ->distinct('user_id')
                    ->count('user_id');

                $active_users_growth = $previous_active_users > 0 ? 
                    (($active_users - $previous_active_users) / $previous_active_users) * 100 : 0;

                $daily_new_users = User::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count'))
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $total_active = User::has('orders')->count();
                $period_active = Order::whereBetween('created_at', [$start_date, $end_date])
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
                $new_products = Product::whereBetween('created_at', [$start_date, $end_date])->count();
                
                $days_diff = $end_date->diffInDays($start_date);
                $previous_start = $start_date->copy()->subDays($days_diff);
                $previous_end = $start_date->copy()->subDay();
                
                $previous_new_products = Product::whereBetween('created_at', [$previous_start, $previous_end])->count();
                $products_growth = $previous_new_products > 0 ? (($new_products - $previous_new_products) / $previous_new_products) * 100 : 0;

                $sold_products = Product::where('is_sold', true)
                    ->whereBetween('updated_at', [$start_date, $end_date])
                    ->count();

                $total_revenue = Product::where('is_sold', true)
                    ->whereBetween('updated_at', [$start_date, $end_date])
                    ->sum('price');

                $category_distribution = Product::select('category', DB::raw('COUNT(*) as count'))
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('category')
                    ->orderByDesc('count')
                    ->get();

                $daily_new_products = Product::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count'))
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                $recent_products = Product::with('user')
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->latest()
                    ->take(10)
                    ->get();

                $data = compact(
                    'start_date',
                    'end_date',
                    'total_products',
                    'new_products',
                    'products_growth',
                    'sold_products',
                    'total_revenue',
                    'category_distribution',
                    'daily_new_products',
                    'recent_products'
                );

                $pdf = PDF::loadView('admin.reports.pdf.products', $data);
                return $pdf->download('products-report-' . $start_date->format('Y-m-d') . '-to-' . $end_date->format('Y-m-d') . '.pdf');

            default:
                abort(404);
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

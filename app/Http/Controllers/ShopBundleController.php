<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopBundleController extends Controller
{
    public function index(Request $request)
    {
        $query = Bundle::where('status', 'approved')
            ->with(['user']);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bundle_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Location filter
        if ($request->has('location') && !empty($request->location)) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('location', $request->location);
            });
        }

        // Price range filter
        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        // Date sort
        if ($request->has('sort_date') && in_array($request->sort_date, ['asc', 'desc'])) {
            $query->orderBy('created_at', $request->sort_date);
        } else {
            $query->latest(); // Default sort by newest
        }

        $bundles = $query->get();

        // Get unique locations for the filter dropdown
        $locations = User::whereHas('bundles', function($q) {
            $q->where('status', 'approved');
        })->pluck('location')->unique()->filter();

        return view('shop.bundles', compact('bundles', 'locations'));
    }

    public function show(Bundle $bundle)
    {
        if ($bundle->status !== 'approved') {
            abort(404);
        }

        return view('bundles.show', compact('bundle'));
    }
}

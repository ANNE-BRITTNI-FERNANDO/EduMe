<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
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
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bundle_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Location filter
        if ($request->filled('location')) {
            $location = $request->location;
            $query->whereHas('user', function($q) use ($location) {
                $q->where('location', $location);
            });
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Combined sort
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        // Get locations for filter
        $locations = User::whereHas('bundles', function($q) {
            $q->where('status', 'approved');
        })->pluck('location')->unique()->filter()->values();

        $bundles = $query->paginate(12)->withQueryString();

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

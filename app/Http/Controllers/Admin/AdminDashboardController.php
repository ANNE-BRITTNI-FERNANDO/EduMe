<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where(function($query) {
                $query->where('is_approved', false)
                      ->where('is_rejected', false);
            })
            ->with('user')
            ->latest()
            ->get();

        $rejectedPayouts = PayoutRequest::where('status', 'rejected')->with('user')->latest()->get();

        return view('admin.dashboard', [
            'products' => $products,
            'rejectedPayouts' => $rejectedPayouts,
        ]);
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

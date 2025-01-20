<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Order;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'is_anonymous' => 'boolean'
        ]);

        $rating = Rating::create([
            'buyer_id' => auth()->id(),
            'seller_id' => $order->seller_id,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_anonymous' => $request->is_anonymous ?? false
        ]);

        return redirect()->back()->with('success', 'Rating submitted successfully!');
    }

    public function sellerRatings()
    {
        $ratings = Rating::where('seller_id', auth()->id())
            ->with(['buyer', 'order'])
            ->latest()
            ->paginate(10);

        return view('seller.ratings', compact('ratings'));
    }

    public function adminRatings()
    {
        $ratings = Rating::with(['buyer', 'seller', 'order'])
            ->latest()
            ->paginate(20);

        return view('admin.ratings', compact('ratings'));
    }
}

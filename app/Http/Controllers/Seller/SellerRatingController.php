<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SellerRatingController extends Controller
{
    public function adminIndex()
    {
        $ratings = SellerRating::with(['seller', 'buyer', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.ratings.index', compact('ratings'));
    }

    public function approve(SellerRating $rating)
    {
        $rating->update(['status' => 'approved']);
        return back()->with('success', 'Rating has been approved.');
    }

    public function reject(SellerRating $rating)
    {
        $rating->update(['status' => 'rejected']);
        return back()->with('success', 'Rating has been rejected.');
    }

    public function store(Request $request, Order $order)
    {
        Log::info('Hitting store method', [
            'request_method' => $request->method(),
            'order_id' => $order->id,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'seller_id' => 'required|exists:users,id',
            'is_anonymous' => 'boolean'
        ]);

        // Check if user has already rated this seller for this order
        $existingRating = SellerRating::where('user_id', auth()->id())
            ->where('seller_id', $request->seller_id)
            ->where('order_id', $order->id)
            ->first();

        if ($existingRating) {
            return back()->with('error', 'You have already rated this seller for this order.');
        }

        // Verify that the seller is actually part of this order
        $sellerInOrder = $order->items()->where('seller_id', $request->seller_id)->exists();
        if (!$sellerInOrder) {
            return back()->with('error', 'Invalid seller for this order.');
        }

        // Create the rating
        SellerRating::create([
            'user_id' => auth()->id(),
            'buyer_id' => auth()->id(), // Set buyer_id same as user_id for backward compatibility
            'seller_id' => $request->seller_id,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_anonymous' => $request->boolean('is_anonymous', false)
        ]);

        return back()->with('success', 'Thank you for your rating!');
    }

    public function showSellerRatings()
    {
        $ratings = SellerRating::where('seller_id', Auth::id())
            ->with(['buyer', 'order'])
            ->latest()
            ->paginate(10);

        return view('seller.ratings.index', compact('ratings'));
    }

    public function showAdminRatings()
    {
        $ratings = SellerRating::with(['seller', 'buyer', 'order'])
            ->latest()
            ->paginate(20);

        return view('admin.ratings.index', compact('ratings'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerRating;
use Illuminate\Http\Request;

class SellerRatingController extends Controller
{
    public function adminIndex()
    {
        $ratings = SellerRating::with(['seller:id,name,email', 'buyer:id,name,email', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.ratings.index', compact('ratings'));
    }

    public function delete(SellerRating $rating)
    {
        $rating->delete();
        return back()->with('success', 'Rating has been deleted successfully.');
    }
}

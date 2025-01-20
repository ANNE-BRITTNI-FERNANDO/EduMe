<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Notifications\ProductSubmittedForReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('user_id', auth()->id())
            ->with('productImages')
            ->latest()
            ->get();

        return view('seller.products.index', compact('products'));
    }

    public function resubmit(Product $product)
    {
        // Check if the product belongs to the authenticated user
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        // Update product status and resubmitted flag
        $product->status = 'pending';
        $product->resubmitted = true;
        $product->save();

        // Notify admin about the resubmission
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new ProductSubmittedForReview($product));
        }

        return redirect()->back()->with('success', 'Product has been resubmitted for review.');
    }

    // ... other methods ...
}

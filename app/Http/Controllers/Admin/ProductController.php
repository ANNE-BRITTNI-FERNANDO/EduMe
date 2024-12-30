<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductApproved;
use App\Notifications\ProductRejected;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function pending()
    {
        $products = Product::where('status', 'pending')
            ->with(['user', 'productImages'])
            ->latest()
            ->paginate(10);

        return view('admin.pending', compact('products'));
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
            'rejection_note' => 'nullable|string'
        ]);

        $product->status = $request->status;
        
        if ($request->status === 'rejected') {
            $product->rejection_reason = $request->rejection_reason;
            $product->rejection_note = $request->rejection_note;
            $product->resubmitted = false;
        } else {
            $product->rejection_reason = null;
            $product->rejection_note = null;
            $product->resubmitted = false;
        }

        $product->save();

        // Send notification to the seller
        $seller = $product->user;
        if ($request->status === 'approved') {
            $seller->notify(new ProductApproved($product));
            $message = 'Product has been approved successfully.';
        } else {
            $seller->notify(new ProductRejected($product));
            $message = 'Product has been rejected.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function approved()
    {
        $products = Product::where('status', 'approved')
            ->with(['user', 'productImages'])
            ->latest()
            ->paginate(10);

        return view('admin.products.approved', compact('products'));
    }

    public function rejected()
    {
        $products = Product::where('status', 'rejected')
            ->with(['user', 'productImages'])
            ->latest()
            ->paginate(10);

        return view('admin.products.rejected', compact('products'));
    }
}

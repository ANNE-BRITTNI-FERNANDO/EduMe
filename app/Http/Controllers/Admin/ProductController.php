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
    public function pending(Request $request)
    {
        $query = Product::where('status', 'pending')
            ->with(['user', 'productImages']);

        // Apply sorting
        $currentSort = $request->get('sort', 'latest');
        switch ($currentSort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        // Apply category filter
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Product::distinct('category')->pluck('category', 'category');

        return view('admin.pending', compact('products', 'categories', 'currentSort'));
    }

    public function updateStatus(Request $request, Product $product)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
            'rejection_note' => 'nullable|string'
        ]);

        $product->status = $request->status;
        $product->is_approved = ($request->status === 'approved');
        $product->is_rejected = ($request->status === 'rejected');
        
        if ($request->status === 'rejected') {
            $product->rejection_reason = $request->rejection_reason;
            $product->rejection_note = $request->rejection_note;
            $product->resubmitted = false;
            $product->approved_at = null;
        } else {
            $product->rejection_reason = null;
            $product->rejection_note = null;
            $product->resubmitted = false;
            $product->approved_at = now();
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

    public function approved(Request $request)
    {
        $query = Product::where('status', 'approved')
            ->with(['user', 'productImages']);

        // Apply sorting
        $currentSort = $request->get('sort', 'latest');
        switch ($currentSort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        // Apply category filter
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Product::distinct('category')->pluck('category', 'category');

        return view('admin.approved', compact('products', 'categories', 'currentSort'));
    }

    public function rejected(Request $request)
    {
        $query = Product::where('status', 'rejected')
            ->with(['user', 'productImages']);

        // Apply sorting
        $currentSort = $request->get('sort', 'latest');
        switch ($currentSort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        // Apply category filter
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $products = $query->paginate(10)->withQueryString();
        $categories = Product::distinct('category')->pluck('category', 'category');

        return view('admin.rejected', compact('products', 'categories', 'currentSort'));
    }
}

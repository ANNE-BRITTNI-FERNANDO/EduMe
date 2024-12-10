<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Bundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with(['product', 'bundle', 'product.user', 'bundle.user'])
            ->get();
        return view('cart.index', ['items' => $cartItems]);
    }

    public function addToCart(Request $request, $type, $id)
    {
        $item = null;
        $itemType = strtolower($type);

        // Get the item based on type
        if ($itemType === 'product') {
            $item = Product::find($id);
        } elseif ($itemType === 'bundle') {
            $item = Bundle::find($id);
        }

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found.'
            ], 404);
        }

        // Check if item is approved
        if ($itemType === 'product' && !$item->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'This item is not available.'
            ], 400);
        }

        if ($itemType === 'bundle' && $item->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'This bundle is not available.'
            ], 400);
        }

        // Check if item is already in cart
        $existingItem = CartItem::where('user_id', Auth::id())
            ->where($itemType . '_id', $item->id)
            ->where('item_type', $itemType)
            ->first();

        if ($existingItem) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($itemType) . ' is already in your cart'
            ], 400);
        }

        // Add item to cart
        CartItem::create([
            'user_id' => Auth::id(),
            'product_id' => $itemType === 'product' ? $item->id : null,
            'bundle_id' => $itemType === 'bundle' ? $item->id : null,
            'item_type' => $itemType
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($itemType) . ' added to cart successfully'
        ]);
    }

    public function removeFromCart($id)
    {
        $cartItem = CartItem::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $cartItem->delete();

        return redirect()->back()->with('success', 'Item removed from cart successfully');
    }

    public function checkout()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with(['product', 'bundle', 'product.user', 'bundle.user'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        // Calculate price for each item
        $cartItems = $cartItems->map(function($item) {
            $item->price = $item->item_type === 'product' 
                ? $item->product->price 
                : $item->bundle->price;
            
            $item->name = $item->item_type === 'product' 
                ? $item->product->name 
                : $item->bundle->name;
                
            return $item;
        });

        return view('payment.checkout', [
            'cartItems' => $cartItems
        ]);
    }
}

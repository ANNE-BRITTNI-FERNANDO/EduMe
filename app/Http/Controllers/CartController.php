<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Bundle;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Delivery;
use App\Services\DeliveryService;

class CartController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    public function index()
    {
        // First, remove any invalid cart items
        CartItem::where('user_id', Auth::id())
            ->whereDoesntHave('product')
            ->whereDoesntHave('bundle')
            ->delete();

        $user = auth()->user();
        $items = CartItem::where('user_id', $user->id)
            ->with(['product', 'product.user', 'bundle', 'bundle.user'])
            ->get();

        $subtotal = 0;
        foreach ($items as $item) {
            if ($item->item_type === 'product' && $item->product) {
                $subtotal += $item->product->price;
            } elseif ($item->item_type === 'bundle' && $item->bundle) {
                $subtotal += $item->bundle->price;
            }
        }

        $deliveryFee = $this->deliveryService->calculateDeliveryFee(
            $items,
            $user->location ?? 'default',
            $user->province ?? null
        );

        $total = $subtotal + $deliveryFee;

        return view('cart.index', compact('items', 'subtotal', 'deliveryFee', 'total'));
    }

    public function addProduct(Product $product)
    {
        try {
            // Check if product is sold
            if ($product->is_sold || $product->quantity <= 0) {
                return redirect()->back()->with('error', 'Product is no longer available');
            }

            // Check if product already in cart
            $existingCartItem = CartItem::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->where('item_type', 'product')
                ->exists();

            if ($existingCartItem) {
                return redirect()->back()->with('error', 'Product is already in your cart');
            }

            // Check if product belongs to the current user
            if ($product->user_id === auth()->id()) {
                return redirect()->back()->with('error', 'You cannot add your own product to cart');
            }

            CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'item_type' => 'product',
                'quantity' => 1
            ]);

            return redirect()->back()->with('success', 'Product added to cart successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to add product to cart: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to add product to cart');
        }
    }

    public function addBundle(Bundle $bundle)
    {
        try {
            // Check if bundle already in cart
            $existingCartItem = CartItem::where('user_id', auth()->id())
                ->where('bundle_id', $bundle->id)
                ->where('item_type', 'bundle')
                ->exists();

            if ($existingCartItem) {
                return redirect()->back()->with('error', 'Bundle is already in your cart');
            }

            // Check if bundle belongs to the current user
            if ($bundle->user_id === auth()->id()) {
                return redirect()->back()->with('error', 'You cannot add your own bundle to cart');
            }

            CartItem::create([
                'user_id' => auth()->id(),
                'bundle_id' => $bundle->id,
                'item_type' => 'bundle',
                'quantity' => 1
            ]);

            return redirect()->back()->with('success', 'Bundle added to cart successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to add bundle to cart: ' . $e->getMessage(), [
                'bundle_id' => $bundle->id,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to add bundle to cart');
        }
    }

    public function removeItem(CartItem $item)
    {
        try {
            // Check if the item belongs to the current user
            if ($item->user_id !== auth()->id()) {
                return redirect()->back()->with('error', 'You are not authorized to remove this item');
            }

            $item->delete();
            return redirect()->back()->with('success', 'Item removed from cart successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to remove item from cart: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Failed to remove item from cart');
        }
    }

    public function getDistricts($province)
    {
        // Hardcoded districts for each province
        $districts = [
            'Western' => ['Colombo', 'Gampaha', 'Kalutara'],
            'Central' => ['Kandy', 'Matale', 'Nuwara Eliya'],
            'Southern' => ['Galle', 'Matara', 'Hambantota'],
            'Northern' => ['Jaffna', 'Kilinochchi', 'Mannar', 'Mullaitivu', 'Vavuniya'],
            'Eastern' => ['Trincomalee', 'Batticaloa', 'Ampara'],
            'North Western' => ['Kurunegala', 'Puttalam'],
            'North Central' => ['Anuradhapura', 'Polonnaruwa'],
            'Uva' => ['Badulla', 'Monaragala'],
            'Sabaragamuwa' => ['Ratnapura', 'Kegalle']
        ];

        return response()->json($districts[$province] ?? []);
    }

    public function updateDeliveryFee(Request $request)
    {
        try {
            $user = auth()->user();
            $items = CartItem::where('user_id', $user->id)
                ->with(['product', 'product.user', 'bundle', 'bundle.user'])
                ->get();

            $deliveryFee = $this->deliveryService->calculateDeliveryFee(
                $items,
                $request->input('location'),
                $request->input('province')
            );

            return response()->json([
                'success' => true,
                'delivery_fee' => $deliveryFee,
                'formatted_fee' => number_format($deliveryFee, 2)
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update delivery fee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update delivery fee'
            ], 500);
        }
    }

    public function checkout()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with(['product', 'product.user', 'bundle', 'bundle.user'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty');
        }

        $subtotal = 0;
        foreach ($cartItems as $item) {
            if ($item->item_type === 'product' && $item->product) {
                $subtotal += $item->product->price;
            } elseif ($item->item_type === 'bundle' && $item->bundle) {
                $subtotal += $item->bundle->price;
            }
        }

        $user = auth()->user();
        $deliveryFee = $this->deliveryService->calculateDeliveryFee(
            $cartItems,
            $user->location ?? 'default',
            $user->province ?? null
        );

        $total = $subtotal + $deliveryFee;

        return view('cart.checkout', compact('cartItems', 'subtotal', 'deliveryFee', 'total'));
    }
}

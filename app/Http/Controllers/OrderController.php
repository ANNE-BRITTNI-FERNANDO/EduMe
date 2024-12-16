<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PayoutService;
use App\Models\Warehouse;

class OrderController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function index()
    {
        $user = auth()->user();
        $view_type = request()->query('view', 'buyer');
        
        // Query based on view type
        if ($view_type === 'seller' && $user->role === 'seller') {
            // Show only orders where user is seller (through order items)
            $orders = Order::whereHas('items', function($query) use ($user) {
                $query->where('seller_id', $user->id);
            })
            ->with(['user', 'items' => function($query) use ($user) {
                $query->where('seller_id', $user->id)
                      ->with('item');
            }])
            ->latest()
            ->paginate(10);
                          
            return view('orders.seller.index', compact('orders'));
        } else {
            // Show only orders where user is buyer
            $orders = Order::where('user_id', $user->id)
                          ->with(['items.seller', 'items.item'])
                          ->latest()
                          ->paginate(10);
                          
            return view('orders.buyer.index', compact('orders'));
        }
    }

    public function show(Order $order)
    {
        $user = auth()->user();
        
        // Check if user is either buyer or seller of this order through order items
        if ($order->user_id !== $user->id && 
            !$order->items()->where('seller_id', $user->id)->exists()) {
            abort(403);
        }
        
        // Load necessary relationships
        $order->load([
            'user',
            'items.seller',
            'items.item',
            'warehouse'
        ]);
        
        // Get the first seller from order items
        $seller = $order->items->first()->seller;
        
        // Get available warehouses for seller
        $warehouses = Warehouse::where('pickup_available', true)->get();
        
        return view('orders.show', compact('order', 'seller', 'warehouses'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,delivered_to_warehouse,dispatched,completed,cancelled'
        ]);

        $order->delivery_status = $request->status;
        
        // If order is completed, mark all products as sold
        if ($request->status === 'completed') {
            foreach ($order->items as $item) {
                $product = $item->item;
                if ($product) {
                    $product->is_sold = true;
                    $product->save();
                }
            }
        }
        
        $order->save();

        return back()->with('success', 'Order status updated successfully');
    }

    public function adminIndex()
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $orders = Order::with(['user', 'items.item', 'sellers'])
            ->when(request('status'), function($query, $status) {
                return $query->where('delivery_status', $status);
            })
            ->latest()
            ->paginate(10);

        return view('orders.admin-orders', compact('orders'));
    }

    public function adminShow(Order $order)
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Load all necessary relationships
        $order->load([
            'user',
            'items.seller',
            'items.item',
            'deliveryTracking',
            'warehouse'
        ]);

        return view('orders.admin-order-show', compact('order'));
    }

    public function createBankTransferOrder(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'bank_transfer_details' => 'required|string',
        ]);

        // Determine if this is for a product or bundle
        $itemType = $conversation->product_id ? 'product' : 'bundle';
        $itemId = $conversation->product_id ?? $conversation->bundle_id;

        $order = Order::create([
            'user_id' => Auth::id(),
            'seller_id' => $conversation->seller_id,
            'item_id' => $itemId,
            'item_type' => $itemType,
            'amount' => $validated['total_amount'],
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
            'bank_transfer_details' => $validated['bank_transfer_details'],
        ]);

        // Send a system message in the conversation about the order
        $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'content' => "🏦 Bank transfer order created! Order #" . $order->id,
            'is_system_message' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank transfer order created successfully',
            'order' => $order
        ]);
    }

    public function confirmOrder(Request $request, Order $order)
    {
        // Only the seller can confirm the order
        if ($order->seller_id !== Auth::id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now()
        ]);

        // Mark products as sold
        foreach ($order->items as $item) {
            if ($item->item_type === 'product') {
                $product = $item->item;
                $product->update(['is_sold' => true]);
            }
        }

        // Send a system message in the conversation
        if ($order->conversation) {
            $order->conversation->messages()->create([
                'sender_id' => Auth::id(),
                'content' => "✅ Order #" . $order->id . " has been confirmed by the seller!",
                'is_system_message' => true
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order confirmed successfully',
            'order' => $order
        ]);
    }

    public function getOrderDetails(Order $order)
    {
        // Check if user is either buyer or seller
        if ($order->user_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'order' => $order->load(['user', 'seller', 'items.item'])
        ]);
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        // Check if user has permission to update the order
        if (Auth::user()->role === 'admin' || 
            (Auth::user()->role === 'seller' && $order->seller_id === Auth::id())) {
            
            $order->update([
                'delivery_status' => $validated['status']
            ]);

            // Mark products as sold when order is confirmed/shipped/delivered
            if (in_array($validated['status'], ['processing', 'shipped', 'delivered'])) {
                foreach ($order->items as $item) {
                    if ($item->item_type === 'product') {
                        $product = $item->item;
                        $product->update(['is_sold' => true]);
                    }
                }
            }

            // Create delivery tracking entry if status is shipped
            if ($validated['status'] === 'shipped') {
                $order->deliveryTracking()->create([
                    'status' => 'shipped',
                    'description' => 'Order has been shipped',
                    'location' => 'Seller\'s location'
                ]);
            }

            return redirect()->back()->with('success', 'Order status updated successfully');
        }

        return redirect()->back()->with('error', 'You do not have permission to update this order');
    }

    public function checkout()
    {
        $user = auth()->user();
        $cartItems = $user->cartItems()->with(['item'])->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $total = $cartItems->sum(function($item) {
            if ($item->item) {
                return $item->item->price;
            }
            return 0;
        });

        return view('orders.checkout', compact('cartItems', 'total'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $cartItems = \App\Models\CartItem::where('user_id', $user->id)
            ->with(['item'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty');
        }

        \DB::beginTransaction();
        try {
            foreach ($cartItems as $cartItem) {
                $order = null;
                if ($cartItem->item) {
                    $order = Order::create([
                        'user_id' => $user->id,
                        'seller_id' => $cartItem->item->user_id,
                        'item_id' => $cartItem->item_id,
                        'item_type' => $cartItem->item_type,
                        'amount' => $cartItem->item->price,
                        'delivery_status' => 'pending',
                        'payment_status' => 'pending'
                    ]);
                }

                // Update seller balance when order is created
                if ($order) {
                    $this->payoutService->updateSellerBalances($order);
                }
            }

            // Clear the cart after creating orders
            $cartItems->each->delete();

            \DB::commit();
            return redirect()->route('orders.index')->with('success', 'Orders placed successfully! Please complete the payment.');
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', 'Failed to place order. Please try again.');
        }
    }

    public function updateDeliveryStatus(Order $order, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,warehouse_confirmed,dispatched,delivered',
            'warehouse_id' => 'required_if:status,warehouse_confirmed|exists:warehouses,id'
        ]);

        $order->delivery_status = $request->status;
        
        if ($request->status === 'warehouse_confirmed') {
            $order->warehouse_id = $request->warehouse_id;
            $order->warehouse_confirmed_at = now();
            
            // Notify seller and buyer
            $order->user->notify(new OrderStatusUpdated($order, 'Your order has been received at the warehouse.'));
            $order->items->first()->seller->notify(new OrderStatusUpdated($order, 'Order has been confirmed at warehouse.'));
        }
        
        if ($request->status === 'dispatched') {
            $order->dispatched_at = now();
            $order->user->notify(new OrderStatusUpdated($order, 'Your order has been dispatched from the warehouse.'));
        }
        
        if ($request->status === 'delivered') {
            $order->delivered_at = now();
            $order->user->notify(new OrderStatusUpdated($order, 'Your order has been delivered successfully.'));
            $order->items->first()->seller->notify(new OrderStatusUpdated($order, 'Order has been delivered to customer.'));
        }

        $order->save();

        return back()->with('success', 'Order status updated successfully.');
    }

    public function getNearbyWarehouses()
    {
        $warehouses = Warehouse::where('pickup_available', true)
            ->orderBy('name')
            ->get();

        return response()->json($warehouses);
    }

    public function confirmDelivery(Order $order)
    {
        // Ensure the user is the buyer of this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Update the order status to completed
        $order->update([
            'status' => 'completed',
            'delivery_status' => 'completed',
            'completed_at' => now()
        ]);

        return back()->with('success', 'Delivery confirmed successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\Bundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\PayoutService;
use App\Models\Warehouse;
use App\Models\SellerBalance;
use App\Services\SellerBalanceService;
use App\Models\BudgetTracking;

class OrderController extends Controller
{
    protected $payoutService;
    protected $sellerBalanceService;

    public function __construct(PayoutService $payoutService, SellerBalanceService $sellerBalanceService)
    {
        $this->payoutService = $payoutService;
        $this->sellerBalanceService = $sellerBalanceService;
    }

    public function confirmOrder(Order $order)
    {
        DB::beginTransaction();
        try {
            // Update order status
            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'delivery_status' => 'processing'
            ]);

            // Mark all items in the order as sold
            foreach ($order->items as $orderItem) {
                if (strtolower($orderItem->item_type) === 'product') {
                    DB::table('products')
                        ->where('id', $orderItem->item_id)
                        ->update([
                            'is_sold' => true,
                            'quantity' => 0,
                            'updated_at' => now()
                        ]);
                    
                    \Log::info('Product marked as sold', [
                        'product_id' => $orderItem->item_id,
                        'order_id' => $order->id
                    ]);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to confirm order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,delivered_to_warehouse,dispatched,completed,cancelled'
        ]);

        DB::beginTransaction();
        try {
            // Update order status
            $order->delivery_status = $validated['status'];
            $order->save();

            // If order is completed or processing, mark items as sold
            if (in_array($validated['status'], ['completed', 'processing'])) {
                foreach ($order->items as $item) {
                    // Convert item_type to lowercase for comparison
                    $itemType = strtolower($item->item_type);

                    if ($itemType === 'product') {
                        try {
                            $product = Product::findOrFail($item->item_id);
                            
                            // Log before product update
                            \Log::info('Before product status update', [
                                'product_id' => $product->id,
                                'current_is_sold' => $product->is_sold,
                                'current_quantity' => $product->quantity
                            ]);

                            $product->update([
                                'is_sold' => true,
                                'quantity' => 0
                            ]);
                            
                            $product->refresh();
                            
                            // Log after product update
                            \Log::info('After product status update', [
                                'product_id' => $product->id,
                                'new_is_sold' => $product->is_sold,
                                'new_quantity' => $product->quantity
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to update product status', [
                                'product_id' => $item->item_id,
                                'error' => $e->getMessage()
                            ]);
                            throw $e;
                        }
                    } elseif ($itemType === 'bundle') {
                        try {
                            $bundle = Bundle::findOrFail($item->item_id);
                            $bundle->update([
                                'is_sold' => true,
                                'quantity' => 0
                            ]);
                            
                            foreach ($bundle->products as $product) {
                                $product->update([
                                    'is_sold' => true,
                                    'quantity' => 0
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Log::error('Failed to update bundle status', [
                                'bundle_id' => $item->item_id,
                                'error' => $e->getMessage()
                            ]);
                            throw $e;
                        }
                    }
                }
            }
            
            DB::commit();
            return back()->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Order status update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
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
        
        // Check if user is either buyer or seller of this order
        if ($order->user_id !== $user->id && $order->seller_id !== $user->id) {
            abort(403);
        }
        
        // Load necessary relationships
        $order->load([
            'user',
            'items.seller',
            'items.item',
            'warehouse',
            'sellerRating'
        ]);
        
        return view('orders.show', compact('order'));
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
                    if ($item->item_type === 'App\\Models\\Product') {
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

        // Check if any of these items are already in an order
        $itemsInOrders = \App\Models\OrderItem::whereIn('item_id', $cartItems->pluck('item_id'))
            ->whereIn('item_type', $cartItems->pluck('item_type'))
            ->exists();

        if ($itemsInOrders) {
            return redirect()->back()->with('error', 'Some items in your cart have already been ordered. Please refresh your cart.');
        }

        // Check if user has active budget tracking
        $budgetTracking = BudgetTracking::where('user_id', $user->id)
            ->where('cycle_end_date', '>', now())
            ->first();

        if ($budgetTracking) {
            // Check if order amount exceeds remaining budget
            $totalAmount = $cartItems->sum(function($item) {
                return $item->item->price * $item->quantity;
            });

            if (!$budgetTracking->hasEnoughBudget($totalAmount)) {
                return back()->with('error', 'This order exceeds your remaining budget. Current remaining budget: $' . number_format($budgetTracking->remaining_amount, 2));
            }

            // Deduct from budget
            if (!$budgetTracking->deductFromBudget($totalAmount)) {
                return back()->with('error', 'Unable to process order due to budget constraints.');
            }
        }

        \DB::beginTransaction();
        try {
            // Group items by seller
            $sellerItems = [];
            foreach ($cartItems as $cartItem) {
                if ($cartItem->item) {
                    $sellerId = $cartItem->item->user_id;
                    if (!isset($sellerItems[$sellerId])) {
                        $sellerItems[$sellerId] = [];
                    }
                    $sellerItems[$sellerId][] = $cartItem;
                }
            }

            $orders = [];
            // Create an order for each seller
            foreach ($sellerItems as $sellerId => $items) {
                // Calculate total amount for this seller's items
                $totalAmount = collect($items)->sum(function ($item) {
                    return $item->item->price * $item->quantity;
                });

                // Create the order
                $order = Order::create([
                    'user_id' => $user->id,
                    'seller_id' => $sellerId,
                    'amount' => $totalAmount,
                    'delivery_status' => 'pending',
                    'payment_status' => 'pending',
                    'order_number' => 'ORD-' . time() . '-' . $sellerId // Add unique order number
                ]);

                // Notify admin about the new order
                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    $admin->notify(new NewOrderNotification($order));
                }

                // Notify seller about the new order
                foreach ($sellerItems as $sellerId => $items) {
                    $seller = User::find($sellerId);
                    if ($seller) {
                        $seller->notify(new NewOrderNotification($order));
                    }
                }

                // Create order items
                foreach ($items as $cartItem) {
                    $order->items()->create([
                        'seller_id' => $sellerId,
                        'item_id' => $cartItem->item_id,
                        'item_type' => $cartItem->item_type,
                        'price' => $cartItem->item->price,
                        'quantity' => $cartItem->quantity
                    ]);

                    // Mark the item as sold to prevent duplicate orders
                    $cartItem->item->update(['is_sold' => true]);
                }

                // Update seller balance using the service
                $this->sellerBalanceService->updateBalanceForNewOrder($order);

                $orders[] = $order;
            }

            // Clear the cart AFTER successful order creation
            \App\Models\CartItem::where('user_id', $user->id)->delete();

            \DB::commit();

            // Return success response
            return redirect()->route('orders.index')->with('success', 'Orders created successfully!');

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Order creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create order. Please try again.');
        }
    }

    public function updateDeliveryStatus(Order $order, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,delivered_to_warehouse,dispatched,delivered,cancelled',
            'warehouse_id' => 'required_if:status,delivered_to_warehouse|exists:warehouses,id'
        ]);

        DB::beginTransaction();
        try {
            $previousStatus = $order->delivery_status;
            $order->delivery_status = $request->status;
            
            // Sync order status with delivery status
            if ($request->status === 'delivered_to_warehouse') {
                $order->warehouse_id = $request->warehouse_id;
                $order->warehouse_confirmed_at = now();
                $order->status = 'delivered_to_warehouse';
                
                // Update seller balance when warehouse confirms
                $this->sellerBalanceService->updateBalanceForOrderStatus($order, 'delivered_to_warehouse', $previousStatus);
                
                // Notify seller and buyer
                $order->user->notify(new OrderStatusUpdated($order, 'Your order has been received at the warehouse.'));
                $order->items->first()->seller->notify(new OrderStatusUpdated($order, 'Order has been confirmed at warehouse.'));
            } 
            elseif ($request->status === 'dispatched') {
                $order->dispatched_at = now();
                $order->status = 'dispatched';
                $order->user->notify(new OrderStatusUpdated($order, 'Your order has been dispatched from the warehouse.'));
            } 
            elseif ($request->status === 'delivered') {
                $order->delivered_at = now();
                $order->status = 'delivered';
                
                // Update seller balance when order is delivered
                $this->sellerBalanceService->updateBalanceForOrderStatus($order, 'delivered', $previousStatus);
                
                $order->user->notify(new OrderStatusUpdated($order, 'Your order has been delivered successfully.'));
                $order->items->first()->seller->notify(new OrderStatusUpdated($order, 'Order has been delivered to customer.'));
            }
            elseif ($request->status === 'cancelled') {
                $order->status = 'cancelled';
                
                // Update seller balance when order is cancelled
                $this->sellerBalanceService->updateBalanceForOrderStatus($order, 'cancelled', $previousStatus);
                
                $order->user->notify(new OrderStatusUpdated($order, 'Your order has been cancelled.'));
                $order->items->first()->seller->notify(new OrderStatusUpdated($order, 'Order has been cancelled.'));
            }

            $order->save();
            
            // Log the status change
            \Log::info('Order status updated', [
                'order_id' => $order->id,
                'previous_status' => $previousStatus,
                'new_status' => $request->status,
                'warehouse_id' => $order->warehouse_id
            ]);

            DB::commit();
            return back()->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to update order status: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'status' => $request->status,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
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

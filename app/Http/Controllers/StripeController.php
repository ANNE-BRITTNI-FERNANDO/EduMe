<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\SellerBalance;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    public function checkout(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Validate delivery details
        $request->validate([
            'delivery_address' => 'required|string',
            'phone' => 'required|string',
            'province' => 'required|string',
            'location' => 'required|string'
        ]);

        $user = auth()->user();
        $items = CartItem::where('user_id', $user->id)
            ->with(['product', 'product.user', 'bundle', 'bundle.user'])
            ->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty');
        }

        // Get delivery details from session
        $deliveryFee = session('delivery_fee');
        $deliveryLocation = session('delivery_location');
        $deliveryProvince = session('delivery_province');

        // Verify delivery details match
        if ($request->location !== $deliveryLocation || $request->province !== $deliveryProvince) {
            // Recalculate if details don't match
            $deliveryFee = $this->deliveryService->calculateDeliveryFee(
                $items,
                $request->location,
                $request->province
            );
            
            // Update session with new details
            session([
                'delivery_fee' => $deliveryFee,
                'delivery_location' => $request->location,
                'delivery_province' => $request->province
            ]);
        }

        if ($deliveryFee === null) {
            // Calculate if not in session
            $deliveryFee = $this->deliveryService->calculateDeliveryFee(
                $items,
                $request->location,
                $request->province
            );
            
            // Store in session
            session([
                'delivery_fee' => $deliveryFee,
                'delivery_location' => $request->location,
                'delivery_province' => $request->province
            ]);
        }

        $lineItems = [];
        $orderItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            if ($item->item_type === 'product' && $item->product) {
                $name = $item->product->product_name;
                $price = $item->product->price;
                $seller_id = $item->product->user_id;
                $image = $item->product->image_path;
            } elseif ($item->item_type === 'bundle' && $item->bundle) {
                $name = $item->bundle->bundle_name;
                $price = $item->bundle->price;
                $seller_id = $item->bundle->user_id;
                $image = $item->bundle->bundle_image;
            } else {
                continue;
            }

            $subtotal += $price;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'lkr',
                    'product_data' => [
                        'name' => $name,
                        'images' => [asset('storage/' . $image)],
                    ],
                    'unit_amount' => $price * 100, // Convert to cents
                ],
                'quantity' => 1,
            ];

            $orderItems[] = [
                'name' => $name,
                'price' => $price,
                'seller_id' => $seller_id,
                'type' => $item->item_type,
                'item_id' => $item->item_type === 'product' ? $item->product_id : $item->bundle_id
            ];
        }

        $totalAmount = $subtotal + $deliveryFee;

        // Add delivery fee as a separate line item
        if ($deliveryFee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'lkr',
                    'product_data' => [
                        'name' => 'Delivery Fee',
                        'description' => 'Standard shipping fee',
                    ],
                    'unit_amount' => $deliveryFee * 100, // Convert to cents
                ],
                'quantity' => 1,
            ];
        }

        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cart.index'),
                'metadata' => [
                    'order_items' => json_encode($orderItems),
                    'delivery_fee' => $deliveryFee,
                    'subtotal' => $subtotal,
                    'total_amount' => $totalAmount,
                    'user_id' => $user->id,
                    'shipping_address' => $request->delivery_address,
                    'phone' => $request->phone,
                    'buyer_city' => $request->location,
                    'buyer_province' => $request->province
                ],
            ]);

            // Update user's address and phone if they've changed
            if ($user->address !== $request->delivery_address || $user->phone !== $request->phone) {
                $user->update([
                    'address' => $request->delivery_address,
                    'phone' => $request->phone,
                    'location' => $request->location,
                    'province' => $request->province
                ]);
            }

            // Clear the delivery fee from session after using it
            session()->forget('delivery_fee');

            return redirect($session->url);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        
        if (!$sessionId) {
            return redirect()->route('cart.index')->with('error', 'Invalid payment session');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                \DB::beginTransaction();
                try {
                    // Get order details from session metadata
                    $metadata = $session->metadata;
                    $orderItems = json_decode($metadata->order_items, true);
                    $deliveryFee = $metadata->delivery_fee;
                    $subtotal = $metadata->subtotal;
                    $totalAmount = $metadata->total_amount;
                    $userId = $metadata->user_id;

                    // Group items by seller
                    $sellerOrders = [];
                    foreach ($orderItems as $item) {
                        $sellerId = $item['seller_id'];
                        if (!isset($sellerOrders[$sellerId])) {
                            $sellerOrders[$sellerId] = [
                                'items' => [],
                                'total' => 0
                            ];
                        }
                        $sellerOrders[$sellerId]['items'][] = $item;
                        $sellerOrders[$sellerId]['total'] += $item['price'];
                    }

                    // Create an order for each seller
                    foreach ($sellerOrders as $sellerId => $sellerOrder) {
                        // Calculate this seller's portion of the delivery fee
                        $sellerDeliveryFee = $deliveryFee * ($sellerOrder['total'] / $subtotal);

                        // Create the order
                        $order = Order::create([
                            'user_id' => $userId,
                            'seller_id' => $sellerId,
                            'subtotal' => $sellerOrder['total'],
                            'total_amount' => $sellerOrder['total'] + $sellerDeliveryFee,
                            'delivery_fee' => $sellerDeliveryFee,
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                            'payment_status' => 'completed',
                            'payment_id' => $session->payment_intent,
                            'payment_method' => 'stripe',
                            'order_number' => 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 12)),
                            'shipping_address' => $metadata->shipping_address,
                            'phone' => $metadata->phone,
                            'buyer_city' => $metadata->buyer_city,
                            'buyer_province' => $metadata->buyer_province
                        ]);

                        // Create order items and update seller balance
                        foreach ($sellerOrder['items'] as $item) {
                            $order->items()->create([
                                'name' => $item['name'],
                                'price' => $item['price'],
                                'seller_id' => $sellerId,
                                'item_type' => $item['type'],
                                'item_id' => $item['item_id']
                            ]);

                            // Mark product or bundle as sold
                            if ($item['type'] === 'product') {
                                \DB::table('products')
                                    ->where('id', $item['item_id'])
                                    ->update([
                                        'is_sold' => true,
                                        'quantity' => 0,
                                        'updated_at' => now()
                                    ]);
                                
                                \Log::info('Product marked as sold after payment', [
                                    'product_id' => $item['item_id'],
                                    'order_id' => $order->id
                                ]);
                            } elseif ($item['type'] === 'bundle') {
                                // Mark bundle as sold
                                \DB::table('bundles')
                                    ->where('id', $item['item_id'])
                                    ->update([
                                        'status' => 'sold',
                                        'is_sold' => true,
                                        'quantity' => 0,
                                        'updated_at' => now()
                                    ]);

                                // Get and mark all products in the bundle categories
                                $bundleCategories = \DB::table('bundle_categories')
                                    ->where('bundle_id', $item['item_id'])
                                    ->get();

                                foreach ($bundleCategories as $category) {
                                    // Find products in this category that aren't sold yet
                                    \DB::table('products')
                                        ->where('category', $category->category)
                                        ->where('is_sold', false)
                                        ->update([
                                            'is_sold' => true,
                                            'quantity' => 0,
                                            'updated_at' => now()
                                        ]);
                                }
                                
                                \Log::info('Bundle and its category products marked as sold after payment', [
                                    'bundle_id' => $item['item_id'],
                                    'categories' => $bundleCategories->pluck('category'),
                                    'order_id' => $order->id
                                ]);
                            }

                            // Update seller balance
                            $sellerBalance = SellerBalance::firstOrCreate(
                                ['seller_id' => $sellerId],
                                [
                                    'available_balance' => 0,
                                    'pending_balance' => 0,
                                    'total_earned' => 0
                                ]
                            );

                            // Add to pending balance and total earned
                            $amount = $item['price'];
                            \DB::table('seller_balances')
                                ->where('seller_id', $sellerId)
                                ->update([
                                    'pending_balance' => \DB::raw("pending_balance + $amount"),
                                    'total_earned' => \DB::raw("total_earned + $amount"),
                                    'updated_at' => now()
                                ]);
                        }

                        // Notify seller
                        $seller = User::find($sellerId);
                        $seller->notify(new NewOrderNotification($order));
                    }

                    // Clear the cart
                    CartItem::where('user_id', $userId)->delete();

                    \DB::commit();

                    return view('payment.success', [
                        'message' => 'Payment completed successfully! Your orders have been placed.'
                    ]);
                } catch (\Exception $e) {
                    \DB::rollback();
                    \Log::error('Failed to process successful payment', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            return redirect()->route('cart.index')->with('error', 'Payment was not successful');
        } catch (\Exception $e) {
            \Log::error('Error in payment success callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('cart.index')->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('cart.index')->with('error', 'Payment was cancelled');
    }
}

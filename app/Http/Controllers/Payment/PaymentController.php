<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\SellerBalance;
use App\Models\Bundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Exception;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function checkout()
    {
        $user = auth()->user();
        $cartItems = CartItem::where('user_id', $user->id)
            ->with(['product', 'bundle'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product ? $item->product->price : $item->bundle->price) + ($item->delivery_fee ?? 0);
        });

        return view('payment.checkout', compact('cartItems', 'total'));
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $cartItems = CartItem::where('user_id', Auth::id())->get();
            $totalAmount = $cartItems->sum(function ($item) {
                return $item->price + $item->delivery_fee;
            });

            $paymentIntent = PaymentIntent::create([
                'amount' => round($totalAmount * 100), // Convert to cents
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'user_id' => Auth::id(),
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function redirectToStripe(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            $amount = $request->query('amount', 0);
            
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Cart Payment',
                        ],
                        'unit_amount' => round($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success'),
                'cancel_url' => route('payment.cancel'),
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function process(Request $request)
    {
        $request->validate([
            'delivery_address' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        try {
            return $this->processCheckout($request);
        } catch (\Exception $e) {
            Log::error('Checkout process failed: ' . $e->getMessage());
            return back()->with('error', 'There was an error processing your payment. Please try again.');
        }
    }

    public function processCheckout(Request $request)
    {
        try {
            $user = auth()->user();
            $cartItems = CartItem::where('user_id', $user->id)
                ->with(['product', 'bundle'])
                ->get();

            if ($cartItems->isEmpty()) {
                return back()->with('error', 'Your cart is empty.');
            }

            // Validate the request
            $request->validate([
                'delivery_address' => 'required|string',
                'phone' => ['required', 'regex:/^(?:\+94|0)[0-9]{9}$/'],
                'delivery_types' => 'required|array'
            ]);

            // Calculate total amount including delivery fees
            $subtotal = 0;
            $totalDeliveryFee = 0;

            foreach ($cartItems as $item) {
                // Get item price
                $itemPrice = $item->item_type === 'product' 
                    ? $item->product->price 
                    : $item->bundle->price;
                
                $subtotal += $itemPrice;

                // Calculate delivery fee based on type
                $baseFee = $item->delivery_fee;
                $deliveryType = $request->delivery_types[$item->id] ?? 'standard';
                $deliveryFee = $deliveryType === 'express' ? $baseFee * 1.5 : $baseFee;
                
                $totalDeliveryFee += $deliveryFee;

                // Update cart item with delivery info
                $item->update([
                    'delivery_type' => $deliveryType,
                    'delivery_fee' => $deliveryFee,
                    'delivery_address' => $request->delivery_address,
                    'phone' => $request->phone
                ]);
            }

            $totalAmount = $subtotal + $totalDeliveryFee;

            // Create Stripe Checkout Session
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            
            $lineItems = [];
            $orderItems = [];
            
            foreach ($cartItems as $item) {
                $itemName = $item->item_type === 'product' 
                    ? $item->product->name 
                    : $item->bundle->name;
                
                $itemPrice = $item->item_type === 'product' 
                    ? $item->product->price 
                    : $item->bundle->price;
                
                $sellerId = $item->item_type === 'product' 
                    ? $item->product->user_id 
                    : $item->bundle->user_id;

                // Add item price
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'lkr',
                        'product_data' => [
                            'name' => $itemName,
                        ],
                        'unit_amount' => round($itemPrice * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ];

                // Add delivery fee as separate line item
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'lkr',
                        'product_data' => [
                            'name' => "Delivery Fee for $itemName (" . ucfirst($item->delivery_type) . ")",
                        ],
                        'unit_amount' => round($item->delivery_fee * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ];

                $orderItems[] = [
                    'item_id' => $item->item_type === 'product' ? $item->product_id : $item->bundle_id,
                    'item_type' => $item->item_type,
                    'price' => $itemPrice,
                    'delivery_fee' => $item->delivery_fee,
                    'delivery_type' => $item->delivery_type,
                    'seller_id' => $sellerId,
                ];
            }

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel'),
                'metadata' => [
                    'user_id' => $user->id,
                    'delivery_address' => $request->delivery_address,
                    'phone' => $request->phone,
                    'order_items' => json_encode($orderItems),
                ],
                'customer_email' => $user->email,
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            \Log::error('Checkout error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during checkout. Please try again.');
        }
    }

    public function success(Request $request)
    {
        try {
            if (!$request->get('session_id')) {
                return redirect()->route('cart.index');
            }

            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $session = $stripe->checkout->sessions->retrieve($request->get('session_id'));
            
            if ($session->payment_status === 'paid') {
                $metadata = $session->metadata;
                $orderItems = json_decode($metadata->order_items, true);
                
                DB::beginTransaction();
                
                try {
                    // Create orders for each seller
                    $sellerOrders = [];
                    foreach ($orderItems as $item) {
                        $sellerId = $item['seller_id'];
                        if (!isset($sellerOrders[$sellerId])) {
                            $sellerOrders[$sellerId] = Order::create([
                                'user_id' => auth()->id(),
                                'seller_id' => $sellerId,  // Add this line
                                'order_number' => 'ORD-' . time() . '-' . auth()->id(),
                                'subtotal' => 0, // Will be updated
                                'delivery_fee' => 0, // Will be updated
                                'total' => 0, // Will be updated
                                'delivery_address' => $metadata->delivery_address,
                                'phone' => $metadata->phone,
                                'status' => 'pending',
                                'payment_method' => 'stripe',
                                'payment_status' => 'paid',
                                'payment_id' => $session->payment_intent,
                            ]);
                        }

                        // Create order item
                        $orderItem = $sellerOrders[$sellerId]->items()->create([
                            'item_id' => $item['item_id'],
                            'item_type' => $item['item_type'],
                            'price' => $item['price'],
                            'delivery_fee' => $item['delivery_fee'],
                            'delivery_type' => $item['delivery_type'],
                        ]);

                        // Update order total
                        $sellerOrders[$sellerId]->increment('subtotal', $item['price']);
                        $sellerOrders[$sellerId]->increment('delivery_fee', $item['delivery_fee']);
                        $sellerOrders[$sellerId]->increment('total', $item['price'] + $item['delivery_fee']);

                        // Create delivery tracking
                        // DeliveryTracking::create([
                        //     'order_id' => $sellerOrders[$sellerId]->id,
                        //     'order_item_id' => $orderItem->id,
                        //     'status' => 'pending',
                        //     'tracking_number' => 'TRK' . strtoupper(uniqid()),
                        //     'estimated_delivery' => now()->addDays($item['delivery_type'] === 'express' ? 1 : 3),
                        // ]);

                        // Update seller balance
                        $sellerBalance = SellerBalance::firstOrCreate(
                            ['user_id' => $sellerId],
                            ['available_balance' => 0, 'pending_balance' => 0]
                        );
                        
                        $sellerBalance->increment('pending_balance', $item['price']);
                    }

                    // Clear the cart
                    CartItem::where('user_id', auth()->id())->delete();

                    DB::commit();

                    return redirect()->route('orders.index')
                        ->with('success', 'Payment successful! Your order has been placed.');
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return redirect()->route('cart.index')
                ->with('error', 'Payment was not completed.');
        } catch (\Exception $e) {
            \Log::error('Payment success error: ' . $e->getMessage());
            return redirect()->route('cart.index')
                ->with('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('cart.index')
            ->with('error', 'Payment was cancelled.');
    }

    public function handleStripeWebhook(Request $request)
    {
        \Log::info('Stripe webhook received');
        
        try {
            $payload = $request->all();
            $event = null;

            try {
                $event = \Stripe\Event::constructFrom($payload);
            } catch(\UnexpectedValueException $e) {
                \Log::error('Invalid payload', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            if ($event->type === 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;
                
                try {
                    DB::beginTransaction();
                    
                    // Find the order by payment_id
                    $order = Order::where('payment_id', $paymentIntent->id)->first();
                    
                    if (!$order) {
                        throw new \Exception('Order not found for payment: ' . $paymentIntent->id);
                    }
                    
                    \Log::info('Processing successful payment for order', [
                        'order_id' => $order->id,
                        'payment_id' => $paymentIntent->id
                    ]);

                    // Update order status
                    $order->update([
                        'payment_status' => 'completed',
                        'status' => 'confirmed',
                        'confirmed_at' => now(),
                        'delivery_status' => 'processing'
                    ]);

                    // Mark items as sold
                    foreach ($order->items as $orderItem) {
                        $itemType = strtolower($orderItem->item_type);
                        
                        if ($itemType === 'product') {
                            $updated = DB::table('products')
                                ->where('id', $orderItem->item_id)
                                ->where('is_sold', false) // Only update if not already sold
                                ->update([
                                    'is_sold' => true,
                                    'quantity' => 0,
                                    'updated_at' => now()
                                ]);
                                
                            if (!$updated) {
                                throw new \Exception("Failed to update product {$orderItem->item_id} - already sold");
                            }
                            
                            \Log::info('Product marked as sold', [
                                'product_id' => $orderItem->item_id,
                                'order_id' => $order->id
                            ]);
                        }
                    }

                    DB::commit();
                    \Log::info('Payment processed successfully', ['order_id' => $order->id]);
                    
                    return response()->json(['status' => 'success']);
                } catch (\Exception $e) {
                    DB::rollback();
                    \Log::error('Failed to process payment', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            \Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

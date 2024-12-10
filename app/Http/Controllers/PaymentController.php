<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Exception;
use App\Models\CartItem;
use App\Models\SellerBalance;
use App\Models\Order;
use App\Models\Product;
use App\Models\Bundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function showCheckout()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with(['product', 'bundle']) // Eager load relationships
            ->get();

        return view('payment.checkout', [
            'cartItems' => $cartItems
        ]);
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            $cartItems = CartItem::where('user_id', Auth::id())
                ->with(['product', 'bundle'])
                ->get();

            $total = 0;
            foreach ($cartItems as $item) {
                if ($item->item_type === 'product' && $item->product) {
                    $total += $item->product->price;
                } elseif ($item->item_type === 'bundle' && $item->bundle) {
                    $total += $item->bundle->price;
                }
            }

            // Convert to cents for Stripe
            $amount = $total * 100;

            if ($amount <= 0) {
                return response()->json([
                    'error' => 'Invalid cart total'
                ], 400);
            }

            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success()
    {
        try {
            DB::transaction(function () {
                // Get the user's cart items with proper eager loading
                $cartItems = CartItem::where('user_id', Auth::id())
                    ->with([
                        'product',
                        'product.user',
                        'bundle',
                        'bundle.user'
                    ])
                    ->get();

                Log::info('Processing cart items', ['cart_items' => $cartItems->toArray()]);

                // Process each item and update seller balances
                foreach ($cartItems as $item) {
                    if ($item->item_type === 'product' && $item->product && $item->product->user) {
                        $seller = $item->product->user;
                        $price = $item->product->price;
                        
                        Log::info('Processing product purchase', [
                            'product_id' => $item->product->id,
                            'seller_id' => $seller->id,
                            'price' => $price
                        ]);

                        // Get or create seller balance for this seller
                        $sellerBalance = SellerBalance::firstOrCreate(
                            ['user_id' => $seller->id],
                            [
                                'available_balance' => 0,
                                'pending_balance' => 0,
                                'total_earned' => 0
                            ]
                        );

                        Log::info('Current seller balance', [
                            'seller_id' => $seller->id,
                            'balance' => $sellerBalance->toArray()
                        ]);

                        // Add earnings to seller's balance
                        DB::beginTransaction();
                        try {
                            $sellerBalance->refresh(); // Get fresh data
                            $oldBalance = $sellerBalance->available_balance;
                            
                            $sellerBalance->available_balance += $price;
                            $sellerBalance->total_earned += $price;
                            $sellerBalance->save();

                            Log::info('Updated seller balance', [
                                'seller_id' => $seller->id,
                                'old_balance' => $oldBalance,
                                'new_balance' => $sellerBalance->available_balance,
                                'amount_added' => $price
                            ]);

                            // Create order record for each item
                            $order = new Order();
                            $order->user_id = Auth::id();
                            $order->seller_id = $seller->id;
                            $order->item_id = $item->product->id;
                            $order->item_type = 'product';
                            $order->total_amount = $price;
                            $order->payment_method = 'stripe';
                            $order->payment_status = 'completed';
                            $order->save();

                            DB::commit();
                            Log::info('Successfully updated seller balance and created order', [
                                'seller_id' => $seller->id,
                                'amount' => $price,
                                'order_id' => $order->id
                            ]);
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Failed to update seller balance', [
                                'seller_id' => $seller->id,
                                'amount' => $price,
                                'error' => $e->getMessage()
                            ]);
                            throw $e;
                        }
                    } elseif ($item->item_type === 'bundle' && $item->bundle && $item->bundle->user) {
                        $seller = $item->bundle->user;
                        $price = $item->bundle->price;

                        Log::info('Processing bundle purchase', [
                            'bundle_id' => $item->bundle->id,
                            'seller_id' => $seller->id,
                            'price' => $price
                        ]);

                        // Get or create seller balance for this seller
                        $sellerBalance = SellerBalance::firstOrCreate(
                            ['user_id' => $seller->id],
                            [
                                'available_balance' => 0,
                                'pending_balance' => 0,
                                'total_earned' => 0
                            ]
                        );

                        Log::info('Current seller balance', [
                            'seller_id' => $seller->id,
                            'balance' => $sellerBalance->toArray()
                        ]);

                        DB::beginTransaction();
                        try {
                            $sellerBalance->refresh(); // Get fresh data
                            $oldBalance = $sellerBalance->available_balance;
                            
                            $sellerBalance->available_balance += $price;
                            $sellerBalance->total_earned += $price;
                            $sellerBalance->save();

                            Log::info('Updated seller balance', [
                                'seller_id' => $seller->id,
                                'old_balance' => $oldBalance,
                                'new_balance' => $sellerBalance->available_balance,
                                'amount_added' => $price
                            ]);

                            $order = new Order();
                            $order->user_id = Auth::id();
                            $order->seller_id = $seller->id;
                            $order->item_id = $item->bundle->id;
                            $order->item_type = 'bundle';
                            $order->total_amount = $price;
                            $order->payment_method = 'stripe';
                            $order->payment_status = 'completed';
                            $order->save();

                            DB::commit();
                            Log::info('Successfully updated seller balance and created order for bundle', [
                                'seller_id' => $seller->id,
                                'amount' => $price,
                                'order_id' => $order->id
                            ]);
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Failed to update seller balance for bundle', [
                                'seller_id' => $seller->id,
                                'amount' => $price,
                                'error' => $e->getMessage()
                            ]);
                            throw $e;
                        }
                    } else {
                        Log::warning('Invalid cart item', [
                            'item' => $item->toArray()
                        ]);
                    }
                }

                // Clear the user's cart after successful payment
                CartItem::where('user_id', Auth::id())->delete();
            });

            return view('payment.success');
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return redirect()->route('cart.index')->with('error', 'Payment processing failed. Please try again.');
        }
    }

    public function cancel()
    {
        return view('payment.cancel');
    }
}

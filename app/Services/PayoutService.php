<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\SellerBalance;
use Illuminate\Support\Facades\DB;

class PayoutService
{
public function updateSellerBalances(Order $order)
{
    $sellerBalance = SellerBalance::firstOrCreate(
        ['seller_id' => $order->seller_id],
        [
            'available_balance' => 0,
            'pending_balance' => 0,
            'total_earned' => 0,
            'balance_to_be_paid' => 0,
            'total_delivery_fees_earned' => 0
        ]
    );

    $orderAmount = $order->amount;
    
    // Update balances
    $sellerBalance->pending_balance += $orderAmount;
    $sellerBalance->total_earned += $orderAmount;
    $sellerBalance->balance_to_be_paid = $sellerBalance->pending_balance + $sellerBalance->available_balance;
    
    // Save changes
    $sellerBalance->save();
}

    private function updateBalanceOnPaymentComplete(Order $order)
    {
        DB::transaction(function () use ($order) {
            $sellerBalance = SellerBalance::updateOrCreate(
                ['user_id' => $order->seller_id],
                [
                    'available_balance' => 0,
                    'pending_balance' => 0,
                    'total_earned' => 0
                ]
            );

            // Add to pending balance initially
            $sellerBalance->increment('pending_balance', $order->amount);
            $sellerBalance->increment('total_earned', $order->amount);

            // Create if not exists
            if (!$sellerBalance->wasRecentlyCreated) {
                $sellerBalance->update([
                    'user_id' => $order->seller_id
                ]);
            }
        });
    }

    public function handlePayoutRequest(PayoutRequest $payoutRequest, string $status, ?string $rejectionReason = null)
    {
        DB::transaction(function () use ($payoutRequest, $status, $rejectionReason) {
            $sellerBalance = SellerBalance::where('user_id', $payoutRequest->user_id)->firstOrFail();

            if ($status === 'approved') {
                // Deduct from available balance when approved
                if ($sellerBalance->available_balance >= $payoutRequest->amount) {
                    DB::table('seller_balances')
                        ->where('user_id', $payoutRequest->user_id)
                        ->update([
                            'available_balance' => DB::raw("available_balance - {$payoutRequest->amount}"),
                            'updated_at' => now()
                        ]);

                    $payoutRequest->update([
                        'status' => 'approved',
                        'processed_at' => now(),
                        'processed_by' => auth()->id()
                    ]);
                } else {
                    throw new \Exception('Insufficient available balance');
                }
            } elseif ($status === 'rejected') {
                // Return amount to available balance if rejected
                DB::table('seller_balances')
                    ->where('user_id', $payoutRequest->user_id)
                    ->update([
                        'available_balance' => DB::raw("available_balance + {$payoutRequest->amount}"),
                        'updated_at' => now()
                    ]);

                $payoutRequest->update([
                    'status' => 'rejected',
                    'rejection_reason' => $rejectionReason,
                    'processed_at' => now(),
                    'processed_by' => auth()->id()
                ]);
            }
        });
    }

    public function movePendingToAvailable(Order $order)
    {
        DB::transaction(function () use ($order) {
            $sellerBalance = SellerBalance::where('user_id', $order->seller_id)->first();
            
            if ($sellerBalance) {
                $amount = $order->total_amount - $order->delivery_fee; // Only move the product amount, not delivery fee
                
                // Move amount from pending to available
                DB::table('seller_balances')
                    ->where('user_id', $order->seller_id)
                    ->update([
                        'pending_balance' => DB::raw("pending_balance - $amount"),
                        'available_balance' => DB::raw("available_balance + $amount"),
                        'updated_at' => now()
                    ]);
            }
        });
    }
}

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
        DB::transaction(function () use ($order) {
            // Get all unique sellers from order items with their total amounts and delivery fees
            $sellerAmounts = $order->items()
                ->select(
                    'seller_id',
                    DB::raw('SUM(price * quantity) as total_amount'),
                    DB::raw('SUM(delivery_fee_share) as total_delivery_fee')
                )
                ->groupBy('seller_id')
                ->get();

            foreach ($sellerAmounts as $sellerAmount) {
                $sellerBalance = SellerBalance::lockForUpdate()->firstOrCreate(
                    ['seller_id' => $sellerAmount->seller_id],
                    [
                        'available_balance' => 0,
                        'pending_balance' => 0,
                        'total_earned' => 0,
                        'balance_to_be_paid' => 0,
                        'total_delivery_fees_earned' => 0
                    ]
                );

                $totalAmount = $sellerAmount->total_amount + $sellerAmount->total_delivery_fee;

                // Handle balance updates based on delivery status
                if ($order->delivery_status === 'delivered_to_warehouse') {
                    // When delivered to warehouse, move amount from pending to available
                    $sellerBalance->decrement('pending_balance', $totalAmount);
                    $sellerBalance->increment('available_balance', $totalAmount);
                    $sellerBalance->increment('total_delivery_fees_earned', $sellerAmount->total_delivery_fee);
                } else {
                    // For new orders or other statuses, add to pending
                    $sellerBalance->increment('pending_balance', $totalAmount);
                }

                // Always update total earned for new orders
                $sellerBalance->increment('total_earned', $totalAmount);

                // Update balance to be paid
                $sellerBalance->update([
                    'balance_to_be_paid' => DB::raw('pending_balance + available_balance')
                ]);

                // Log the balance update
                \Log::info("Seller balance updated", [
                    'order_id' => $order->id,
                    'seller_id' => $sellerAmount->seller_id,
                    'delivery_status' => $order->delivery_status,
                    'total_amount' => $totalAmount,
                    'delivery_fee' => $sellerAmount->total_delivery_fee,
                    'pending_balance' => $sellerBalance->pending_balance,
                    'available_balance' => $sellerBalance->available_balance
                ]);
            }
        });
    }

    public function updateBalanceForDeliveryStatus(Order $order, string $newStatus, ?string $previousStatus = null)
    {
        DB::transaction(function () use ($order, $newStatus, $previousStatus) {
            \Log::info("Starting updateBalanceForDeliveryStatus", [
                'order_id' => $order->id,
                'new_status' => $newStatus,
                'previous_status' => $previousStatus
            ]);

            // Only process if this is the first time the order is being marked as delivered_to_warehouse
            if ($newStatus === 'delivered_to_warehouse' && $previousStatus !== 'delivered_to_warehouse') {
                $sellerAmounts = $order->items()
                    ->select(
                        'seller_id',
                        DB::raw('SUM(price * quantity) as total_amount'),
                        DB::raw('SUM(COALESCE(delivery_fee_share, 0)) as total_delivery_fee')
                    )
                    ->groupBy('seller_id')
                    ->get();

                foreach ($sellerAmounts as $sellerAmount) {
                    $sellerBalance = SellerBalance::lockForUpdate()
                        ->where('seller_id', $sellerAmount->seller_id)
                        ->first();
                    
                    if (!$sellerBalance) {
                        \Log::error("No seller balance found", [
                            'seller_id' => $sellerAmount->seller_id,
                            'order_id' => $order->id
                        ]);
                        continue;
                    }

                    $totalAmount = $sellerAmount->total_amount;

                    \Log::info("Processing seller balance", [
                        'seller_id' => $sellerAmount->seller_id,
                        'total_amount' => $totalAmount,
                        'current_pending' => $sellerBalance->pending_balance,
                        'current_available' => $sellerBalance->available_balance
                    ]);

                    // Add amount directly to available balance
                    DB::table('seller_balances')
                        ->where('seller_id', $sellerAmount->seller_id)
                        ->update([
                            'available_balance' => DB::raw("available_balance + {$totalAmount}"),
                            'total_earned' => DB::raw("total_earned + {$totalAmount}"),
                            'balance_to_be_paid' => DB::raw("balance_to_be_paid + {$totalAmount}"),
                            'updated_at' => now()
                        ]);

                    if ($sellerAmount->total_delivery_fee > 0) {
                        DB::table('seller_balances')
                            ->where('seller_id', $sellerAmount->seller_id)
                            ->increment('total_delivery_fees_earned', $sellerAmount->total_delivery_fee);
                    }

                    \Log::info("Added amount to available balance", [
                        'seller_id' => $sellerAmount->seller_id,
                        'amount' => $totalAmount,
                        'delivery_fee' => $sellerAmount->total_delivery_fee
                    ]);

                    // Get final state for logging
                    $finalBalance = SellerBalance::where('seller_id', $sellerAmount->seller_id)->first();
                    \Log::info("Final balance state", [
                        'seller_id' => $sellerAmount->seller_id,
                        'pending_balance' => $finalBalance->pending_balance,
                        'available_balance' => $finalBalance->available_balance,
                        'total_earned' => $finalBalance->total_earned,
                        'total_delivery_fees' => $finalBalance->total_delivery_fees_earned
                    ]);
                }
            }
            // For all other status changes, do nothing with the balance
        });
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
            $sellerBalance = SellerBalance::where('seller_id', $payoutRequest->seller_id)
                ->lockForUpdate()
                ->first();

            if (!$sellerBalance) {
                throw new \Exception('Seller balance not found');
            }

            if ($status === 'completed') {
                // Check if there's enough available balance
                if ($sellerBalance->available_balance >= $payoutRequest->amount) {
                    // Deduct from available balance and balance to be paid
                    $sellerBalance->available_balance -= $payoutRequest->amount;
                    $sellerBalance->balance_to_be_paid -= $payoutRequest->amount;
                    $sellerBalance->pending_balance -= $payoutRequest->amount;
                    $sellerBalance->save();

                    // Update payout request
                    $payoutRequest->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                        'processed_by' => auth()->id(),
                        'approved_at' => now()
                    ]);

                    \Log::info('Payout request completed', [
                        'payout_request_id' => $payoutRequest->id,
                        'seller_id' => $payoutRequest->seller_id,
                        'amount' => $payoutRequest->amount,
                        'new_available_balance' => $sellerBalance->available_balance,
                        'new_pending_balance' => $sellerBalance->pending_balance
                    ]);
                } else {
                    throw new \Exception('Insufficient available balance for payout');
                }
            } elseif ($status === 'rejected') {
                // Remove from pending balance only
                $sellerBalance->pending_balance -= $payoutRequest->amount;
                $sellerBalance->save();

                // Update payout request
                $payoutRequest->update([
                    'status' => 'rejected',
                    'rejection_reason' => $rejectionReason,
                    'processed_at' => now(),
                    'processed_by' => auth()->id()
                ]);

                \Log::info('Payout request rejected', [
                    'payout_request_id' => $payoutRequest->id,
                    'seller_id' => $payoutRequest->seller_id,
                    'amount' => $payoutRequest->amount,
                    'reason' => $rejectionReason,
                    'new_pending_balance' => $sellerBalance->pending_balance
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

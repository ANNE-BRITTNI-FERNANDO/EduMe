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

            // Get all unique sellers from order items with their total amounts and delivery fees
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

                if ($newStatus === 'delivered_to_warehouse' && $previousStatus !== 'delivered_to_warehouse') {
                    // Move amount from pending to available when delivered to warehouse
                    DB::table('seller_balances')
                        ->where('seller_id', $sellerAmount->seller_id)
                        ->update([
                            'pending_balance' => DB::raw("pending_balance - {$totalAmount}"),
                            'available_balance' => DB::raw("available_balance + {$totalAmount}"),
                            'updated_at' => now()
                        ]);

                    if ($sellerAmount->total_delivery_fee > 0) {
                        DB::table('seller_balances')
                            ->where('seller_id', $sellerAmount->seller_id)
                            ->increment('total_delivery_fees_earned', $sellerAmount->total_delivery_fee);
                    }

                    \Log::info("Moved balance from pending to available", [
                        'seller_id' => $sellerAmount->seller_id,
                        'amount' => $totalAmount,
                        'delivery_fee' => $sellerAmount->total_delivery_fee
                    ]);
                }

                // Update balance to be paid
                DB::table('seller_balances')
                    ->where('seller_id', $sellerAmount->seller_id)
                    ->update([
                        'balance_to_be_paid' => DB::raw('pending_balance + available_balance'),
                        'updated_at' => now()
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
            $sellerBalance = SellerBalance::lockForUpdate()
                ->where('seller_id', $payoutRequest->seller_id)
                ->firstOrFail();

            if ($status === 'approved') {
                // Check if there's enough available balance
                if ($sellerBalance->available_balance >= $payoutRequest->amount) {
                    // Update seller balance
                    $sellerBalance->decrement('available_balance', $payoutRequest->amount);
                    $sellerBalance->decrement('balance_to_be_paid', $payoutRequest->amount);

                    // Update payout request status
                    $payoutRequest->update([
                        'status' => 'approved',
                        'processed_at' => now(),
                        'processed_by' => auth()->id()
                    ]);

                    // Log the successful payout
                    \Log::info('Payout request approved', [
                        'payout_request_id' => $payoutRequest->id,
                        'seller_id' => $payoutRequest->seller_id,
                        'amount' => $payoutRequest->amount,
                        'new_available_balance' => $sellerBalance->fresh()->available_balance
                    ]);
                } else {
                    throw new \Exception('Insufficient available balance for payout');
                }
            } elseif ($status === 'rejected') {
                $payoutRequest->update([
                    'status' => 'rejected',
                    'rejection_reason' => $rejectionReason,
                    'processed_at' => now(),
                    'processed_by' => auth()->id()
                ]);

                // Log the rejection
                \Log::info('Payout request rejected', [
                    'payout_request_id' => $payoutRequest->id,
                    'seller_id' => $payoutRequest->seller_id,
                    'amount' => $payoutRequest->amount,
                    'reason' => $rejectionReason
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

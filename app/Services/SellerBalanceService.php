<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SellerBalance;
use Illuminate\Support\Facades\DB;

class SellerBalanceService
{
    public function updateBalanceForOrderStatus(Order $order, string $newStatus, ?string $previousStatus = null)
    {
        DB::beginTransaction();
        try {
            foreach ($order->items->groupBy('seller_id') as $sellerId => $items) {
                // First, ensure the seller balance record exists
                SellerBalance::firstOrCreate(
                    ['seller_id' => $sellerId],
                    [
                        'available_balance' => 0,
                        'pending_balance' => 0,
                        'total_earned' => 0,
                        'balance_to_be_paid' => 0
                    ]
                );

                // Calculate the total amount for this order's items for this seller
                $orderAmount = $items->sum(function($item) {
                    return $item->price * $item->quantity;
                });

                // Update balances based on status change
                if ($newStatus === 'cancelled') {
                    if ($previousStatus === 'pending' || $previousStatus === 'processing') {
                        DB::statement("
                            UPDATE seller_balances
                            SET pending_balance = pending_balance - ?,
                                total_earned = total_earned - ?
                            WHERE seller_id = ?
                        ", [$orderAmount, $orderAmount, $sellerId]);
                    } elseif (in_array($previousStatus, ['completed', 'delivered', 'confirmed', 'delivered_to_warehouse', 'dispatched'])) {
                        DB::statement("
                            UPDATE seller_balances
                            SET available_balance = available_balance - ?,
                                total_earned = total_earned - ?
                            WHERE seller_id = ?
                        ", [$orderAmount, $orderAmount, $sellerId]);
                    }
                } elseif (in_array($newStatus, ['completed', 'delivered', 'confirmed', 'delivered_to_warehouse', 'dispatched'])) {
                    if ($previousStatus === 'pending' || $previousStatus === 'processing') {
                        DB::statement("
                            UPDATE seller_balances
                            SET pending_balance = pending_balance - ?,
                                available_balance = available_balance + ?
                            WHERE seller_id = ?
                        ", [$orderAmount, $orderAmount, $sellerId]);
                    } elseif ($previousStatus === null) {
                        DB::statement("
                            UPDATE seller_balances
                            SET available_balance = available_balance + ?,
                                total_earned = total_earned + ?
                            WHERE seller_id = ?
                        ", [$orderAmount, $orderAmount, $sellerId]);
                    }
                } elseif ($newStatus === 'pending' || $newStatus === 'processing') {
                    if ($previousStatus === null) {
                        DB::statement("
                            UPDATE seller_balances
                            SET pending_balance = pending_balance + ?,
                                total_earned = total_earned + ?
                            WHERE seller_id = ?
                        ", [$orderAmount, $orderAmount, $sellerId]);
                    }
                }

                // Update balance_to_be_paid
                DB::statement("
                    UPDATE seller_balances
                    SET balance_to_be_paid = available_balance + pending_balance
                    WHERE seller_id = ?
                ", [$sellerId]);

                // Log the updated balances for debugging
                $balance = SellerBalance::where('seller_id', $sellerId)->first();
                \Log::info("Updated balance for seller $sellerId", [
                    'pending' => $balance->pending_balance,
                    'available' => $balance->available_balance,
                    'total' => $balance->total_earned,
                    'order_id' => $order->id,
                    'order_amount' => $orderAmount,
                    'new_status' => $newStatus,
                    'previous_status' => $previousStatus
                ]);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to update seller balance: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
                'new_status' => $newStatus,
                'previous_status' => $previousStatus
            ]);
            throw $e;
        }
    }

    public function updateBalanceForOrderConfirmation(Order $order)
    {
        return $this->updateBalanceForOrderStatus($order, $order->delivery_status, $order->delivery_status);
    }

    public function updateBalanceForNewOrder(Order $order)
    {
        return $this->updateBalanceForOrderStatus($order, 'pending', null);
    }
}

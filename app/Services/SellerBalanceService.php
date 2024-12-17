<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SellerBalance;
use Illuminate\Support\Facades\DB;

class SellerBalanceService
{
    public function updateBalanceForOrderStatus(Order $order, string $newStatus, ?string $previousStatus = null)
    {
        \Log::info("Starting balance update", [
            'order_id' => $order->id,
            'new_status' => $newStatus,
            'previous_status' => $previousStatus
        ]);

        DB::beginTransaction();
        try {
            // First calculate all seller shares including delivery fees
            $sellerShares = $order->calculateSellerShares();
            
            foreach ($sellerShares as $sellerId => $totalAmount) {
                // Get the seller's items for this order
                $items = $order->items->where('seller_id', $sellerId);
                
                // Calculate product amount and delivery fee separately
                $productAmount = $items->sum(function($item) {
                    return $item->price * $item->quantity;
                });
                
                $deliveryFeeShare = $totalAmount - $productAmount;

                \Log::info("Processing balance update", [
                    'seller_id' => $sellerId,
                    'amount' => $totalAmount,
                    'new_status' => $newStatus
                ]);

                // Get current balance
                $balance = SellerBalance::where('seller_id', $sellerId)->first();
                if (!$balance) {
                    $balance = new SellerBalance();
                    $balance->seller_id = $sellerId;
                    $balance->available_balance = 0;
                    $balance->pending_balance = 0;
                    $balance->total_earned = 0;
                    $balance->balance_to_be_paid = 0;
                    $balance->total_delivery_fees_earned = 0;
                    $balance->save();
                }

                if ($newStatus === 'delivered_to_warehouse') {
                    // Super simple update - just move the amount
                    $balance->pending_balance -= $totalAmount;
                    $balance->available_balance += $totalAmount;
                    $balance->save();

                    \Log::info("Updated balance for delivered_to_warehouse", [
                        'seller_id' => $sellerId,
                        'amount_moved' => $totalAmount,
                        'new_pending' => $balance->pending_balance,
                        'new_available' => $balance->available_balance
                    ]);
                }
                elseif ($newStatus === 'cancelled') {
                    $balance->pending_balance -= $totalAmount;
                    $balance->total_earned -= $totalAmount;
                    $balance->total_delivery_fees_earned -= $deliveryFeeShare;
                    $balance->save();
                }
                elseif (($newStatus === 'pending' || $newStatus === 'processing') && $previousStatus === null) {
                    $balance->pending_balance += $totalAmount;
                    $balance->total_earned += $totalAmount;
                    $balance->total_delivery_fees_earned += $deliveryFeeShare;
                    $balance->save();
                }

                // Update balance to be paid
                $balance->balance_to_be_paid = $balance->available_balance + $balance->pending_balance;
                $balance->save();

                \Log::info("Final balance state", [
                    'seller_id' => $sellerId,
                    'pending' => $balance->pending_balance,
                    'available' => $balance->available_balance,
                    'total' => $balance->total_earned
                ]);
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to update seller balance: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

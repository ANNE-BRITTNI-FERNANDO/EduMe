<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SellerBalance;
use App\Services\PayoutService;
use Illuminate\Support\Facades\DB;

class SellerBalanceService
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function updateBalanceForOrderStatus(Order $order, string $newStatus, ?string $previousStatus = null)
    {
        \Log::info("Starting balance update", [
            'order_id' => $order->id,
            'new_status' => $newStatus,
            'previous_status' => $previousStatus
        ]);

        // Only update balance when order is first marked as delivered_to_warehouse
        if ($newStatus === 'delivered_to_warehouse' && $previousStatus !== 'delivered_to_warehouse') {
            // Get the seller balance
            $sellerBalance = SellerBalance::where('seller_id', $order->seller_id)->first();
            
            if (!$sellerBalance) {
                \Log::error("No seller balance found", [
                    'seller_id' => $order->seller_id,
                    'order_id' => $order->id
                ]);
                return false;
            }

            // Calculate order amount
            $orderAmount = $order->items()
                ->where('seller_id', $order->seller_id)
                ->sum(DB::raw('price * quantity'));

            \Log::info("Adding earnings from order", [
                'order_id' => $order->id,
                'amount' => $orderAmount,
                'seller_id' => $order->seller_id
            ]);

            // Update the balance
            DB::transaction(function () use ($sellerBalance, $orderAmount) {
                $sellerBalance->total_earned += $orderAmount;
                $sellerBalance->available_balance += $orderAmount;
                $sellerBalance->balance_to_be_paid += $orderAmount;
                $sellerBalance->save();
            });

            return true;
        }

        // For other status changes, just update the order status without affecting the balance
        $order->status = $newStatus;
        $order->save();

        return true;
    }

    public function updateBalanceForOrderConfirmation(Order $order)
    {
        \Log::info("Starting balance update for order confirmation", [
            'order_id' => $order->id
        ]);

        return $this->payoutService->updateSellerBalances($order);
    }

    public function updateBalanceForNewOrder(Order $order)
    {
        return $this->updateBalanceForOrderStatus($order, 'pending', null);
    }
}

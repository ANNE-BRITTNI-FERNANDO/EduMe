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

        // Use the new PayoutService to handle delivery status changes
        if ($newStatus === 'delivered_to_warehouse' || $newStatus === 'cancelled') {
            return $this->payoutService->updateBalanceForDeliveryStatus($order, $newStatus, $previousStatus);
        }

        // For other status changes, update the order status
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

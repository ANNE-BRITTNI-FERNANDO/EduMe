<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\PayoutService;

class OrderObserver
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function updated(Order $order)
    {
        // Check if delivery_status was changed
        if ($order->isDirty('delivery_status')) {
            $previousStatus = $order->getOriginal('delivery_status');
            $newStatus = $order->delivery_status;

            \Log::info("Order status changed in observer", [
                'order_id' => $order->id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus
            ]);

            if ($newStatus === 'delivered_to_warehouse') {
                $this->payoutService->updateBalanceForDeliveryStatus($order, $newStatus, $previousStatus);
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use App\Notifications\OrderStatusUpdated;

class UpdateOrderStatuses extends Command
{
    protected $signature = 'orders:update-statuses';
    protected $description = 'Update order statuses automatically based on time elapsed';

    public function handle()
    {
        $this->info('Starting order status updates...');

        // Find orders that are marked as delivered to warehouse
        $orders = Order::where('delivery_status', 'delivered_to_warehouse')
            ->where('status_updated_at', '<=', now()->subMinutes(30))
            ->get();

        foreach ($orders as $order) {
            // Check if dispatch is scheduled
            if (Cache::has("order_{$order->id}_dispatch_scheduled")) {
                $this->updateOrderStatus($order, 'dispatched');
                Cache::forget("order_{$order->id}_dispatch_scheduled");
                
                // Schedule delivery after 2 hours
                Cache::put(
                    "order_{$order->id}_delivery_scheduled",
                    true,
                    now()->addHours(2)
                );
            }
        }

        // Find orders that are marked as dispatched
        $orders = Order::where('delivery_status', 'dispatched')
            ->where('status_updated_at', '<=', now()->subHours(2))
            ->get();

        foreach ($orders as $order) {
            // Check if delivery is scheduled
            if (Cache::has("order_{$order->id}_delivery_scheduled")) {
                $this->updateOrderStatus($order, 'delivered');
                Cache::forget("order_{$order->id}_delivery_scheduled");
            }
        }

        $this->info('Order status updates completed.');
    }

    private function updateOrderStatus($order, $status)
    {
        $order->update([
            'delivery_status' => $status,
            'status_updated_at' => now()
        ]);

        // Create delivery tracking entry
        $order->deliveryTracking()->create([
            'status' => $status,
            'description' => $this->getStatusDescription($status),
            'location' => $this->getStatusLocation($status, $order)
        ]);

        // Send notification to buyer with appropriate message
        $message = $this->getNotificationMessage($status);
        $order->user->notify(new OrderStatusUpdated($order, $message));
    }

    private function getStatusDescription($status)
    {
        $descriptions = [
            'dispatched' => 'Package has been dispatched for delivery',
            'delivered' => 'Package has been delivered to the customer'
        ];

        return $descriptions[$status] ?? 'Status updated';
    }

    private function getStatusLocation($status, $order)
    {
        switch ($status) {
            case 'dispatched':
                return 'In Transit';
            case 'delivered':
                return $order->shipping_address;
            default:
                return '';
        }
    }

    private function getNotificationMessage($status)
    {
        switch ($status) {
            case 'dispatched':
                return 'Your order has been dispatched and is on its way to you!';
            case 'delivered':
                return 'Your order has been delivered. Thank you for shopping with us!';
            default:
                return 'Your order status has been updated.';
        }
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $message;

    public function __construct(Order $order, string $message)
    {
        $this->order = $order;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Status Update - Order #' . $this->order->id)
            ->line($this->message)
            ->line('Order Details:')
            ->line('Order ID: ' . $this->order->id)
            ->line('Status: ' . ucfirst($this->order->delivery_status))
            ->action('View Order', url('/orders/' . $this->order->id));
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'message' => $this->message,
            'status' => $this->order->delivery_status,
        ];
    }
}

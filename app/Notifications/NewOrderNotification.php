<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('You have received a new order!')
            ->line('Order ID: ' . $this->order->id)
            ->line('Total Amount: ₹' . number_format($this->order->total_amount, 2))
            ->action('View Order', url('/seller/orders/' . $this->order->id))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'amount' => $this->order->total_amount,
            'buyer_name' => $this->order->user->name,
            'message' => 'New order received'
        ];
    }
}

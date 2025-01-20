<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class ProductApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Product Has Been Approved')
            ->line('Good news! Your product "' . $this->product->product_name . '" has been approved.')
            ->line('It is now visible to buyers on our platform.')
            ->action('View Product', route('seller.products.edit', $this->product->id))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'message' => 'Your product "' . $this->product->product_name . '" has been approved.',
            'type' => 'product_approved'
        ];
    }
}

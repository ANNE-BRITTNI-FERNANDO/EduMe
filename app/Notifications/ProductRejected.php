<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class ProductRejected extends Notification
{
    use Queueable;

    protected $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Product Listing Rejected')
            ->line('Your product listing has been rejected.')
            ->line('Product Name: ' . $this->product->product_name);

        if ($this->product->rejection_reason) {
            $message->line('Rejection Reason: ' . $this->product->rejection_reason);
        }

        if ($this->product->rejection_note) {
            $message->line('Additional Notes: ' . $this->product->rejection_note);
        }

        return $message
            ->line('Please review and make the necessary adjustments before resubmitting.')
            ->action('View Product', url('/seller/products/' . $this->product->id))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'rejection_reason' => $this->product->rejection_reason,
            'rejection_note' => $this->product->rejection_note,
            'message' => 'Your product listing has been rejected.',
            'action_url' => url('/seller/products/' . $this->product->id),
            'type' => 'product_rejected'
        ];
    }
}

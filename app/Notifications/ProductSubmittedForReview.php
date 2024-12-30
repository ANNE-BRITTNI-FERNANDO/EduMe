<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductSubmittedForReview extends Notification
{
    use Queueable;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->product->resubmitted ? 'Product Resubmitted for Review' : 'New Product Submitted for Review')
            ->line($this->product->resubmitted ? 
                'A product has been resubmitted for review.' : 
                'A new product has been submitted for review.')
            ->line('Product Name: ' . $this->product->product_name)
            ->line('Seller: ' . $this->product->user->name);

        if ($this->product->resubmitted) {
            $message->line('This is a resubmission of a previously rejected product.');
        }

        return $message
            ->action('Review Product', url('/admin/products/pending'))
            ->line('Please review this product at your earliest convenience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->product_name,
            'seller_name' => $this->product->user->name,
            'resubmitted' => $this->product->resubmitted,
            'message' => $this->product->resubmitted ? 
                'Product has been resubmitted for review.' : 
                'New product submitted for review.',
            'action_url' => url('/admin/products/pending')
        ];
    }
}

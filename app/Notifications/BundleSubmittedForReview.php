<?php

namespace App\Notifications;

use App\Models\Bundle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BundleSubmittedForReview extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bundle;

    public function __construct(Bundle $bundle)
    {
        $this->bundle = $bundle;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Bundle Submitted for Review')
            ->greeting('Hello Admin!')
            ->line('A new bundle has been submitted for review.')
            ->line('Bundle Name: ' . $this->bundle->bundle_name)
            ->line('Seller: ' . $this->bundle->user->name)
            ->action('Review Bundle', url('/admin/bundles/' . $this->bundle->id))
            ->line('Please review this bundle at your earliest convenience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bundle_id' => $this->bundle->id,
            'bundle_name' => $this->bundle->bundle_name,
            'seller_id' => $this->bundle->user_id,
            'seller_name' => $this->bundle->user->name,
            'message' => 'New bundle "' . $this->bundle->bundle_name . '" submitted for review',
            'type' => 'bundle_submitted_for_review'
        ];
    }
}

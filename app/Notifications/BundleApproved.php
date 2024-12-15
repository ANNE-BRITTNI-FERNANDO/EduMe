<?php

namespace App\Notifications;

use App\Models\Bundle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BundleApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bundle;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bundle $bundle)
    {
        $this->bundle = $bundle;
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
        return (new MailMessage)
            ->subject('Bundle Offer Approved!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! Your bundle offer "' . $this->bundle->bundle_name . '" has been approved.')
            ->line('Your bundle is now live and available for purchase.')
            ->action('View Bundle', url('/seller/bundles/' . $this->bundle->id))
            ->line('Thank you for being a part of our marketplace!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'bundle_id' => $this->bundle->id,
            'bundle_name' => $this->bundle->bundle_name,
            'message' => 'Your bundle offer has been approved!',
            'type' => 'bundle_approved'
        ];
    }
}

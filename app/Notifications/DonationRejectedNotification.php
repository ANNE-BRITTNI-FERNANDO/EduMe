<?php

namespace App\Notifications;

use App\Models\DonationItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $donation;

    public function __construct(DonationItem $donation)
    {
        $this->donation = $donation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Donation Has Been Rejected')
            ->line('Unfortunately, your donation has been rejected.')
            ->line('Donation Details:')
            ->line('Item: ' . $this->donation->item_name)
            ->line('Reason: ' . $this->donation->rejection_reason)
            ->action('View Details', url('/donations/' . $this->donation->id))
            ->line('If you have any questions, please contact us.');
    }

    public function toArray($notifiable)
    {
        return [
            'donation_id' => $this->donation->id,
            'item_name' => $this->donation->item_name,
            'category' => $this->donation->category,
            'education_level' => $this->donation->education_level,
            'quantity' => $this->donation->quantity,
            'message' => 'Your donation has been rejected',
            'rejection_reason' => $this->donation->rejection_reason,
            'type' => 'donation_rejected'
        ];
    }
}

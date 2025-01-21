<?php

namespace App\Notifications;

use App\Models\DonationItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Your Donation Has Been Approved')
            ->line('Good news! Your donation has been approved.')
            ->line('Donation Details:')
            ->line('Item: ' . $this->donation->item_name)
            ->line('Quantity: ' . $this->donation->quantity)
            ->action('View Donation', url('/donations/' . $this->donation->id))
            ->line('Thank you for your generosity!');
    }

    public function toArray($notifiable)
    {
        return [
            'donation_id' => $this->donation->id,
            'item_name' => $this->donation->item_name,
            'category' => $this->donation->category,
            'education_level' => $this->donation->education_level,
            'quantity' => $this->donation->quantity,
            'message' => 'Your donation has been approved',
            'type' => 'donation_approved'
        ];
    }
}

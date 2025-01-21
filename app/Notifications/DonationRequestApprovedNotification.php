<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DonationRequestApprovedNotification extends Notification
{
    use Queueable;

    protected $donationRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(DonationRequest $donationRequest)
    {
        $this->donationRequest = $donationRequest;
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
        $donationItem = $this->donationRequest->donationItem;
        
        return (new MailMessage)
            ->subject('Your Donation Request Has Been Approved!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Great news! Your request for the following donation has been approved:')
            ->line('Item: ' . $donationItem->item_name)
            ->line('Quantity: ' . $this->donationRequest->quantity)
            ->line('You can now proceed with collecting your donation.')
            ->line('Please make sure to bring your verification documents when collecting the donation.')
            ->action('View Request Details', url('/donations/requests/' . $this->donationRequest->id))
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
            'donation_request_id' => $this->donationRequest->id,
            'donation_item_id' => $this->donationRequest->donation_item_id,
            'item_name' => $this->donationRequest->donationItem->item_name,
            'quantity' => $this->donationRequest->quantity,
            'status' => 'approved',
            'message' => 'Your donation request has been approved!'
        ];
    }
}

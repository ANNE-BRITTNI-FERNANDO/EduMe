<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DonationRequestApproved extends Notification
{
    use Queueable;

    protected $donationRequest;

    public function __construct(DonationRequest $donationRequest)
    {
        $this->donationRequest = $donationRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Donation Request Approved')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your donation request has been approved!')
            ->line('Details:');

        if ($this->donationRequest->donationItem) {
            $message->line('Item: ' . $this->donationRequest->donationItem->item_name)
                   ->line('Category: ' . $this->donationRequest->donationItem->category)
                   ->line('Quantity: ' . $this->donationRequest->quantity);
        }

        if ($this->donationRequest->monetaryDonation) {
            $message->line('Amount: ₹' . number_format($this->donationRequest->monetaryDonation->amount, 2));
        }

        return $message->line('Thank you for using our platform!')
                      ->action('View Request Details', url('/donations/requests/' . $this->donationRequest->id));
    }

    public function toArray($notifiable)
    {
        return [
            'donation_request_id' => $this->donationRequest->id,
            'status' => 'approved',
            'message' => 'Your donation request has been approved'
        ];
    }
}

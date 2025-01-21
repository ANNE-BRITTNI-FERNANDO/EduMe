<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DonationRequestRejected extends Notification
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
            ->subject('Donation Request Rejected')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your donation request has been rejected.')
            ->line('Details:');

        if ($this->donationRequest->donationItem) {
            $message->line('Item: ' . $this->donationRequest->donationItem->item_name)
                   ->line('Category: ' . $this->donationRequest->donationItem->category)
                   ->line('Quantity: ' . $this->donationRequest->quantity);
        }

        if ($this->donationRequest->monetaryDonation) {
            $message->line('Amount: ₹' . number_format($this->donationRequest->monetaryDonation->amount, 2));
        }

        $message->line('Reason for rejection: ' . $this->donationRequest->rejection_reason);

        return $message;
    }

    public function toArray($notifiable)
    {
        return [
            'donation_request_id' => $this->donationRequest->id,
            'type' => $this->donationRequest->type,
            'status' => 'rejected',
            'message' => 'Your donation request was not approved'
        ];
    }
}

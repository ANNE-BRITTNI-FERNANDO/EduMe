<?php

namespace App\Notifications;

use App\Models\DonationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDonationRequest extends Notification implements ShouldQueue
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
        $type = $this->donationRequest->type === 'monetary' ? 'Funding' : 'Item';
        
        return (new MailMessage)
            ->subject("New {$type} Request Received")
            ->line("A new {$type} request has been submitted.")
            ->line("Purpose: " . ucfirst($this->donationRequest->purpose))
            ->line("Details: " . $this->donationRequest->purpose_details)
            ->action('Review Request', url('/admin/donations'))
            ->line('Please review this request at your earliest convenience.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'donation_request',
            'message' => 'New donation request received',
            'donation_request_id' => $this->donationRequest->id,
            'donation_type' => $this->donationRequest->type,
            'purpose' => $this->donationRequest->purpose,
            'status' => $this->donationRequest->status,
        ];
    }
}

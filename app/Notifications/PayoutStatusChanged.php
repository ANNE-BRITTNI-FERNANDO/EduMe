<?php

namespace App\Notifications;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payoutRequest;

    public function __construct(PayoutRequest $payoutRequest)
    {
        $this->payoutRequest = $payoutRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = match($this->payoutRequest->status) {
            'approved' => 'Your payout request has been approved and is being processed.',
            'completed' => 'Your payout has been successfully processed and sent to your account.',
            'rejected' => 'Your payout request has been rejected.',
            default => 'There has been an update to your payout request.'
        };

        $mailMessage = (new MailMessage)
            ->subject('Payout Request Update')
            ->greeting('Hello ' . $notifiable->name)
            ->line($message)
            ->line('Amount: $' . number_format($this->payoutRequest->amount, 2));

        if ($this->payoutRequest->notes) {
            $mailMessage->line('Notes: ' . $this->payoutRequest->notes);
        }

        $mailMessage->action('View Details', route('seller.payouts.show', $this->payoutRequest->id))
            ->line('Thank you for using our platform!');

        return $mailMessage;
    }

    public function toArray($notifiable)
    {
        return [
            'payout_request_id' => $this->payoutRequest->id,
            'amount' => $this->payoutRequest->amount,
            'status' => $this->payoutRequest->status,
            'notes' => $this->payoutRequest->notes,
            'processed_at' => $this->payoutRequest->processed_at,
        ];
    }
}

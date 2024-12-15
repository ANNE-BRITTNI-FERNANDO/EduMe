<?php

namespace App\Notifications;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutApproved extends Notification
{
    use Queueable;

    protected $payoutRequest;

    public function __construct(PayoutRequest $payoutRequest)
    {
        $this->payoutRequest = $payoutRequest;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payout Request Approved')
            ->line('Your payout request for $' . number_format($this->payoutRequest->amount, 2) . ' has been approved.')
            ->line('We will process your payment shortly and upload the receipt once completed.')
            ->action('View Payout Details', route('seller.payouts.show', $this->payoutRequest));
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Payout Request Approved',
            'message' => 'Your payout request #' . $this->payoutRequest->id . ' for $' . number_format($this->payoutRequest->amount, 2) . ' has been approved and will be processed shortly.',
            'payout_id' => $this->payoutRequest->id,
            'amount' => $this->payoutRequest->amount,
            'status' => $this->payoutRequest->status,
            'processed_at' => $this->payoutRequest->processed_at?->format('M d, Y H:i A'),
            'type' => 'payout_approved'
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutCompleted extends Notification
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
            ->subject('Payout Completed')
            ->line('Your payout request for $' . number_format($this->payoutRequest->amount, 2) . ' has been completed.')
            ->line('Transaction ID: ' . $this->payoutRequest->transaction_id)
            ->line('The payment has been processed and the receipt has been uploaded.')
            ->action('View Payout Details', route('seller.payouts.show', $this->payoutRequest));
    }

    public function toArray($notifiable)
    {
        return [
            'payout_id' => $this->payoutRequest->id,
            'amount' => $this->payoutRequest->amount,
            'transaction_id' => $this->payoutRequest->transaction_id,
            'completed_at' => $this->payoutRequest->completed_at?->format('M d, Y H:i A'),
            'receipt_path' => $this->payoutRequest->receipt_path
        ];
    }
}

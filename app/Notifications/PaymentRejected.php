<?php

namespace App\Notifications;

use App\Models\BankTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentRejected extends Notification
{
    use Queueable;

    protected $bankTransfer;

    public function __construct(BankTransfer $bankTransfer)
    {
        $this->bankTransfer = $bankTransfer;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $url = route('chat.show', $this->bankTransfer->conversation_id);

        return (new MailMessage)
            ->subject('Payment Rejected')
            ->line('Your payment of $' . number_format($this->bankTransfer->amount, 2) . ' has been rejected.')
            ->line('Reason: ' . $this->bankTransfer->rejection_reason)
            ->action('View Details', $url)
            ->line('Please upload a new payment slip or contact the seller for more information.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment_rejected',
            'bank_transfer_id' => $this->bankTransfer->id,
            'conversation_id' => $this->bankTransfer->conversation_id,
            'amount' => $this->bankTransfer->amount,
            'payment_slip_path' => $this->bankTransfer->payment_slip_path,
            'rejection_reason' => $this->bankTransfer->rejection_reason,
            'rejected_at' => $this->bankTransfer->rejected_at,
        ];
    }
}

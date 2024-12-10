<?php

namespace App\Notifications;

use App\Models\BankTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentSlipUploaded extends Notification
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
            ->subject('New Payment Slip Uploaded')
            ->line('A buyer has uploaded a payment slip for $' . number_format($this->bankTransfer->amount, 2))
            ->action('View Payment', $url)
            ->line('Please review and confirm the payment.');
    }

    public function toArray($notifiable)
    {
        return [
            'conversation_id' => $this->bankTransfer->conversation_id,
            'amount' => $this->bankTransfer->amount,
            'payment_slip_path' => $this->bankTransfer->payment_slip_path,
            'bank_details' => $this->bankTransfer->bank_details,
            'status' => $this->bankTransfer->status,
            'created_at' => $this->bankTransfer->created_at,
        ];
    }
}

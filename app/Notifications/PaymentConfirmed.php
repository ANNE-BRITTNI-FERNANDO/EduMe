<?php

namespace App\Notifications;

use App\Models\BankTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentConfirmed extends Notification
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
            ->subject('Payment Confirmed')
            ->line('Your payment of $' . number_format($this->bankTransfer->amount, 2) . ' has been confirmed.')
            ->action('View Details', $url)
            ->line('Thank you for your purchase!');
    }

    public function toArray($notifiable)
    {
        return [
            'conversation_id' => $this->bankTransfer->conversation_id,
            'amount' => $this->bankTransfer->amount,
            'payment_slip_path' => $this->bankTransfer->payment_slip_path,
            'confirmed_at' => $this->bankTransfer->confirmed_at,
        ];
    }
}

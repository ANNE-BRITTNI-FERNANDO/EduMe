<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDonationMessage extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        $conversation = $this->message->conversation;
        $sender = $this->message->sender;
        $title = $conversation->getTitle();

        return [
            'message_id' => $this->message->id,
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'sender_name' => $sender->name,
            'content' => $this->message->content,
            'title' => $title,
            'type' => 'donation_message',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}

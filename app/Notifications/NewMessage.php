<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $conversation = $this->message->conversation;
        $sender = $this->message->sender;
        
        $subject = $conversation->product_id 
            ? 'New message about product: ' . $conversation->product->name
            : 'New message about bundle: ' . $conversation->bundle->bundle_name;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new message from ' . $sender->name)
            ->line('Message: ' . substr($this->message->content, 0, 100) . (strlen($this->message->content) > 100 ? '...' : ''))
            ->action('View Conversation', url('/conversations/' . $conversation->id))
            ->line('Reply to continue the conversation.');
    }

    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;
        return [
            'message_id' => $this->message->id,
            'conversation_id' => $conversation->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'content_preview' => substr($this->message->content, 0, 100),
            'product_id' => $conversation->product_id,
            'bundle_id' => $conversation->bundle_id,
            'type' => 'new_message'
        ];
    }
}

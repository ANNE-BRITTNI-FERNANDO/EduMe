<?php

namespace App\Notifications;

use App\Models\Bundle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BundleStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bundle;
    protected $action;

    public function __construct(Bundle $bundle, string $action)
    {
        $this->bundle = $bundle;
        $this->action = $action;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match($this->action) {
            'approved' => 'Your Bundle Has Been Approved!',
            'rejected' => 'Bundle Update Required',
            'edited' => 'Bundle Update Notification',
            default => 'Bundle Status Update'
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!');

        switch($this->action) {
            case 'approved':
                $message->line('Great news! Your bundle "' . $this->bundle->bundle_name . '" has been approved.')
                        ->line('Your bundle is now live and available for purchase.')
                        ->action('View Bundle', url('/bundles/' . $this->bundle->id));
                break;
            case 'rejected':
                $message->line('Your bundle "' . $this->bundle->bundle_name . '" needs some updates.')
                        ->line('Reason: ' . $this->bundle->rejection_reason)
                        ->line('Details: ' . $this->bundle->rejection_details)
                        ->action('Edit Bundle', url('/seller/bundles/' . $this->bundle->id . '/edit'))
                        ->line('Please make the necessary changes and submit for review again.');
                break;
            case 'edited':
                $message->line('Your bundle "' . $this->bundle->bundle_name . '" has been updated.')
                        ->action('View Bundle', url('/bundles/' . $this->bundle->id));
                break;
            default:
                $message->line('The status of your bundle "' . $this->bundle->bundle_name . '" has been updated.')
                        ->action('View Bundle', url('/bundles/' . $this->bundle->id));
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $message = match($this->action) {
            'approved' => 'Your bundle "' . $this->bundle->bundle_name . '" has been approved!',
            'rejected' => 'Your bundle "' . $this->bundle->bundle_name . '" needs some updates.',
            'edited' => 'Your bundle "' . $this->bundle->bundle_name . '" has been updated.',
            default => 'Your bundle "' . $this->bundle->bundle_name . '" status has been updated.'
        };

        return [
            'bundle_id' => $this->bundle->id,
            'bundle_name' => $this->bundle->bundle_name,
            'action' => $this->action,
            'message' => $message,
            'rejection_reason' => $this->bundle->rejection_reason,
            'rejection_details' => $this->bundle->rejection_details,
            'type' => 'bundle_status_updated'
        ];
    }
}

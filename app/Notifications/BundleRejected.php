<?php

namespace App\Notifications;

use App\Models\Bundle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BundleRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bundle;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bundle $bundle)
    {
        $this->bundle = $bundle;
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
        $message = (new MailMessage)
            ->subject('Bundle Offer Update Required')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your bundle offer "' . $this->bundle->bundle_name . '" requires some updates before it can be approved.');

        // Add main rejection reason if available
        if ($this->bundle->rejection_reason) {
            $message->line('Main Issue: ' . $this->formatRejectionReason($this->bundle->rejection_reason));
        }

        // Get all rejected categories
        $rejectedCategories = $this->bundle->categories()->where('status', 'rejected')->get();
        if ($rejectedCategories->isNotEmpty()) {
            $message->line('The following items need attention:');
            foreach ($rejectedCategories as $category) {
                if ($category->rejection_reason) {
                    $message->line('- ' . $category->category . ': ' . $this->formatRejectionReason($category->rejection_reason));
                }
            }
        }

        return $message
            ->action('Edit Bundle', url('/seller/bundles/' . $this->bundle->id . '/edit'))
            ->line('Please address these issues and resubmit your bundle for approval.');
    }

    /**
     * Format the rejection reason to be more readable
     */
    private function formatRejectionReason(string $reason): string
    {
        // Convert snake_case to Title Case and add additional context
        $formatted = ucwords(str_replace('_', ' ', $reason));

        // Add specific context based on the reason
        switch ($reason) {
            case 'low_quality_images':
                return $formatted . ' - Please provide higher resolution images (recommended: at least 1000x1000 pixels)';
            case 'missing_images':
                return $formatted . ' - Ensure all required product images are included';
            case 'inappropriate_images':
                return $formatted . ' - Images must comply with our content guidelines';
            case 'inappropriate_content':
                return $formatted . ' - Content must comply with our community guidelines';
            case 'misleading_information':
                return $formatted . ' - Please ensure all information is accurate and verifiable';
            case 'incomplete_description':
                return $formatted . ' - Please provide more detailed product descriptions';
            case 'incorrect_pricing':
                return $formatted . ' - Please review and adjust the pricing';
            case 'unreasonable_price':
                return $formatted . ' - Price should be competitive and reasonable for the market';
            case 'incompatible_items':
                return $formatted . ' - Bundle items should be complementary and make sense together';
            case 'missing_items':
                return $formatted . ' - Please ensure all necessary items are included in the bundle';
            default:
                return $formatted;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $rejectedCategories = $this->bundle->categories()
            ->where('status', 'rejected')
            ->get()
            ->map(function ($category) {
                return [
                    'category' => $category->category,
                    'reason' => $this->formatRejectionReason($category->rejection_reason)
                ];
            });

        return [
            'bundle_id' => $this->bundle->id,
            'bundle_name' => $this->bundle->bundle_name,
            'message' => 'Your bundle offer requires updates',
            'rejection_reason' => $this->formatRejectionReason($this->bundle->rejection_reason),
            'rejected_categories' => $rejectedCategories,
            'type' => 'bundle_rejected'
        ];
    }
}

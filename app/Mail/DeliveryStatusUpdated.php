<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\DeliveryTracking;

class DeliveryStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $tracking;

    public function __construct(DeliveryTracking $tracking)
    {
        $this->tracking = $tracking;
    }

    public function build()
    {
        return $this->markdown('emails.delivery-status-updated')
                    ->subject('Delivery Status Update - ' . ucwords(str_replace('_', ' ', $this->tracking->status)));
    }
}

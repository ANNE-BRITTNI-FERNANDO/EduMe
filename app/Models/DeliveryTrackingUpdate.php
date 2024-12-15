<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryTrackingUpdate extends Model
{
    protected $fillable = [
        'delivery_tracking_id',
        'status',
        'location',
        'description',
        'notes'
    ];

    public function tracking(): BelongsTo
    {
        return $this->belongsTo(DeliveryTracking::class, 'delivery_tracking_id');
    }
}

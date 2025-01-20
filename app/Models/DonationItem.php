<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_name',
        'education_level',
        'description',
        'quantity',
        'available_quantity',
        'condition',
        'category',
        'status',
        'is_anonymous',
        'contact_number',
        'pickup_address',
        'preferred_pickup_date',
        'received_at',
        'received_by',
        'review_started_at',
        'reviewed_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'notes',
        'preferred_contact_method',
        'preferred_contact_times',
        'show_contact_details',
        'images',
        
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'preferred_pickup_date' => 'datetime',
        'received_at' => 'datetime',
        'review_started_at' => 'datetime',
        'rejected_at' => 'datetime',
        'quantity' => 'integer',
        'available_quantity' => 'integer',
        'preferred_contact_times' => 'array',
        'show_contact_details' => 'boolean',
        'images' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Set initial available_quantity equal to quantity for new items
            if (!isset($item->available_quantity)) {
                $item->available_quantity = $item->quantity;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requests()
    {
        return $this->hasMany(DonationRequest::class);
    }

    public function images()
    {
        return $this->hasMany(DonationImage::class);
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the donation requests for this item.
     */
    public function donationRequests()
    {
        return $this->hasMany(DonationRequest::class, 'donation_item_id');
    }
}

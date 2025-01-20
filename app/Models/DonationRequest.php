<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DonationItem;
use App\Models\User;
use App\Models\Conversation;
use App\Models\DonationMessage;

class DonationRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'donation_item_id',
        'quantity',
        'purpose',
        'purpose_details',
        'contact_number',
        'preferred_contact_time',
        'notes',
        'status',
        'approved_at',
        'rejected_at',
        'rejected_by',
        'completed_at',
        'rejection_reason'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'quantity' => 'integer',
        'purpose_details' => 'array'
    ];

    protected $appends = ['verification_document_url', 'document_type', 'document_uploaded_at'];

    /**
     * Get the donation item associated with this request.
     */
    public function donationItem()
    {
        return $this->belongsTo(DonationItem::class);
    }

    public function messages()
    {
        return $this->hasMany(DonationMessage::class);
    }

    /**
     * Get the user who made this request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    public function hasChat()
    {
        return !is_null($this->conversation);
    }

    public function canChat()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Get the user who rejected this request.
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the formatted status for display.
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
            self::STATUS_APPROVED => '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Approved</span>',
            self::STATUS_REJECTED => '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Rejected</span>',
            default => '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Unknown</span>',
        };
    }

    public function getVerificationDocumentUrlAttribute()
    {
        $details = $this->purpose_details;
        return isset($details['document_path']) ? asset('storage/' . $details['document_path']) : null;
    }

    public function getDocumentTypeAttribute()
    {
        $details = $this->purpose_details;
        return isset($details['document_type']) ? $details['document_type'] : null;
    }

    public function getDocumentUploadedAtAttribute()
    {
        $details = $this->purpose_details;
        return isset($details['uploaded_at']) ? $details['uploaded_at'] : null;
    }

    public function getStatusAttribute($value)
    {
        return strtolower($value);
    }
}

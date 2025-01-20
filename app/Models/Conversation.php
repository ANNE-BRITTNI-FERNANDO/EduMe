<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'bundle_id',
        'donation_request_id',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime'
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function donationRequest(): BelongsTo
    {
        return $this->belongsTo(DonationRequest::class);
    }

    public function lastMessage()
    {
        return $this->messages()->latest()->first();
    }

    public function bankTransfers(): HasMany
    {
        return $this->hasMany(BankTransfer::class);
    }

    public function getOtherParticipant(User $user)
    {
        return $user->id === $this->buyer_id ? $this->seller : $this->buyer;
    }

    public function isDonationChat()
    {
        return !is_null($this->donation_request_id);
    }

    public function getTitle()
    {
        if ($this->isDonationChat() && $this->donationRequest && $this->donationRequest->donationItem) {
            return 'Donation: ' . $this->donationRequest->donationItem->item_name;
        }
        if ($this->product) {
            return $this->product->name;
        }
        if ($this->bundle) {
            return $this->bundle->name;
        }
        return 'Conversation';
    }
}

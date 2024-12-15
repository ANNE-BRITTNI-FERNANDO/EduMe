<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'seller_id',
        'item_type',
        'item_id',
        'price',
        'quantity',
        'delivery_fee_share'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'delivery_fee_share' => 'decimal:2',
        'quantity' => 'integer',
    ];

    protected $with = ['item'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function getTotalAttribute()
    {
        return ($this->price * $this->quantity) + $this->delivery_fee_share;
    }

    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}

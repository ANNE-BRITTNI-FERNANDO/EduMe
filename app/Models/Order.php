<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'item_id',
        'item_type',
        'total_amount',
        'payment_method',
        'status',
        'bank_transfer_details',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id')->where('item_type', 'product');
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'item_id')->where('item_type', 'bundle');
    }
}

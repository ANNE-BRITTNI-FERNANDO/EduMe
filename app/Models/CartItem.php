<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'bundle_id',
        'item_type'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function getNameAttribute()
    {
        if ($this->item_type === 'product' && $this->product) {
            return $this->product->name;
        } elseif ($this->item_type === 'bundle' && $this->bundle) {
            return $this->bundle->name;
        }
        return 'Unknown Item';
    }

    public function getPriceAttribute()
    {
        if ($this->item_type === 'product' && $this->product) {
            return $this->product->price;
        } elseif ($this->item_type === 'bundle' && $this->bundle) {
            return $this->bundle->price;
        }
        return 0;
    }
}

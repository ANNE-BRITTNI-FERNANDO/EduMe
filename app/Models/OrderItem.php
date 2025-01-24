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

    /**
     * Get the owning item model (Product or Course).
     */
    public function item(): MorphTo
    {
        return $this->morphTo('item', 'item_type', 'item_id')->withDefault([
            'name' => 'Unknown Item',
            'title' => 'Unknown Item'
        ]);
    }

    /**
     * Get the item name regardless of type
     */
    public function getItemNameAttribute()
    {
        if (!$this->item) {
            return 'Unknown Item';
        }

        // Convert item_type to lowercase for comparison
        $type = strtolower($this->item_type);

        if ($type === 'product') {
            return $this->item->product_name ?? 'Unknown Product';
        }

        if ($type === 'course') {
            return $this->item->title ?? 'Unknown Course';
        }

        if ($type === 'bundle') {
            return $this->item->name ?? 'Unknown Bundle';
        }

        return $this->item->product_name ?? $this->item->title ?? $this->item->name ?? 'Unknown Item';
    }

    /**
     * Get the product if this is a product order item
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id')
            ->where('item_type', 'product');
    }

    /**
     * Get the course if this is a course order item
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'item_id')
            ->where('item_type', 'course');
    }

    /**
     * Map the item type to the correct model class
     */
    public function getMorphClass()
    {
        $type = strtolower($this->item_type);
        
        $map = [
            'product' => Product::class,
            'course' => Course::class
        ];

        return $map[$type] ?? $this->item_type;
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orderItem) {
            // Convert the item_type to lowercase when creating
            $orderItem->item_type = strtolower($orderItem->item_type);
        });

        static::updating(function ($orderItem) {
            // Convert the item_type to lowercase when updating
            $orderItem->item_type = strtolower($orderItem->item_type);
        });
    }
}

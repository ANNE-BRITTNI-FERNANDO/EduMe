<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'delivery_fee',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_id',
        'bank_transfer_details',
        'shipping_address',
        'phone',
        'delivery_status',
        'buyer_province',
        'buyer_city',
        'warehouse_id',
        'warehouse_confirmed_at',
        'dispatched_at',
        'delivered_at',
        'status',
        'confirmed_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'warehouse_confirmed_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function sellers()
    {
        return $this->belongsToMany(User::class, 'order_items', 'order_id', 'seller_id')
                    ->distinct();
    }

    public function deliveryTracking(): HasMany
    {
        return $this->hasMany(DeliveryTracking::class);
    }

    public function payoutRequests(): HasManyThrough
    {
        return $this->hasManyThrough(
            PayoutRequest::class,
            OrderItem::class,
            'order_id',
            'order_item_id'
        );
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function updateDeliveryStatus($status, $description = null)
    {
        $this->delivery_status = $status;
        $this->save();

        return $this->deliveryTracking()->create([
            'status' => $status,
            'description' => $description,
            'location' => $this->buyer_city,
            'tracked_at' => now()
        ]);
    }

    public function calculateSellerShares()
    {
        $sellerShares = [];
        $totalItemsAmount = $this->items()->sum(DB::raw('price * quantity'));

        foreach ($this->items as $item) {
            $sellerId = $item->seller_id;
            if (!isset($sellerShares[$sellerId])) {
                $sellerShares[$sellerId] = 0;
            }

            // Calculate this item's share of the delivery fee based on its proportion of the total order
            $itemTotal = $item->price * $item->quantity;
            $deliveryFeeShare = ($itemTotal / $totalItemsAmount) * $this->delivery_fee;

            // Update the item's delivery fee share
            $item->update(['delivery_fee_share' => $deliveryFeeShare]);

            $sellerShares[$sellerId] += $itemTotal + $deliveryFeeShare;
        }

        return $sellerShares;
    }

    public function updateSellerBalances()
    {
        $sellerShares = $this->calculateSellerShares();

        foreach ($sellerShares as $sellerId => $amount) {
            $sellerBalance = SellerBalance::firstOrCreate(
                ['seller_id' => $sellerId],
                [
                    'available_balance' => 0,
                    'total_earned' => 0,
                    'balance_to_be_paid' => 0,
                    'pending_balance' => 0,
                    'total_delivery_fees_earned' => 0
                ]
            );

            $deliveryFeeShare = $this->items()
                ->where('seller_id', $sellerId)
                ->sum('delivery_fee_share');

            $sellerBalance->addEarnings($amount, $deliveryFeeShare);
        }
    }

    public function getStatusAttribute()
    {
        return $this->delivery_status;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['delivery_status'] = $value;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_number = 'ORD-' . strtoupper(uniqid());
        });
    }
}

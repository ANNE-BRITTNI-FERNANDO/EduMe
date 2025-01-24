<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Notifications\PayoutStatusChanged;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\SellerBalance;

class PayoutRequest extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'order_item_id',
        'amount',
        'status',
        'bank_name',
        'account_number',
        'account_holder_name',
        'bank_details',
        'receipt_path',
        'receipt_uploaded_at',
        'transaction_id',
        'approved_at',
        'completed_at',
        'cancelled_at',
        'notes',
        'processed_by',
        'rejection_reason',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bank_details' => 'array',
        'receipt_uploaded_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'processed_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get all order items associated with this payout request
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'seller_id', 'seller_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where(function($query) {
                $query->where('orders.delivery_status', '!=', 'cancelled')
                    ->orWhereNull('orders.delivery_status');
            })
            ->select('order_items.*', 'orders.delivery_status');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approve($adminId, $notes = null)
    {
        return DB::transaction(function () use ($adminId, $notes) {
            // Allow approval if status is null (new request) or pending
            if ($this->status !== self::STATUS_PENDING && $this->status !== null) {
                throw new \Exception('Only pending requests can be approved');
            }

            $this->status = self::STATUS_APPROVED;
            $this->processed_by = $adminId;
            $this->notes = $notes;
            $this->processed_at = now();
            $this->approved_at = now();
            $this->save();

            // Send notification
            if ($this->user) {
                $this->user->notify(new PayoutStatusChanged($this));
            }

            return true;
        });
    }

    public function complete($adminId, $notes = null)
    {
        return DB::transaction(function () use ($adminId, $notes) {
            if ($this->status !== self::STATUS_APPROVED) {
                throw new \Exception('Only approved requests can be completed');
            }

            $sellerBalance = $this->user->sellerBalance;
            $sellerBalance->deductFromPending($this->amount);

            $this->status = self::STATUS_COMPLETED;
            $this->processed_by = $adminId;
            $this->notes = $notes;
            $this->processed_at = now();
            $this->completed_at = now();
            $this->save();

            // Send notification
            $this->user->notify(new PayoutStatusChanged($this));

            return true;
        });
    }

    public function reject($adminId, $reason)
    {
        return DB::transaction(function () use ($adminId, $reason) {
            // Allow rejection if status is null (new request) or pending
            if ($this->status !== self::STATUS_PENDING && $this->status !== null) {
                throw new \Exception('Only pending requests can be rejected');
            }

            if ($this->user && $this->user->sellerBalance) {
                $sellerBalance = $this->user->sellerBalance;
                $sellerBalance->returnToPending($this->amount);
            }

            $this->status = self::STATUS_REJECTED;
            $this->processed_by = $adminId;
            $this->rejection_reason = $reason;
            $this->processed_at = now();
            $this->save();

            // Send notification
            if ($this->user) {
                $this->user->notify(new PayoutStatusChanged($this));
            }

            return $this;
        });
    }

    public function cancel($adminId, $reason)
    {
        return DB::transaction(function () use ($adminId, $reason) {
            if ($this->status !== self::STATUS_APPROVED) {
                throw new \Exception('Only approved requests can be cancelled');
            }

            $sellerBalance = $this->user->sellerBalance;
            $sellerBalance->returnToPending($this->amount);

            $this->status = self::STATUS_CANCELLED;
            $this->processed_by = $adminId;
            $this->rejection_reason = $reason;
            $this->cancelled_at = now();
            $this->save();

            // Send notification
            $this->user->notify(new PayoutStatusChanged($this));

            return $this;
        });
    }

    public function updateSellerBalance($status)
    {
        $sellerBalance = SellerBalance::where('seller_id', $this->seller_id)->first();
        if (!$sellerBalance) {
            $sellerBalance = SellerBalance::create([
                'seller_id' => $this->seller_id,
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0
            ]);
        }
        
        return $sellerBalance->processPayout($this->amount, $status);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payout) {
            // When creating a new payout request, update seller balance
            $payout->updateSellerBalance('pending');
        });

        static::updating(function ($payout) {
            if ($payout->isDirty('status')) {
                // When status changes, update seller balance
                $payout->updateSellerBalance($payout->status);
            }
        });
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'completed' => 'success',
            'cancelled' => 'secondary',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Notifications\PayoutStatusChanged;
use Illuminate\Support\Facades\DB;
use App\Models\OrderItem;
use App\Models\User;

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

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approve($adminId, $notes = null)
    {
        return DB::transaction(function () use ($adminId, $notes) {
            if ($this->status !== self::STATUS_PENDING) {
                throw new \Exception('Only pending requests can be approved');
            }

            $this->status = self::STATUS_APPROVED;
            $this->processed_by = $adminId;
            $this->notes = $notes;
            $this->processed_at = now();
            $this->approved_at = now();
            $this->save();

            // Send notification
            $this->user->notify(new PayoutStatusChanged($this));

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
            if ($this->status !== self::STATUS_PENDING) {
                throw new \Exception('Only pending requests can be rejected');
            }

            $sellerBalance = $this->user->sellerBalance;
            $sellerBalance->returnToPending($this->amount);

            $this->status = self::STATUS_REJECTED;
            $this->processed_by = $adminId;
            $this->rejection_reason = $reason;
            $this->save();

            // Send notification
            $this->user->notify(new PayoutStatusChanged($this));

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

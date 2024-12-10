<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Notifications\PayoutStatusChanged;
use Illuminate\Support\Facades\DB;

class PayoutRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'bank_name',
        'account_number',
        'account_holder_name',
        'notes',
        'processed_at',
        'processed_by',
        'rejection_reason',
        'bank_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'completed' => 'success',
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

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }
}

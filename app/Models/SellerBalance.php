<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SellerBalance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'seller_id',
        'available_balance',
        'total_earned',
        'balance_to_be_paid',
        'pending_balance',
        'total_delivery_fees_earned'
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'balance_to_be_paid' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_delivery_fees_earned' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function addEarnings($amount, $deliveryFeeShare = 0)
    {
        return DB::transaction(function () use ($amount, $deliveryFeeShare) {
            $this->available_balance += $amount;
            $this->total_earned += $amount;
            $this->total_delivery_fees_earned += $deliveryFeeShare;
            $this->save();
            
            return true;
        });
    }

    public function moveToPending($amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->available_balance < $amount) {
                throw new \Exception('Insufficient available balance');
            }

            $this->available_balance -= $amount;
            $this->pending_balance += $amount;
            $this->balance_to_be_paid += $amount;
            $this->save();

            return true;
        });
    }

    public function approvePayout($amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->pending_balance < $amount) {
                throw new \Exception('Insufficient pending balance');
            }

            $this->pending_balance -= $amount;
            $this->balance_to_be_paid -= $amount;
            $this->save();

            return true;
        });
    }

    public function rejectPayout($amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->pending_balance < $amount) {
                throw new \Exception('Insufficient pending balance');
            }

            $this->pending_balance -= $amount;
            $this->available_balance += $amount;
            $this->balance_to_be_paid -= $amount;
            $this->save();

            return true;
        });
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($balance) {
            // Ensure all balances start at 0 if not set
            $balance->available_balance = $balance->available_balance ?? 0;
            $balance->total_earned = $balance->total_earned ?? 0;
            $balance->balance_to_be_paid = $balance->balance_to_be_paid ?? 0;
            $balance->pending_balance = $balance->pending_balance ?? 0;
            $balance->total_delivery_fees_earned = $balance->total_delivery_fees_earned ?? 0;
        });
    }
}

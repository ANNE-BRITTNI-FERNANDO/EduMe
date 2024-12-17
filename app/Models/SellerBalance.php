<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Log;

class SellerBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'available_balance',
        'pending_balance',
        'total_earned',
        'balance_to_be_paid',
        'total_delivery_fees_earned'
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'balance_to_be_paid' => 'decimal:2',
        'total_delivery_fees_earned' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($balance) {
            $balance->available_balance = $balance->available_balance ?? 0;
            $balance->pending_balance = $balance->pending_balance ?? 0;
            $balance->total_earned = $balance->total_earned ?? 0;
            $balance->balance_to_be_paid = $balance->balance_to_be_paid ?? 0;
            $balance->total_delivery_fees_earned = $balance->total_delivery_fees_earned ?? 0;
        });

        static::saving(function ($balance) {
            // Ensure balance_to_be_paid is always the sum of available and pending
            $balance->balance_to_be_paid = $balance->available_balance + $balance->pending_balance;
        });
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function addEarnings($amount, $deliveryFeeShare = 0)
    {
        return DB::transaction(function () use ($amount, $deliveryFeeShare) {
            // First, add the delivery fee to total_delivery_fees_earned
            if ($deliveryFeeShare > 0) {
                DB::statement("
                    UPDATE seller_balances 
                    SET total_delivery_fees_earned = total_delivery_fees_earned + ?
                    WHERE seller_id = ?
                ", [$deliveryFeeShare, $this->seller_id]);
            }

            // Then add the total amount (including delivery fee) to pending balance
            return DB::statement("
                UPDATE seller_balances 
                SET pending_balance = pending_balance + ?,
                    total_earned = total_earned + ?,
                    balance_to_be_paid = available_balance + pending_balance + ?
                WHERE seller_id = ?
            ", [$amount, $amount, $amount, $this->seller_id]);
        });
    }

    public function addToAvailableBalance($amount)
    {
        return DB::transaction(function () use ($amount) {
            return DB::statement("
                UPDATE seller_balances 
                SET available_balance = available_balance + ?,
                    total_earned = total_earned + ?,
                    balance_to_be_paid = available_balance + pending_balance + ?
                WHERE seller_id = ?
            ", [$amount, $amount, $amount, $this->seller_id]);
        });
    }

    public function addToPendingBalance($amount)
    {
        return DB::transaction(function () use ($amount) {
            return DB::statement("
                UPDATE seller_balances 
                SET pending_balance = pending_balance + ?,
                    total_earned = total_earned + ?,
                    balance_to_be_paid = available_balance + pending_balance + ?
                WHERE seller_id = ?
            ", [$amount, $amount, $amount, $this->seller_id]);
        });
    }

    public function deductFromPending($amount)
    {
        return DB::transaction(function () use ($amount) {
            // First check if we have enough pending balance
            $currentBalance = DB::selectOne("
                SELECT pending_balance 
                FROM seller_balances 
                WHERE seller_id = ?
            ", [$this->seller_id])->pending_balance;

            if ($currentBalance < $amount) {
                throw new \Exception('Insufficient pending balance for payout');
            }

            return DB::statement("
                UPDATE seller_balances 
                SET pending_balance = pending_balance - ?,
                    balance_to_be_paid = available_balance + (pending_balance - ?)
                WHERE seller_id = ?
            ", [$amount, $amount, $this->seller_id]);
        });
    }

    public function movePendingToAvailable($amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->pending_balance < $amount) {
                throw new \Exception('Insufficient pending balance');
            }

            DB::statement("
                UPDATE seller_balances 
                SET pending_balance = pending_balance - ?,
                    available_balance = available_balance + ?
                WHERE seller_id = ?
            ", [$amount, $amount, $this->seller_id]);

            $this->refresh();
            return true;
        });
    }

    public function deductFromPendingBalance($amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->pending_balance < $amount) {
                throw new \Exception('Insufficient pending balance');
            }

            return DB::statement("
                UPDATE seller_balances 
                SET pending_balance = pending_balance - ?,
                    total_earned = total_earned - ?,
                    balance_to_be_paid = balance_to_be_paid - ?
                WHERE seller_id = ?
            ", [$amount, $amount, $amount, $this->seller_id]);
        });
    }

    public function deductFromAvailableBalance($amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->available_balance < $amount) {
                throw new \Exception('Insufficient available balance');
            }

            return DB::statement("
                UPDATE seller_balances 
                SET available_balance = available_balance - ?,
                    balance_to_be_paid = balance_to_be_paid - ?
                WHERE seller_id = ?
            ", [$amount, $amount, $this->seller_id]);
        });
    }
}

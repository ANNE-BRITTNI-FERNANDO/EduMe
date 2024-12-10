<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SellerBalance extends Model
{
    protected $fillable = [
        'user_id',
        'available_balance',
        'pending_balance',
        'total_earned'
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addEarnings($amount)
    {
        return DB::transaction(function () use ($amount) {
            $this->available_balance += $amount;
            $this->total_earned += $amount;
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
            $this->save();

            return true;
        });
    }

    public function deductFromPending($amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->pending_balance < $amount) {
                throw new \Exception('Insufficient pending balance');
            }

            $this->pending_balance -= $amount;
            $this->save();

            return true;
        });
    }

    public function returnToPending($amount)
    {
        return DB::transaction(function () use ($amount) {
            $this->pending_balance -= $amount;
            $this->available_balance += $amount;
            $this->save();

            return true;
        });
    }
}

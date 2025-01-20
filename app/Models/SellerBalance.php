<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PayoutRequest;
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
            // Ensure balances are never negative
            $balance->available_balance = max(0, $balance->available_balance);
            $balance->pending_balance = max(0, $balance->pending_balance);
            
            // Update balance to be paid
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

            // Add the total amount directly to available balance when delivered to warehouse
            return DB::statement("
                UPDATE seller_balances 
                SET available_balance = available_balance + ?,
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

    public function updateBalanceFromOrder($order, $amount, $status)
    {
        DB::beginTransaction();
        try {
            if ($status === 'delivered_to_warehouse') {
                // When order is delivered to warehouse, add directly to available balance
                $this->available_balance += $amount;
                $this->total_earned += $amount;
            }
            // Do nothing for other statuses - balance should not change
            
            $this->save();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update seller balance: ' . $e->getMessage());
            return false;
        }
    }

    public function processPayout($amount, $status)
    {
        DB::beginTransaction();
        try {
            switch ($status) {
                case 'pending':
                    // When payout is requested, move from available to pending
                    if ($this->available_balance >= $amount) {
                        $this->available_balance -= $amount;
                        $this->pending_balance += $amount;
                    }
                    break;

                case 'approved':
                    // When payout is approved, deduct from pending balance
                    if ($this->pending_balance >= $amount) {
                        $this->pending_balance -= $amount;
                    }
                    break;

                case 'rejected':
                    // When payout is rejected, move from pending back to available
                    if ($this->pending_balance >= $amount) {
                        $this->pending_balance -= $amount;
                        $this->available_balance += $amount;
                    }
                    break;
            }

            $this->save();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to process payout: ' . $e->getMessage());
            return false;
        }
    }

    public function recalculateBalance()
    {
        DB::transaction(function () {
            // 1. Calculate total earnings from all confirmed orders
            $totalEarnings = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('order_items.seller_id', $this->seller_id)
                ->whereNotNull('orders.warehouse_confirmed_at')
                ->sum(DB::raw('order_items.price * order_items.quantity'));

            // 2. Get payout amounts by status
            $payouts = DB::table('payout_requests')
                ->where('seller_id', $this->seller_id)
                ->select('status', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            // Get payout amounts by status
            $completedPayouts = isset($payouts['completed']) ? $payouts['completed']->total_amount : 0;
            $approvedPayouts = isset($payouts['approved']) ? $payouts['approved']->total_amount : 0;
            $pendingPayouts = isset($payouts['pending']) ? $payouts['pending']->total_amount : 0;
            // Rejected payouts don't affect any balances

            // 3. Calculate balances
            // Total earned is the sum of all confirmed orders
            $this->total_earned = $totalEarnings;
            
            // Balance to be paid = Total earned - Completed payouts
            $this->balance_to_be_paid = $totalEarnings - $completedPayouts;
            
            // Available balance = Balance to be paid - (Approved + Pending payouts)
            $this->available_balance = $this->balance_to_be_paid - $approvedPayouts - $pendingPayouts;
            
            // Pending balance = Current pending payouts
            $this->pending_balance = $pendingPayouts;

            // For debugging
            \Log::info('Balance calculation details for seller ' . $this->seller_id, [
                'raw_values' => [
                    'total_earnings' => $totalEarnings,
                    'completed_payouts' => $completedPayouts,
                    'approved_payouts' => $approvedPayouts,
                    'pending_payouts' => $pendingPayouts
                ],
                'calculations' => [
                    'balance_to_be_paid' => "{$totalEarnings} - {$completedPayouts} = {$this->balance_to_be_paid}",
                    'available_balance' => "{$this->balance_to_be_paid} - {$approvedPayouts} - {$pendingPayouts} = {$this->available_balance}"
                ],
                'final_balances' => [
                    'total_earned' => $this->total_earned,
                    'balance_to_be_paid' => $this->balance_to_be_paid,
                    'available_balance' => $this->available_balance,
                    'pending_balance' => $this->pending_balance
                ]
            ]);

            // Update database
            DB::statement("
                UPDATE seller_balances 
                SET 
                    total_earned = ?,
                    balance_to_be_paid = ?,
                    available_balance = ?,
                    pending_balance = ?,
                    updated_at = NOW()
                WHERE seller_id = ?
            ", [
                $totalEarnings,
                $this->balance_to_be_paid,
                $this->available_balance,
                $pendingPayouts,
                $this->seller_id
            ]);

            // Refresh the model
            $this->refresh();
        });

        return $this;
    }

    public function getCurrentBalanceAttribute()
    {
        return $this->total_earned - PayoutRequest::where('seller_id', $this->seller_id)
            ->where('status', 'approved')
            ->sum('amount');
    }

    public function getPendingPayoutsAttribute()
    {
        return PayoutRequest::where('seller_id', $this->seller_id)
            ->where('status', 'pending')
            ->sum('amount');
    }

    public function getAvailableBalanceAttribute()
    {
        return max(0, $this->current_balance - $this->pending_payouts);
    }
}

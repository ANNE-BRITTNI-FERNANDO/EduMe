<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BudgetTracking extends Model
{
    use HasFactory;

    protected $table = 'budget_tracking';

    protected $fillable = [
        'user_id',
        'budget_id',
        'total_amount',
        'spent_amount',
        'remaining_amount',
        'cycle_start_date',
        'cycle_end_date',
        'last_modified_at',
        'cycle_type',
        'category'
    ];

    protected $casts = [
        'cycle_start_date' => 'datetime',
        'cycle_end_date' => 'datetime',
        'last_modified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($budgetTracking) {
            // Calculate initial spent amount from existing orders in this cycle
            $spentAmount = Order::where('user_id', $budgetTracking->user_id)
                ->where('created_at', '>=', $budgetTracking->cycle_start_date)
                ->where('created_at', '<=', $budgetTracking->cycle_end_date)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            
            $budgetTracking->spent_amount = $spentAmount;
            $budgetTracking->remaining_amount = max(0, $budgetTracking->total_amount - $spentAmount);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function budget()
    {
        return $this->belongsTo(UserBudget::class, 'budget_id');
    }

    public function canModifyBudget()
    {
        if (!$this->last_modified_at) {
            return true;
        }

        $now = Carbon::now();
        $daysSinceLastModification = $this->last_modified_at->diffInDays($now);
        return $daysSinceLastModification >= 7;
    }

    public function getTimeUntilNextModification()
    {
        if (!$this->last_modified_at) {
            return "now";
        }
    
        $nextModificationDate = $this->last_modified_at->addDays(7);
        $now = Carbon::now();
        
        if ($now >= $nextModificationDate) {
            return "now";
        }
    
        $diff = $now->diff($nextModificationDate);
        
        $parts = [];
        if ($diff->d > 0) {
            $parts[] = $diff->d . " day" . ($diff->d > 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h . " hour" . ($diff->h > 1 ? 's' : '');
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i . " minute" . ($diff->i > 1 ? 's' : '');
        }
        
        return implode(', ', $parts);
    }

    public function hasEnoughBudget($amount)
    {
        return $this->remaining_amount >= $amount;
    }

    public function deductFromBudget($amount)
    {
        if (!$this->hasEnoughBudget($amount)) {
            return false;
        }
    
        $this->spent_amount += $amount;
        $this->remaining_amount -= $amount;
        $this->save();
    
        // Record in budget history
        BudgetHistory::create([
            'user_id' => $this->user_id,
            'total_budget' => $this->total_amount,
            'spent_amount' => $amount,
            'cycle_type' => $this->budget->cycle_type,
            'cycle_start_date' => $this->cycle_start_date,
            'cycle_end_date' => $this->cycle_end_date,
            'status' => $this->isActive() ? 'active' : 'expired'
        ]);
    
        return true;
    }

    public function isActive()
    {
        return Carbon::now()->between($this->cycle_start_date, $this->cycle_end_date);
    }

    public function getBudgetStatus()
    {
        $percentage = ($this->remaining_amount / $this->total_amount) * 100;
        
        if ($percentage <= 20) {
            return 'critical';
        } elseif ($percentage <= 50) {
            return 'warning';
        }
        
        return 'good';
    }

    public function getDaysUntilNextModification()
    {
        $lastModified = $this->last_modified_at ?? $this->created_at;
        $daysLeft = 4 - Carbon::now()->diffInDays($lastModified);
        return max(0, $daysLeft);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetHistory extends Model
{
    use HasFactory;

    protected $table = 'budget_history';

    protected $fillable = [
        'user_id',
        'budget_id',
        'total_amount',
        'spent_amount',
        'remaining_amount',
        'cycle_type',
        'cycle_start_date',
        'cycle_end_date',
        'status'
    ];

    protected $casts = [
        'cycle_start_date' => 'datetime',
        'cycle_end_date' => 'datetime',
        'total_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute($value)
    {
        // Always recalculate status based on current time
        $now = now();
        if ($now > $this->cycle_end_date) {
            return 'expired';
        }
        return $value;
    }
}

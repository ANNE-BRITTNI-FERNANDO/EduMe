<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'budget_amount',
        'cycle_type',
        'start_date',
    ];

    protected $dates = [
        'start_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function budgetTracking()
    {
        return $this->hasMany(BudgetTracking::class, 'budget_id');
    }

    public function budgetHistory()
    {
        return $this->hasMany(BudgetHistory::class, 'budget_id');
    }
}
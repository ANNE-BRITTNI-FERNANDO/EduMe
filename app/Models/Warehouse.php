<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'location',
        'address',
        'contact_number',
        'opening_time',
        'closing_time',
        'pickup_available'
    ];

    protected $casts = [
        'pickup_available' => 'boolean',
        'opening_time' => 'datetime',
        'closing_time' => 'datetime'
    ];
}

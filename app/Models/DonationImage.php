<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonationImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_id',
        'path',
    ];

    public function donation()
    {
        return $this->belongsTo(DonationItem::class, 'donation_id');
    }
}

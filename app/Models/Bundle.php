<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Bundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_name',
        'description',
        'bundle_image',
        'price',
        'user_id',
        'status',
        'rejection_reason',
        'rejection_details',
        'is_sold',
        'quantity'
    ];

    protected $casts = [
        'price' => 'float',
        'is_sold' => 'boolean',
        'quantity' => 'integer'
    ];

    public function categories()
    {
        return $this->hasMany(BundleCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', '!=', 'sold')
                    ->where('status', 'approved')
                    ->where('quantity', '>', 0);
    }
}

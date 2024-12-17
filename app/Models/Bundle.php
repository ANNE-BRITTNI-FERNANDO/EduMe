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
        'bundle_description',
        'bundle_image',
        'price',
        'user_id',
        'is_approved',
        'is_rejected',
        'rejection_reason',
        'is_sold',
        'quantity'
    ];

    protected $casts = [
        'price' => 'float',
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
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
                    ->where('is_approved', true)
                    ->where('is_rejected', false)
                    ->where('quantity', '>', 0);
    }
}

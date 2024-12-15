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
        'price',
        'bundle_image',
        'status',
        'user_id'
    ];

    protected $casts = [
        'price' => 'float',
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
}

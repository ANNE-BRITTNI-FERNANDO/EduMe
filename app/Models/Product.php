<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    // Fillable attributes to prevent mass-assignment vulnerabilities
    protected $fillable = [
        'product_name',
        'description',
        'price',
        'image_path',
        'category',
        'user_id',
        'is_approved',
        'is_rejected'
    ];

    // If you want to use attribute casting, for example to ensure that the price is always a float
    protected $casts = [
        'price' => 'float',
    ];

    // Accessor for getting the full URL of the image
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    // Define a relationship to the User model if required
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'item');
    }
}

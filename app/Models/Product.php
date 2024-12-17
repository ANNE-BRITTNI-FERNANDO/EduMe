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
        'is_rejected',
        'quantity',
        'is_sold'
    ];

    // If you want to use attribute casting, for example to ensure that the price is always a float
    protected $casts = [
        'price' => 'float',
        'is_sold' => 'boolean',
        'is_approved' => 'boolean',
        'is_rejected' => 'boolean',
        'quantity' => 'integer'
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

    // Scope for available products
    public function scopeAvailable($query)
    {
        return $query->where('is_approved', true)
                    ->where('is_rejected', false)
                    ->where('is_sold', false)
                    ->where('quantity', '>', 0);
    }

    protected static function boot()
    {
        parent::boot();

        // Log when a product is updated
        static::updating(function ($product) {
            \Log::info('Product being updated', [
                'product_id' => $product->id,
                'dirty' => $product->getDirty(),
                'original' => $product->getOriginal()
            ]);
        });

        static::updated(function ($product) {
            \Log::info('Product was updated', [
                'product_id' => $product->id,
                'is_sold' => $product->is_sold,
                'quantity' => $product->quantity
            ]);
        });
    }
}

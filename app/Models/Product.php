<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\OrderItem;

class Product extends Model
{
    use HasFactory;

    // Define product categories
    public const CATEGORIES = [
        'textbooks' => 'Textbooks',
        'stationery' => 'Stationery',
        'devices' => 'Electronic Devices',
        'other' => 'Other'
    ];

    // Fillable attributes to prevent mass-assignment vulnerabilities
    protected $fillable = [
        'product_name',
        'description',
        'price',
        'image_path',
        'category',
        'user_id',
        'quantity',
        'is_sold',
        'status',
        'rejection_reason',
        'rejection_note',
        'resubmitted',
        'approved_at'
    ];

    // If you want to use attribute casting, for example to ensure that the price is always a float
    protected $casts = [
        'price' => 'float',
        'is_sold' => 'boolean',
        'quantity' => 'integer',
        'resubmitted' => 'boolean',
        'approved_at' => 'datetime'
    ];

    protected $attributes = [
        'status' => 'pending'
    ];

    // Accessor for getting the full URL of the image
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        
        // Return the primary product image URL if exists
        $primaryImage = $this->images()->where('is_primary', true)->first();
        return $primaryImage ? $primaryImage->image_url : null;
    }

    // Define a relationship to the User model if required
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationship to ProductImage model
    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    // Alias for user() to maintain semantic clarity
    public function seller()
    {
        return $this->user();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get all of the order items for the product.
     */
    public function orderItems()
    {
        return $this->morphMany(OrderItem::class, 'item');
    }

    /**
     * Get the morph class name for this model.
     */
    public function getMorphClass()
    {
        return 'product';
    }

    // Scope for available products
    public function scopeAvailable($query)
    {
        return $query->where('status', 'approved')
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

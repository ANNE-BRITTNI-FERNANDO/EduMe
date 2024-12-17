<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Bundle; // Add the Bundle model to the namespace
use App\Models\Cart;
use App\Models\SellerBalance;
use App\Models\Product; // Add the Product model to the namespace
use App\Models\PayoutRequest;
use App\Models\Order;
use App\Models\OrderItem;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'street_address',
        'city',
        'province',
        'location',
        'is_seller',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'location' => 'Not specified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_seller' => 'boolean',
    ];

    /**
     * Get the seller balance associated with the user.
     */
    public function sellerBalance()
    {
        return $this->hasOne(SellerBalance::class, 'seller_id');
    }

    /**
     * Get the bundles that belong to the user.
     */
    public function bundles()
    {
        return $this->hasMany(Bundle::class);
    }

    /**
     * Get the cart associated with the user.
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get the products that belong to the user.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the payout requests that belong to the user.
     */
    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class, 'seller_id');
    }

    // Relationship for orders where user is a seller
    public function sellerOrders()
    {
        return $this->hasManyThrough(
            Order::class,
            OrderItem::class,
            'seller_id', // Foreign key on order_items table
            'id', // Local key on orders table
            'id', // Local key on users table
            'order_id' // Foreign key on order_items table
        );
    }
}

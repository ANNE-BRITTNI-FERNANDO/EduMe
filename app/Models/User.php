<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use HasFactory, Notifiable, SoftDeletes;

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
        'phone',
        'address',
        'province',
        'location',
        'is_seller',
        'deleted_at',
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

    // Relationship for orders where user is a buyer
    public function buyerOrders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Get the orders that belong to the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Handle account deletion logic
     */
    public function deleteAccount()
    {
        // Start a database transaction
        \DB::beginTransaction();

        try {
            // 1. Handle products - soft delete them but keep them visible in past orders
            $this->products()->update(['status' => 'unavailable']);
            $this->products()->delete();

            // 2. Handle bundles - soft delete them but keep them visible in past orders
            $this->bundles()->update(['status' => 'unavailable']);
            $this->bundles()->delete();

            // 3. Handle active orders
            $activeOrders = $this->buyerOrders()
                ->whereIn('status', ['pending', 'processing'])
                ->exists();
            
            if ($activeOrders) {
                throw new \Exception('Cannot delete account while there are active orders. Please wait for all orders to complete.');
            }

            // 4. Handle seller balance
            $sellerBalance = $this->sellerBalance;
            if ($sellerBalance && $sellerBalance->available_balance > 0) {
                throw new \Exception('Cannot delete account while there is an available balance. Please withdraw your earnings first.');
            }

            // 5. Mark the user as deleted but keep their information for order history
            $this->update([
                'email' => $this->email . '.deleted.' . time(),
                'name' => 'Deleted User',
                'phone' => null,
                'address' => null,
                'province' => null,
                'location' => null,
            ]);

            // 6. Soft delete the user
            $this->delete();

            \DB::commit();
            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}

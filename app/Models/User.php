<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Bundle; // Add the Bundle model to the namespace
use App\Models\Cart;
use App\Models\SellerBalance;

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
        'location',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the seller balance associated with the user.
     */
    public function sellerBalance()
    {
        return $this->hasOne(SellerBalance::class);
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
}

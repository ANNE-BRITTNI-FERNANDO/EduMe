<?php

namespace App\Services;

use App\Models\User;
use App\Models\SellerBalance;
use Illuminate\Support\Facades\DB;

class EarningsService
{
    public function updateSellerEarnings(User $seller, float $amount)
    {
        DB::transaction(function () use ($seller, $amount) {
            $sellerBalance = SellerBalance::firstOrCreate(
                ['user_id' => $seller->id],
                ['balance' => 0]
            );
            
            $sellerBalance->balance += $amount;
            $sellerBalance->save();
        });
    }
}

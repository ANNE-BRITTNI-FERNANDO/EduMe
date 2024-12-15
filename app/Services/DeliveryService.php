<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\SriLankaLocation;
use Illuminate\Support\Facades\Cache;

class DeliveryService
{
    const SAME_CITY_FEE = 200;
    const SAME_PROVINCE_FEE = 300;
    const DIFFERENT_PROVINCE_FEE = 600;

    public function calculateDeliveryFee($items, $buyerCity, $buyerProvince = null)
    {
        if (empty($buyerCity) || $buyerCity === 'default') {
            return 0;
        }

        // Get buyer's province if not provided
        if (!$buyerProvince) {
            $buyerLocation = $this->getLocationInfo($buyerCity);
            $buyerProvince = $buyerLocation ? $buyerLocation->province : null;
        }

        $sellerGroups = [];
        
        // Group items by seller
        foreach ($items as $item) {
            $seller = null;
            if ($item->item_type === 'product' && $item->product) {
                $seller = $item->product->user;
            } elseif ($item->item_type === 'bundle' && $item->bundle) {
                $seller = $item->bundle->user;
            }
            
            if ($seller) {
                $sellerId = $seller->id;
                if (!isset($sellerGroups[$sellerId])) {
                    $sellerGroups[$sellerId] = [
                        'city' => $seller->city,
                        'province' => $this->getLocationInfo($seller->city)?->province,
                        'items' => []
                    ];
                }
                $sellerGroups[$sellerId]['items'][] = $item;
            }
        }
        
        $totalDeliveryFee = 0;
        
        // Calculate delivery fee for each seller
        foreach ($sellerGroups as $sellerId => $group) {
            $sellerCity = $group['city'];
            $sellerProvince = $group['province'];
            
            // Calculate base delivery fee based on location
            if ($sellerCity === $buyerCity) {
                $fee = self::SAME_CITY_FEE;
            } elseif ($sellerProvince && $buyerProvince && $sellerProvince === $buyerProvince) {
                $fee = self::SAME_PROVINCE_FEE;
            } else {
                $fee = self::DIFFERENT_PROVINCE_FEE;
            }
            
            $totalDeliveryFee += $fee;
        }
        
        return $totalDeliveryFee;
    }

    public function getLocationInfo($city)
    {
        return Cache::remember('location_' . $city, 3600, function () use ($city) {
            return SriLankaLocation::where('city', $city)->first();
        });
    }
}

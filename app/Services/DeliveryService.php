<?php

namespace App\Services;

use App\Models\CartItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    // Base delivery fees by distance
    const BASE_FEES = [
        'same_city' => 200,      // Same city
        'same_district' => 300,  // Different city, same district
        'same_province' => 400,  // Different district, same province
        'different_province' => 500  // Different province
    ];

    // Additional item fees (after first item)
    const ADDITIONAL_ITEM_FEE = 50;  // Per additional item from same seller

    // Maximum items for group discount
    const MAX_ITEMS_FOR_DISCOUNT = 5;

    public function calculateDeliveryFee($items, $buyerLocation, $buyerProvince)
    {
        if (empty($buyerLocation) || empty($buyerProvince)) {
            return 0;
        }

        // Group items by seller
        $sellerGroups = [];
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
                        'seller' => $seller,
                        'items' => []
                    ];
                }
                $sellerGroups[$sellerId]['items'][] = $item;
            }
        }

        $totalDeliveryFee = 0;

        // Calculate fee for each seller's items
        foreach ($sellerGroups as $group) {
            $seller = $group['seller'];
            $itemCount = count($group['items']);
            
            // Get base fee based on location
            $baseFee = $this->getBaseFee($seller->location, $seller->province, $buyerLocation, $buyerProvince);
            
            // Calculate additional item fees (if more than 1 item)
            $additionalFee = 0;
            if ($itemCount > 1) {
                // Apply group discount for bulk orders
                $additionalItems = min($itemCount - 1, self::MAX_ITEMS_FOR_DISCOUNT - 1);
                $additionalFee = $additionalItems * self::ADDITIONAL_ITEM_FEE;
            }

            // Log the calculation for debugging
            Log::info('Delivery Fee Calculation', [
                'seller_location' => $seller->location,
                'seller_province' => $seller->province,
                'buyer_location' => $buyerLocation,
                'buyer_province' => $buyerProvince,
                'base_fee' => $baseFee,
                'item_count' => $itemCount,
                'additional_fee' => $additionalFee,
                'total_fee' => $baseFee + $additionalFee
            ]);

            $totalDeliveryFee += $baseFee + $additionalFee;
        }

        return $totalDeliveryFee;
    }

    private function getBaseFee($sellerLocation, $sellerProvince, $buyerLocation, $buyerProvince)
    {
        // Clean and standardize location strings
        $sellerLocation = strtolower(trim($sellerLocation ?? ''));
        $sellerProvince = strtolower(trim($sellerProvince ?? ''));
        $buyerLocation = strtolower(trim($buyerLocation ?? ''));
        $buyerProvince = strtolower(trim($buyerProvince ?? ''));

        // Log the cleaned values for debugging
        Log::info('Location Comparison', [
            'seller_location' => $sellerLocation,
            'seller_province' => $sellerProvince,
            'buyer_location' => $buyerLocation,
            'buyer_province' => $buyerProvince
        ]);

        // Same city/district
        if ($sellerLocation === $buyerLocation) {
            Log::info('Same location detected', ['fee' => self::BASE_FEES['same_city']]);
            return self::BASE_FEES['same_city'];
        }

        // Same province
        if ($sellerProvince === $buyerProvince) {
            Log::info('Same province detected', ['fee' => self::BASE_FEES['same_province']]);
            return self::BASE_FEES['same_province'];
        }

        // Different province
        Log::info('Different province detected', ['fee' => self::BASE_FEES['different_province']]);
        return self::BASE_FEES['different_province'];
    }
}

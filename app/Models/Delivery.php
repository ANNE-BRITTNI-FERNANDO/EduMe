<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Delivery extends Model
{
    protected $fillable = [
        'sender_name',
        'sender_phone',
        'sender_address',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'package_description',
        'package_weight',
        'delivery_type',
        'status',
        'delivery_fee',
        'tracking_number',
        'distance_km',
        'estimated_time'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($delivery) {
            $delivery->tracking_number = 'SL-' . strtoupper(uniqid());
            
            // Calculate distance and fee
            $distance = self::calculateDistance(
                $delivery->sender_address,
                $delivery->receiver_address
            );
            
            $delivery->distance_km = $distance;
            $delivery->delivery_fee = self::calculateDeliveryFee(
                $distance,
                $delivery->package_weight,
                $delivery->delivery_type
            );
            
            // Estimate delivery time
            $delivery->estimated_time = self::estimateDeliveryTime(
                $distance,
                $delivery->delivery_type
            );
        });
    }

    public static function calculateDistance($fromAddress, $toAddress)
    {
        // Using coordinates for Sri Lankan cities/areas
        // In a production environment, you would use Google Maps Distance Matrix API
        $distances = [
            'colombo' => ['lat' => 6.9271, 'lng' => 79.8612],
            'kandy' => ['lat' => 7.2906, 'lng' => 80.6337],
            'galle' => ['lat' => 6.0535, 'lng' => 80.2210],
            'jaffna' => ['lat' => 9.6615, 'lng' => 80.0255],
            'batticaloa' => ['lat' => 7.7170, 'lng' => 81.7000],
        ];

        // Simple distance calculation for demo
        // In production, use Google Maps API
        $from = strtolower(explode(',', $fromAddress)[0]);
        $to = strtolower(explode(',', $toAddress)[0]);

        if (isset($distances[$from]) && isset($distances[$to])) {
            $lat1 = $distances[$from]['lat'];
            $lon1 = $distances[$from]['lng'];
            $lat2 = $distances[$to]['lat'];
            $lon2 = $distances[$to]['lng'];

            return self::haversineDistance($lat1, $lon1, $lat2, $lon2);
        }

        return 50; // Default 50km if cities not found
    }

    private static function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat/2)**2 + cos($lat1) * cos($lat2) * sin($dlon/2)**2;
        $c = 2 * asin(sqrt($a));
        $r = 6371; // Radius of Earth in kilometers

        return round($c * $r, 2);
    }

    public static function calculateDeliveryFee($distance, $weight, $deliveryType)
    {
        // Base fee in LKR
        $baseFee = 250;

        // Distance fee: 50 LKR per km
        $distanceFee = $distance * 50;

        // Weight fee: 100 LKR per kg
        $weightFee = ($weight ?? 1) * 100;

        // Express delivery fee
        $expressFee = $deliveryType === 'express' ? 1000 : 0;

        // Calculate total
        $total = $baseFee + $distanceFee + $weightFee + $expressFee;

        // Add fuel surcharge (10%)
        $fuelSurcharge = $total * 0.10;

        return ceil($total + $fuelSurcharge);
    }

    private static function estimateDeliveryTime($distance, $deliveryType)
    {
        // Base processing time: 1 hour
        $processingHours = 1;

        // Calculate transit time based on distance
        // Assume average speed of 40 km/h for standard, 60 km/h for express
        $speed = $deliveryType === 'express' ? 60 : 40;
        $transitHours = $distance / $speed;

        // Add buffer time
        $bufferHours = $deliveryType === 'express' ? 2 : 4;

        $totalHours = $processingHours + $transitHours + $bufferHours;

        // Round up to nearest hour
        return ceil($totalHours);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}

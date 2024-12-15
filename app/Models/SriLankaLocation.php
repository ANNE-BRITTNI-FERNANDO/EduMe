<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SriLankaLocation extends Model
{
    protected $fillable = [
        'city',
        'province'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public static function getProvinces()
    {
        return [
            'Western',
            'Central',
            'Southern',
            'Northern',
            'Eastern',
            'North Western',
            'North Central',
            'Uva',
            'Sabaragamuwa'
        ];
    }

    public static function getCitiesByProvince($province)
    {
        return self::where('province', $province)
            ->orderBy('city')
            ->pluck('city');
    }

    public static function getAllCities()
    {
        return self::orderBy('city')
            ->pluck('city');
    }

    public static function getProvinceForCity($city)
    {
        $location = self::where('city', $city)->first();
        return $location ? $location->province : null;
    }
}

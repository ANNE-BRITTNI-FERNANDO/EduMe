<?php

namespace App\Services;

class LocationService
{
    private static $cityProvinceMap = [
        // Western Province
        'Colombo' => 'Western',
        'Gampaha' => 'Western',
        'Kalutara' => 'Western',

        // Central Province
        'Kandy' => 'Central',
        'Matale' => 'Central',
        'Nuwara Eliya' => 'Central',

        // Southern Province
        'Galle' => 'Southern',
        'Matara' => 'Southern',
        'Hambantota' => 'Southern',

        // Northern Province
        'Jaffna' => 'Northern',
        'Kilinochchi' => 'Northern',
        'Mannar' => 'Northern',
        'Mullaitivu' => 'Northern',
        'Vavuniya' => 'Northern',

        // Eastern Province
        'Trincomalee' => 'Eastern',
        'Batticaloa' => 'Eastern',
        'Ampara' => 'Eastern',

        // North Western Province
        'Kurunegala' => 'North Western',
        'Puttalam' => 'North Western',

        // North Central Province
        'Anuradhapura' => 'North Central',
        'Polonnaruwa' => 'North Central',

        // Uva Province
        'Badulla' => 'Uva',
        'Monaragala' => 'Uva',

        // Sabaragamuwa Province
        'Ratnapura' => 'Sabaragamuwa',
        'Kegalle' => 'Sabaragamuwa'
    ];

    public static function getProvinceForCity($city)
    {
        $city = trim($city);
        return self::$cityProvinceMap[$city] ?? null;
    }

    public static function validateCity($city)
    {
        $city = trim($city);
        return isset(self::$cityProvinceMap[$city]);
    }

    public static function getCityProvinceMap()
    {
        // Sort cities alphabetically
        $cities = self::$cityProvinceMap;
        ksort($cities);
        return $cities;
    }

    public static function getAllProvinces()
    {
        return array_unique(array_values(self::$cityProvinceMap));
    }

    public static function getCitiesInProvince($province)
    {
        return array_keys(array_filter(self::$cityProvinceMap, function($p) use ($province) {
            return $p === $province;
        }));
    }

    public static function getProvinceDetails()
    {
        return [
            'Western' => ['capital' => 'Colombo', 'districts' => ['Colombo', 'Gampaha', 'Kalutara']],
            'Central' => ['capital' => 'Kandy', 'districts' => ['Kandy', 'Matale', 'Nuwara Eliya']],
            'Southern' => ['capital' => 'Galle', 'districts' => ['Galle', 'Matara', 'Hambantota']],
            'Northern' => ['capital' => 'Jaffna', 'districts' => ['Jaffna', 'Kilinochchi', 'Mannar', 'Mullaitivu', 'Vavuniya']],
            'Eastern' => ['capital' => 'Trincomalee', 'districts' => ['Trincomalee', 'Batticaloa', 'Ampara']],
            'North Western' => ['capital' => 'Kurunegala', 'districts' => ['Kurunegala', 'Puttalam']],
            'North Central' => ['capital' => 'Anuradhapura', 'districts' => ['Anuradhapura', 'Polonnaruwa']],
            'Uva' => ['capital' => 'Badulla', 'districts' => ['Badulla', 'Monaragala']],
            'Sabaragamuwa' => ['capital' => 'Ratnapura', 'districts' => ['Ratnapura', 'Kegalle']]
        ];
    }
}

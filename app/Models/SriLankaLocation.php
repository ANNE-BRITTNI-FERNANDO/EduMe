<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SriLankaLocation extends Model
{
    protected $fillable = [
        'city',
        'district',
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

    public static function getDistrictsForProvince($province)
    {
        $districts = [
            'Western' => ['Colombo', 'Gampaha', 'Kalutara'],
            'Central' => ['Kandy', 'Matale', 'Nuwara Eliya'],
            'Southern' => ['Galle', 'Matara', 'Hambantota'],
            'Northern' => ['Jaffna', 'Kilinochchi', 'Mannar', 'Mullaitivu', 'Vavuniya'],
            'Eastern' => ['Trincomalee', 'Batticaloa', 'Ampara'],
            'North Western' => ['Kurunegala', 'Puttalam'],
            'North Central' => ['Anuradhapura', 'Polonnaruwa'],
            'Uva' => ['Badulla', 'Monaragala'],
            'Sabaragamuwa' => ['Ratnapura', 'Kegalle']
        ];

        return isset($districts[$province]) ? $districts[$province] : [];
    }

    public static function getCitiesByDistrict($district)
    {
        $cities = [
            'Colombo' => ['Colombo', 'Dehiwala', 'Moratuwa', 'Kotte', 'Kolonnawa', 'Kaduwela', 'Maharagama'],
            'Gampaha' => ['Gampaha', 'Negombo', 'Wattala', 'Ja-Ela', 'Kandana', 'Kelaniya'],
            'Kalutara' => ['Kalutara', 'Panadura', 'Horana', 'Bandaragama'],
            'Kandy' => ['Kandy', 'Peradeniya', 'Gampola', 'Katugastota'],
            'Matale' => ['Matale', 'Dambulla', 'Galewela'],
            'Nuwara Eliya' => ['Nuwara Eliya', 'Hatton', 'Talawakele'],
            'Galle' => ['Galle', 'Ambalangoda', 'Hikkaduwa'],
            'Matara' => ['Matara', 'Weligama', 'Dickwella'],
            'Hambantota' => ['Hambantota', 'Tangalle', 'Beliatta'],
            'Jaffna' => ['Jaffna', 'Chavakachcheri', 'Point Pedro'],
            'Kilinochchi' => ['Kilinochchi'],
            'Mannar' => ['Mannar'],
            'Mullaitivu' => ['Mullaitivu'],
            'Vavuniya' => ['Vavuniya'],
            'Trincomalee' => ['Trincomalee', 'Kantale'],
            'Batticaloa' => ['Batticaloa', 'Kattankudy'],
            'Ampara' => ['Ampara', 'Kalmunai'],
            'Kurunegala' => ['Kurunegala', 'Kuliyapitiya'],
            'Puttalam' => ['Puttalam', 'Chilaw'],
            'Anuradhapura' => ['Anuradhapura'],
            'Polonnaruwa' => ['Polonnaruwa'],
            'Badulla' => ['Badulla', 'Bandarawela', 'Haputale'],
            'Monaragala' => ['Monaragala', 'Wellawaya'],
            'Ratnapura' => ['Ratnapura', 'Embilipitiya'],
            'Kegalle' => ['Kegalle', 'Mawanella']
        ];

        return isset($cities[$district]) ? $cities[$district] : [];
    }

    public static function getDistrictForCity($city)
    {
        foreach (self::getProvinces() as $province) {
            foreach (self::getDistrictsForProvince($province) as $district) {
                if (in_array($city, self::getCitiesByDistrict($district))) {
                    return $district;
                }
            }
        }
        return null;
    }

    public static function getProvinceForCity($city)
    {
        foreach (self::getProvinces() as $province) {
            foreach (self::getDistrictsForProvince($province) as $district) {
                if (in_array($city, self::getCitiesByDistrict($district))) {
                    return $province;
                }
            }
        }
        return null;
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
}

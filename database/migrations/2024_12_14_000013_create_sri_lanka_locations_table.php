<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sri_lanka_locations', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('province');
            $table->timestamps();
            
            // Add unique constraint on city
            $table->unique('city');
        });

        // Insert initial data for Sri Lankan provinces and cities
        $locations = [
            // Western Province
            ['city' => 'Colombo', 'province' => 'Western'],
            ['city' => 'Gampaha', 'province' => 'Western'],
            ['city' => 'Kalutara', 'province' => 'Western'],
            
            // Central Province
            ['city' => 'Kandy', 'province' => 'Central'],
            ['city' => 'Matale', 'province' => 'Central'],
            ['city' => 'Nuwara Eliya', 'province' => 'Central'],
            
            // Southern Province
            ['city' => 'Galle', 'province' => 'Southern'],
            ['city' => 'Matara', 'province' => 'Southern'],
            ['city' => 'Hambantota', 'province' => 'Southern'],
            
            // Northern Province
            ['city' => 'Jaffna', 'province' => 'Northern'],
            ['city' => 'Kilinochchi', 'province' => 'Northern'],
            ['city' => 'Mannar', 'province' => 'Northern'],
            ['city' => 'Vavuniya', 'province' => 'Northern'],
            ['city' => 'Mullaitivu', 'province' => 'Northern'],
            
            // Eastern Province
            ['city' => 'Batticaloa', 'province' => 'Eastern'],
            ['city' => 'Ampara', 'province' => 'Eastern'],
            ['city' => 'Trincomalee', 'province' => 'Eastern'],
            
            // North Western Province
            ['city' => 'Kurunegala', 'province' => 'North Western'],
            ['city' => 'Puttalam', 'province' => 'North Western'],
            
            // North Central Province
            ['city' => 'Anuradhapura', 'province' => 'North Central'],
            ['city' => 'Polonnaruwa', 'province' => 'North Central'],
            
            // Uva Province
            ['city' => 'Badulla', 'province' => 'Uva'],
            ['city' => 'Monaragala', 'province' => 'Uva'],
            
            // Sabaragamuwa Province
            ['city' => 'Ratnapura', 'province' => 'Sabaragamuwa'],
            ['city' => 'Kegalle', 'province' => 'Sabaragamuwa'],
        ];

        DB::table('sri_lanka_locations')->insert($locations);
    }

    public function down()
    {
        Schema::dropIfExists('sri_lanka_locations');
    }
};

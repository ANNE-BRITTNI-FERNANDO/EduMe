<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Additional contact information
            $table->string('email')->nullable();
            $table->string('alternate_contact')->nullable();
            $table->string('manager_name')->nullable();
            
            // Capacity and status
            $table->integer('storage_capacity')->nullable(); // in cubic meters
            $table->integer('current_occupancy')->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['operational', 'maintenance', 'closed'])->default('operational');
            
            // Security and access
            $table->boolean('has_security')->default(true);
            $table->boolean('has_cctv')->default(true);
            $table->boolean('has_loading_dock')->default(true);
            $table->boolean('has_parking')->default(true);
            
            // Facilities
            $table->boolean('has_climate_control')->default(false);
            $table->boolean('has_refrigeration')->default(false);
            $table->boolean('has_hazmat_storage')->default(false);
            
            // Operating schedule
            $table->json('operating_days')->nullable(); // Store days of the week
            $table->boolean('open_24_7')->default(false);
            $table->boolean('open_on_holidays')->default(false);
            
            // Location details
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            
            // Additional features
            $table->text('special_instructions')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->json('available_services')->nullable(); // Store array of services
            $table->json('restricted_items')->nullable(); // Store array of restricted items
            
            // Costs and rates
            $table->decimal('storage_rate_daily', 10, 2)->nullable();
            $table->decimal('storage_rate_monthly', 10, 2)->nullable();
            $table->decimal('handling_fee', 10, 2)->nullable();
            $table->string('currency')->default('INR');
            
            // Insurance and compliance
            $table->boolean('is_insured')->default(true);
            $table->date('insurance_expiry')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            
            // Timestamps for specific events
            $table->timestamp('last_inspection_at')->nullable();
            $table->timestamp('next_maintenance_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'alternate_contact',
                'manager_name',
                'storage_capacity',
                'current_occupancy',
                'is_active',
                'status',
                'has_security',
                'has_cctv',
                'has_loading_dock',
                'has_parking',
                'has_climate_control',
                'has_refrigeration',
                'has_hazmat_storage',
                'operating_days',
                'open_24_7',
                'open_on_holidays',
                'latitude',
                'longitude',
                'city',
                'state',
                'country',
                'postal_code',
                'special_instructions',
                'delivery_instructions',
                'available_services',
                'restricted_items',
                'storage_rate_daily',
                'storage_rate_monthly',
                'handling_fee',
                'currency',
                'is_insured',
                'insurance_expiry',
                'license_number',
                'license_expiry',
                'last_inspection_at',
                'next_maintenance_at'
            ]);
        });
    }
};

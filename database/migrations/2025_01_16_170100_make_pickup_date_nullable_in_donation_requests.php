<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePickupDateNullableInDonationRequests extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->timestamp('pickup_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->timestamp('pickup_date')->nullable(false)->change();
        });
    }
}

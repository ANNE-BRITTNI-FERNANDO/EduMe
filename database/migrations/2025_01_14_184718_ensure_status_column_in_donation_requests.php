<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureStatusColumnInDonationRequests extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('donation_requests', 'status')) {
                $table->string('status')->default('pending');
            } else {
                // Update any null statuses to 'pending'
                DB::table('donation_requests')
                    ->whereNull('status')
                    ->update(['status' => 'pending']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            if (Schema::hasColumn('donation_requests', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}

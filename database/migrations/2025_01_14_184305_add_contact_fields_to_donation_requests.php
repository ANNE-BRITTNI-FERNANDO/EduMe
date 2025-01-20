<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactFieldsToDonationRequests extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('donation_requests', 'preferred_contact_method')) {
                $table->string('preferred_contact_method')->default('email');
            }
            if (!Schema::hasColumn('donation_requests', 'preferred_contact_times')) {
                $table->string('preferred_contact_times')->nullable();
            }
            if (!Schema::hasColumn('donation_requests', 'pickup_address')) {
                $table->text('pickup_address')->nullable();
            }
            if (!Schema::hasColumn('donation_requests', 'show_contact_details')) {
                $table->boolean('show_contact_details')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_contact_method',
                'preferred_contact_times',
                'pickup_address',
                'show_contact_details'
            ]);
        });
    }
}

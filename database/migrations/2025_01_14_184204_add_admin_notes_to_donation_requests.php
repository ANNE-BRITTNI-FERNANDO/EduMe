<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminNotesToDonationRequests extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('donation_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            if (Schema::hasColumn('donation_requests', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });
    }
}

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
        Schema::table('donation_items', function (Blueprint $table) {
            $table->string('education_level')->nullable()->after('category');
        });

        // Update existing records to have a default education level
        DB::table('donation_items')
            ->whereNull('education_level')
            ->update(['education_level' => 'secondary']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_items', function (Blueprint $table) {
            $table->dropColumn('education_level');
        });
    }
};

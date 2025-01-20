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
        Schema::table('donation_requests', function (Blueprint $table) {
            // Make fields nullable that aren't always required
            $table->text('purpose_details')->nullable()->change();
            $table->string('contact_number')->nullable()->change();
            $table->enum('preferred_contact_time', ['morning', 'afternoon', 'evening'])->nullable()->change();
            $table->text('notes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            $table->text('purpose_details')->nullable(false)->change();
            $table->string('contact_number')->nullable(false)->change();
            $table->enum('preferred_contact_time', ['morning', 'afternoon', 'evening'])->nullable(false)->change();
            $table->text('notes')->nullable(false)->change();
        });
    }
};

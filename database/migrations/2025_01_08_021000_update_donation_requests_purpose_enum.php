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
        // First drop the enum constraint
        DB::statement('ALTER TABLE donation_requests MODIFY COLUMN purpose VARCHAR(50)');
        
        // Then add the new enum with updated values
        DB::statement("ALTER TABLE donation_requests MODIFY COLUMN purpose ENUM('personal', 'educational', 'community', 'other', 'tuition', 'books', 'equipment', 'transport') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First drop the enum constraint
        DB::statement('ALTER TABLE donation_requests MODIFY COLUMN purpose VARCHAR(50)');
        
        // Then restore the original enum values
        DB::statement("ALTER TABLE donation_requests MODIFY COLUMN purpose ENUM('personal', 'educational', 'community', 'other') NOT NULL");
    }
};

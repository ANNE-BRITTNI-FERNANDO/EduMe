<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            // Add any missing columns
            if (!Schema::hasColumn('payout_requests', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('payout_requests', 'processed_at')) {
                $table->timestamp('processed_at')->nullable();
            }

            if (!Schema::hasColumn('payout_requests', 'notes')) {
                $table->text('notes')->nullable();
            }

            // Update status enum if needed
            DB::statement("ALTER TABLE payout_requests MODIFY COLUMN status ENUM('pending', 'approved', 'completed', 'rejected') NOT NULL DEFAULT 'pending'");
        });
    }

    public function down()
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropColumn(['processed_by', 'processed_at', 'notes']);
        });
    }
};

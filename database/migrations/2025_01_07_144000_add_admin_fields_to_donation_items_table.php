<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminFieldsToDonationItemsTable extends Migration
{
    public function up()
    {
        Schema::table('donation_items', function (Blueprint $table) {
            // Add admin action tracking fields
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            
            // Add timestamps for various statuses
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            // Add rejection reason
            $table->text('rejection_reason')->nullable();
            
            // Modify status field to include new statuses
            $table->string('status')->default('pending')->change();
        });
    }

    public function down()
    {
        Schema::table('donation_items', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['received_by']);
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['rejected_by']);
            
            // Drop columns
            $table->dropColumn([
                'received_by',
                'reviewed_by',
                'rejected_by',
                'review_started_at',
                'rejected_at',
                'rejection_reason'
            ]);
        });
    }
}

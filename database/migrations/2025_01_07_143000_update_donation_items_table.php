<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDonationItemsTable extends Migration
{
    public function up()
    {
        Schema::table('donation_items', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('donation_items', 'verification_status')) {
                $table->dropColumn('verification_status');
            }
            if (Schema::hasColumn('donation_items', 'education_level')) {
                $table->dropColumn('education_level');
            }

            // Add new columns if they don't exist
            if (!Schema::hasColumn('donation_items', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false);
            }
            if (!Schema::hasColumn('donation_items', 'contact_number')) {
                $table->string('contact_number')->nullable();
            }
            if (!Schema::hasColumn('donation_items', 'pickup_address')) {
                $table->text('pickup_address')->nullable();
            }
            if (!Schema::hasColumn('donation_items', 'preferred_pickup_date')) {
                $table->timestamp('preferred_pickup_date')->nullable();
            }
            if (!Schema::hasColumn('donation_items', 'received_at')) {
                $table->timestamp('received_at')->nullable();
            }
            if (!Schema::hasColumn('donation_items', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('donation_items', 'images')) {
                $table->json('images')->nullable();
            }
            
            // Rename donor_id to user_id if it exists
            if (Schema::hasColumn('donation_items', 'donor_id') && !Schema::hasColumn('donation_items', 'user_id')) {
                $table->renameColumn('donor_id', 'user_id');
            }
        });
    }

    public function down()
    {
        Schema::table('donation_items', function (Blueprint $table) {
            // Drop new columns if they exist
            $columns = [
                'is_anonymous',
                'contact_number',
                'pickup_address',
                'preferred_pickup_date',
                'received_at',
                'notes',
                'images'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('donation_items', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Add back old columns if they don't exist
            if (!Schema::hasColumn('donation_items', 'verification_status')) {
                $table->string('verification_status')->default('pending');
            }
            if (!Schema::hasColumn('donation_items', 'education_level')) {
                $table->string('education_level')->nullable();
            }
            
            // Rename user_id back to donor_id if needed
            if (Schema::hasColumn('donation_items', 'user_id') && !Schema::hasColumn('donation_items', 'donor_id')) {
                $table->renameColumn('user_id', 'donor_id');
            }
        });
    }
}

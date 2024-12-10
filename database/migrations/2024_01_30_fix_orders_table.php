<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop conversation_id if it exists
            if (Schema::hasColumn('orders', 'conversation_id')) {
                $table->dropForeign(['conversation_id']);
                $table->dropColumn('conversation_id');
            }

            // Add bank_transfer_details if it doesn't exist
            if (!Schema::hasColumn('orders', 'bank_transfer_details')) {
                $table->text('bank_transfer_details')->nullable();
            }

            // Make sure payment_status exists
            if (!Schema::hasColumn('orders', 'payment_status') && Schema::hasColumn('orders', 'status')) {
                $table->renameColumn('status', 'payment_status');
            } elseif (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Reverse the changes
            if (Schema::hasColumn('orders', 'bank_transfer_details')) {
                $table->dropColumn('bank_transfer_details');
            }

            if (!Schema::hasColumn('orders', 'conversation_id')) {
                $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('set null');
            }

            if (Schema::hasColumn('orders', 'payment_status') && !Schema::hasColumn('orders', 'status')) {
                $table->renameColumn('payment_status', 'status');
            }
        });
    }
};

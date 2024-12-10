<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('seller_id')->references('id')->on('users')->onDelete('cascade');
                $table->string('payment_method');
                $table->decimal('total_amount', 10, 2);
                $table->timestamps();
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('orders', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable();
            }
            if (!Schema::hasColumn('orders', 'item_type')) {
                $table->string('item_type')->nullable(); // 'product' or 'bundle'
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Reverse the changes
            if (Schema::hasColumn('orders', 'item_id')) {
                $table->dropColumn('item_id');
            }
            if (Schema::hasColumn('orders', 'item_type')) {
                $table->dropColumn('item_type');
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};

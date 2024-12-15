<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop the existing order_items table
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('order_items');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->morphs('item'); // This creates item_id and item_type for products/bundles
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('delivery_fee_share', 10, 2)->default(0);
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['order_id', 'seller_id']);
            $table->index(['item_id', 'item_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};

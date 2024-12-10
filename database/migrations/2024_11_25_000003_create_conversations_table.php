<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('buyer_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreignId('seller_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('bundle_id')->nullable()->constrained()->onDelete('set null');
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();

                // Ensure a buyer can only have one conversation per product/bundle with a seller
                $table->unique(['product_id', 'seller_id', 'buyer_id'], 'unique_product_conversation');
                $table->unique(['bundle_id', 'seller_id', 'buyer_id'], 'unique_bundle_conversation');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};

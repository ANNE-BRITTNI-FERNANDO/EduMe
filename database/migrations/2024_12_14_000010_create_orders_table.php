<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('payment_id')->nullable();
            $table->text('bank_transfer_details')->nullable();
            
            // Delivery Information
            $table->string('shipping_address');
            $table->string('phone');
            $table->string('delivery_status')->default('pending');
            $table->string('buyer_province')->nullable();
            $table->string('buyer_city')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};

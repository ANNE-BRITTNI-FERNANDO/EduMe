<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('sender_name');
            $table->string('sender_phone'); // Sri Lankan format
            $table->string('sender_address');
            $table->string('receiver_name');
            $table->string('receiver_phone'); // Sri Lankan format
            $table->string('receiver_address');
            $table->string('package_description');
            $table->decimal('package_weight', 8, 2)->nullable();
            $table->enum('delivery_type', ['standard', 'express']);
            $table->enum('status', ['pending', 'picked_up', 'in_transit', 'delivered'])->default('pending');
            $table->decimal('delivery_fee', 10, 2);
            $table->string('tracking_number')->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};

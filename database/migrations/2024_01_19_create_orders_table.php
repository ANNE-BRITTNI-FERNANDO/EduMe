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
                $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('set null');
                $table->string('payment_method'); // 'bank_transfer' or 'stripe'
                $table->string('status')->default('pending'); // pending, confirmed, completed, cancelled
                $table->decimal('total_amount', 10, 2);
                $table->text('bank_transfer_details')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};

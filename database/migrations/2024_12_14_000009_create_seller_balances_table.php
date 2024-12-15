<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seller_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('available_balance', 10, 2)->default(0);
            $table->decimal('total_earned', 10, 2)->default(0);
            $table->decimal('balance_to_be_paid', 10, 2)->default(0);
            $table->decimal('pending_balance', 10, 2)->default(0);
            $table->decimal('total_delivery_fees_earned', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Add unique constraint on seller_id
            $table->unique('seller_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('seller_balances');
    }
};

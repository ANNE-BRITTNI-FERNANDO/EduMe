<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('tracked_at');
            $table->timestamps();
            
            // Add index for better performance
            $table->index(['order_id', 'tracked_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_tracking');
    }
};

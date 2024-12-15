<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_tracking_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_tracking_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->string('location');
            $table->text('description');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_tracking_updates');
    }
};

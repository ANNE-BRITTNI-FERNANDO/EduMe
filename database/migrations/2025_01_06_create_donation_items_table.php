<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('donation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained('users');
            $table->string('category');
            $table->string('education_level');
            $table->string('condition');
            $table->text('description');
            $table->integer('quantity');
            $table->string('status')->default('pending');
            $table->json('images')->nullable();
            $table->string('verification_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('donation_items');
    }
};

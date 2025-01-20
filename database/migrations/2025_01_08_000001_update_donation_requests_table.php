<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop the old table and create a new one
        Schema::dropIfExists('donation_requests');
        
        Schema::create('donation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('donation_item_id')->nullable()->constrained('donation_items')->onDelete('cascade');
            $table->foreignId('monetary_donation_id')->nullable()->constrained('monetary_donations')->onDelete('cascade');
            $table->integer('quantity');
            $table->enum('purpose', ['personal', 'educational', 'community', 'other']);
            $table->text('purpose_details');
            $table->string('contact_number');
            $table->enum('preferred_contact_time', ['morning', 'afternoon', 'evening']);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('donation_requests');
        
        Schema::create('donation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('donation_id')->constrained('donation_items')->onDelete('cascade');
            $table->integer('quantity');
            $table->enum('purpose', ['personal', 'educational', 'community', 'other']);
            $table->text('purpose_details');
            $table->string('contact_number');
            $table->enum('preferred_contact_time', ['morning', 'afternoon', 'evening']);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }
};

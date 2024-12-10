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
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('bank_transfers')) {
            Schema::create('bank_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->text('bank_details');
                $table->string('payment_slip_path');
                $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('bank_transfers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('budget_amount', 10, 2)->default(0);
            $table->string('cycle_type', 10)->default('monthly');
            $table->timestamp('start_date')->useCurrent();
            $table->timestamps();
        });

        Schema::create('price_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('target_price', 10, 2);
            $table->boolean('notify_when_below')->default(true);
            $table->timestamps();
        });

        Schema::create('budget_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('budget_id')->constrained('user_budgets')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('spent_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->timestamp('cycle_start_date')->useCurrent();
            $table->timestamp('cycle_end_date')->useCurrent();
            $table->timestamp('last_modified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_tracking');
        Schema::dropIfExists('price_alerts');
        Schema::dropIfExists('user_budgets');
    }
};

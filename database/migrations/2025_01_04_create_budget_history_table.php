<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budget_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_budget', 10, 2);
            $table->decimal('spent_amount', 10, 2)->default(0);
            $table->string('cycle_type'); // weekly/monthly
            $table->timestamp('cycle_start_date')->useCurrent();
            $table->timestamp('cycle_end_date')->useCurrent();
            $table->string('status')->default('active'); // active/completed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_history');
    }
};

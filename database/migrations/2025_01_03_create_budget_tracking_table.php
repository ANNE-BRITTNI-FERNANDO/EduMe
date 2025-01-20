<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the table if it exists
        Schema::dropIfExists('budget_tracking');

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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budget_tracking');
    }
};

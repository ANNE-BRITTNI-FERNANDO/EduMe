<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, convert the enum to a string column
        DB::statement("ALTER TABLE user_budgets MODIFY cycle_type VARCHAR(10)");
        
        // Update any existing weekly cycles to monthly
        DB::table('user_budgets')
            ->where('cycle_type', 'weekly')
            ->update(['cycle_type' => 'monthly']);
    }

    public function down()
    {
        // Convert back to enum with original values
        DB::statement("ALTER TABLE user_budgets MODIFY cycle_type ENUM('weekly', 'monthly')");
    }
};

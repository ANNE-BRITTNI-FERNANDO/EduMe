<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('donation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('donation_items', 'available_quantity')) {
                $table->integer('available_quantity')->default(0);
            }
        });

        // Set available_quantity equal to quantity for existing records
        DB::statement('UPDATE donation_items SET available_quantity = quantity WHERE available_quantity = 0');
    }

    public function down()
    {
        Schema::table('donation_items', function (Blueprint $table) {
            if (Schema::hasColumn('donation_items', 'available_quantity')) {
                $table->dropColumn('available_quantity');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bundles', function (Blueprint $table) {
            if (!Schema::hasColumn('bundles', 'is_sold')) {
                $table->boolean('is_sold')->default(false);
            }
            if (!Schema::hasColumn('bundles', 'quantity')) {
                $table->integer('quantity')->default(1);
            }
        });
    }

    public function down()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropColumn(['is_sold', 'quantity']);
        });
    }
};

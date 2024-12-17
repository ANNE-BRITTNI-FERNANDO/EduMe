<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'quantity')) {
                $table->integer('quantity')->default(1);
            }
        });

        Schema::table('bundles', function (Blueprint $table) {
            if (!Schema::hasColumn('bundles', 'quantity')) {
                $table->integer('quantity')->default(1);
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('bundles', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};

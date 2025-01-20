<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('seller_ratings', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('is_anonymous');
        });
    }

    public function down()
    {
        Schema::table('seller_ratings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

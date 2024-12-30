<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('status')->default('pending');
        });

        // Update existing products
        DB::table('products')
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->update(['status' => 'approved']);

        DB::table('products')
            ->where('is_rejected', true)
            ->update(['status' => 'rejected']);
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

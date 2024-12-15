<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('seller_id')->after('user_id')->nullable();
        });

        // Copy user_id to seller_id for existing records
        DB::table('payout_requests')->update([
            'seller_id' => DB::raw('user_id')
        ]);

        // Add foreign key after data is copied
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn('seller_id');
        });
    }
};

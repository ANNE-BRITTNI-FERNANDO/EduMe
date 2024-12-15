<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->foreignId('order_item_id')->nullable()->after('seller_id')->constrained('order_items')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropForeign(['order_item_id']);
            $table->dropColumn('order_item_id');
        });
    }
};

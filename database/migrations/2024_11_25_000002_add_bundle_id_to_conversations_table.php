<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'bundle_id')) {
                $table->unsignedBigInteger('bundle_id')->nullable()->after('product_id');
                $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['bundle_id']);
            $table->dropColumn('bundle_id');
        });
    }
};

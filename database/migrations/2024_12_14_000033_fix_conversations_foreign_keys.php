<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Schema::table('conversations', function (Blueprint $table) {
            // Drop existing foreign keys if they exist
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['bundle_id']);

            // Add foreign keys back
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->nullable()->references('id')->on('products')->onDelete('cascade');
            $table->foreign('bundle_id')->nullable()->references('id')->on('bundles')->onDelete('cascade');
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['bundle_id']);
        });
    }
};

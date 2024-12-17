<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'is_sold')) {
                $table->boolean('is_sold')->default(false);
            }
            if (!Schema::hasColumn('products', 'quantity')) {
                $table->integer('quantity')->default(1);
            }
            if (!Schema::hasColumn('products', 'is_approved')) {
                $table->boolean('is_approved')->default(false);
            }
            if (!Schema::hasColumn('products', 'is_rejected')) {
                $table->boolean('is_rejected')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_sold', 'quantity', 'is_approved', 'is_rejected']);
        });
    }
};

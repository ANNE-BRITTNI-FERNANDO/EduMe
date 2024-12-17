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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('pending');
            }
            if (!Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable();
            }
        });

        // Update existing orders
        DB::table('orders')->update([
            'status' => DB::raw("CASE 
                WHEN delivery_status = 'delivered' THEN 'confirmed'
                WHEN delivery_status = 'processing' THEN 'confirmed'
                WHEN delivery_status = 'shipped' THEN 'confirmed'
                ELSE 'pending'
            END"),
            'confirmed_at' => DB::raw('CASE 
                WHEN delivery_status IN ("delivered", "processing", "shipped") THEN updated_at
                ELSE NULL
            END')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['status', 'confirmed_at']);
        });
    }
};

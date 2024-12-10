<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('seller_balances')) {
            Schema::create('seller_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('available_balance', 10, 2)->default(0);
                $table->decimal('pending_balance', 10, 2)->default(0);
                $table->decimal('total_earned', 10, 2)->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('seller_balances', function (Blueprint $table) {
                if (!Schema::hasColumn('seller_balances', 'pending_balance')) {
                    $table->decimal('pending_balance', 10, 2)->default(0)->after('available_balance');
                }
                if (!Schema::hasColumn('seller_balances', 'total_earned')) {
                    $table->decimal('total_earned', 10, 2)->default(0)->after('pending_balance');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('seller_balances', function (Blueprint $table) {
            $table->dropColumn(['pending_balance', 'total_earned']);
        });
    }
};

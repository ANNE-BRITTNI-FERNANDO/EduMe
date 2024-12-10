<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('bundles', 'user_id')) {
            Schema::table('bundles', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::table('bundles', function (Blueprint $table) {
            if (Schema::hasColumn('bundles', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};

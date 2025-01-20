<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('donation_request_id')->nullable()->after('id')
                  ->constrained('donation_requests')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['donation_request_id']);
            $table->dropColumn('donation_request_id');
        });
    }
};

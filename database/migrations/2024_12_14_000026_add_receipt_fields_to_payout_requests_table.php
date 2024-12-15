<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->string('receipt_path')->nullable();
            $table->timestamp('receipt_uploaded_at')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->foreign('completed_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn([
                'receipt_path',
                'receipt_uploaded_at',
                'transaction_id',
                'completed_at',
                'completed_by'
            ]);
        });
    }
};

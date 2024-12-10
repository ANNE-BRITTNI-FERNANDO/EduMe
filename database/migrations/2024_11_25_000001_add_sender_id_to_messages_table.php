<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            // Drop the existing user_id column if it exists
            if (Schema::hasColumn('messages', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            
            // Add sender_id column with foreign key constraint
            $table->unsignedBigInteger('sender_id')->after('conversation_id');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropColumn('sender_id');
            
            // Restore the original user_id column
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

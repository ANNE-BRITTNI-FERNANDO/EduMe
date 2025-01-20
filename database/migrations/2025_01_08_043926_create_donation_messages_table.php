<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDonationMessagesTable extends Migration
{
    public function up()
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('donation_requests', 'contact_message')) {
                $table->text('contact_message')->nullable();
            }
        });

        if (!Schema::hasTable('donation_messages')) {
            Schema::create('donation_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('donation_request_id')->constrained()->onDelete('cascade');
                $table->foreignId('sender_id')->constrained('users');
                $table->foreignId('receiver_id')->constrained('users');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::table('donation_requests', function (Blueprint $table) {
            if (Schema::hasColumn('donation_requests', 'contact_message')) {
                $table->dropColumn('contact_message');
            }
        });

        Schema::dropIfExists('donation_messages');
    }
}

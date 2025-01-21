<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('donation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('donation_items', 'preferred_contact_method')) {
                $table->string('preferred_contact_method')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('donation_items', function (Blueprint $table) {
            if (Schema::hasColumn('donation_items', 'preferred_contact_method')) {
                $table->dropColumn('preferred_contact_method');
            }
        });
    }
};

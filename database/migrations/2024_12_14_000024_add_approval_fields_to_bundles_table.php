<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->string('rejection_reason')->nullable();
            $table->text('rejection_details')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('last_edited_at')->nullable();
        });

        Schema::table('bundle_categories', function (Blueprint $table) {
            $table->string('status')->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->text('rejection_details')->nullable();
        });
    }

    public function down()
    {
        Schema::table('bundles', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_reason',
                'rejection_details',
                'approved_at',
                'rejected_at',
                'approved_by',
                'is_edited',
                'last_edited_at'
            ]);
        });

        Schema::table('bundle_categories', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'rejection_reason',
                'rejection_details'
            ]);
        });
    }
};

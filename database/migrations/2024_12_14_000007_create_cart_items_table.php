<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create new table
        Schema::create('cart_items_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('bundle_id')->nullable()->constrained('bundles')->onDelete('cascade');
            $table->string('item_type');
            $table->timestamps();
        });

        // Copy data from old table to new table
        DB::statement('
            INSERT INTO cart_items_new (user_id, product_id, bundle_id, item_type, created_at, updated_at)
            SELECT c.user_id, ci.product_id, NULL as bundle_id, "product" as item_type, ci.created_at, ci.updated_at
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
        ');

        // Drop old table
        Schema::dropIfExists('cart_items');

        // Rename new table to cart_items
        Schema::rename('cart_items_new', 'cart_items');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Create the old table structure
        Schema::create('cart_items_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('bundle_id')->nullable()->constrained('bundles')->onDelete('cascade');
            $table->string('item_type');
            $table->timestamps();
        });

        // Copy data back
        DB::statement('
            INSERT INTO cart_items_old (cart_id, product_id, bundle_id, item_type, created_at, updated_at)
            SELECT c.id, ci.product_id, ci.bundle_id, ci.item_type, ci.created_at, ci.updated_at
            FROM cart_items ci
            JOIN carts c ON ci.user_id = c.user_id
        ');

        // Drop new table
        Schema::dropIfExists('cart_items');

        // Rename old table back to cart_items
        Schema::rename('cart_items_old', 'cart_items');
    }
}

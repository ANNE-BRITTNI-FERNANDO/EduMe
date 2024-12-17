<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update seller balances based on order status
        DB::statement("
            UPDATE seller_balances sb
            SET sb.total_earned = (
                SELECT COALESCE(SUM(oi.price * oi.quantity), 0)
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.seller_id = sb.seller_id
                AND o.delivery_status NOT IN ('cancelled')
            ),
            sb.pending_balance = (
                SELECT COALESCE(SUM(oi.price * oi.quantity), 0)
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.seller_id = sb.seller_id
                AND o.delivery_status IN ('pending', 'processing')
            ),
            sb.available_balance = (
                SELECT COALESCE(SUM(oi.price * oi.quantity), 0)
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE oi.seller_id = sb.seller_id
                AND o.delivery_status IN ('completed', 'confirmed')
            )
        ");

        // Update balance_to_be_paid
        DB::statement("
            UPDATE seller_balances
            SET balance_to_be_paid = available_balance + pending_balance
        ");

        // Create seller balances for any sellers that don't have them
        DB::statement("
            INSERT INTO seller_balances (seller_id, available_balance, pending_balance, total_earned, balance_to_be_paid, total_delivery_fees_earned, created_at, updated_at)
            SELECT 
                u.id as seller_id,
                0 as available_balance,
                0 as pending_balance,
                0 as total_earned,
                0 as balance_to_be_paid,
                0 as total_delivery_fees_earned,
                NOW() as created_at,
                NOW() as updated_at
            FROM users u
            LEFT JOIN seller_balances sb ON u.id = sb.seller_id
            WHERE u.role = 'seller'
            AND sb.id IS NULL
        ");
    }

    public function down()
    {
        // Reset all balances to 0
        DB::table('seller_balances')->update([
            'available_balance' => 0,
            'pending_balance' => 0,
            'total_earned' => 0,
            'balance_to_be_paid' => 0,
            'total_delivery_fees_earned' => 0
        ]);
    }
};

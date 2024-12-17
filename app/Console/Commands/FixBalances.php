<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixBalances extends Command
{
    protected $signature = 'fix:balances';
    protected $description = 'Fix seller balances';

    public function handle()
    {
        DB::beginTransaction();
        try {
            // Get all sellers with orders
            $sellerIds = DB::table('order_items')
                ->select('seller_id')
                ->distinct()
                ->get()
                ->pluck('seller_id');

            $this->info('Found ' . count($sellerIds) . ' sellers to update');

            foreach ($sellerIds as $sellerId) {
                // Calculate correct balances for each seller
                $balances = DB::select("
                    SELECT 
                        COALESCE(SUM(CASE 
                            WHEN o.delivery_status IN ('pending', 'processing') 
                            THEN oi.price * oi.quantity
                            ELSE 0 
                        END), 0) as pending_balance,
                        COALESCE(SUM(CASE 
                            WHEN o.delivery_status IN ('completed', 'delivered', 'confirmed', 'delivered_to_warehouse', 'dispatched') 
                            THEN oi.price * oi.quantity
                            ELSE 0 
                        END), 0) as available_balance,
                        COALESCE(SUM(CASE 
                            WHEN o.delivery_status NOT IN ('cancelled') 
                            THEN oi.price * oi.quantity
                            ELSE 0 
                        END), 0) as total_earned,
                        COUNT(DISTINCT o.id) as total_orders,
                        COUNT(DISTINCT CASE WHEN o.delivery_status IN ('pending', 'processing') THEN o.id END) as pending_orders,
                        COUNT(DISTINCT CASE WHEN o.delivery_status IN ('completed', 'delivered', 'confirmed', 'delivered_to_warehouse', 'dispatched') THEN o.id END) as completed_orders,
                        COUNT(DISTINCT CASE WHEN o.delivery_status = 'cancelled' THEN o.id END) as cancelled_orders
                    FROM order_items oi
                    INNER JOIN orders o ON oi.order_id = o.id
                    WHERE oi.seller_id = ?
                ", [$sellerId]);

                if (!empty($balances)) {
                    $balance = $balances[0];
                    
                    // Update or create seller balance
                    DB::statement("
                        INSERT INTO seller_balances 
                            (seller_id, pending_balance, available_balance, total_earned, balance_to_be_paid, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE
                            pending_balance = VALUES(pending_balance),
                            available_balance = VALUES(available_balance),
                            total_earned = VALUES(total_earned),
                            balance_to_be_paid = VALUES(pending_balance) + VALUES(available_balance),
                            updated_at = NOW()
                    ", [
                        $sellerId,
                        $balance->pending_balance,
                        $balance->available_balance,
                        $balance->total_earned,
                        $balance->pending_balance + $balance->available_balance
                    ]);

                    $this->info("Updated balances for seller $sellerId:");
                    $this->info("  Pending: {$balance->pending_balance}");
                    $this->info("  Available: {$balance->available_balance}");
                    $this->info("  Total Earned: {$balance->total_earned}");
                    $this->info("  Total Orders: {$balance->total_orders}");
                    $this->info("  Pending Orders: {$balance->pending_orders}");
                    $this->info("  Completed Orders: {$balance->completed_orders}");
                    $this->info("  Cancelled Orders: {$balance->cancelled_orders}");
                }
            }

            DB::commit();
            $this->info('All balances have been fixed!');
        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Failed to fix balances: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}

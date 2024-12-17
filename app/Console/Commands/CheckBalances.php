<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckBalances extends Command
{
    protected $signature = 'check:balances';
    protected $description = 'Check order and balance status';

    public function handle()
    {
        $this->info('Checking orders...');
        $orders = DB::select("
            SELECT o.id, o.delivery_status, oi.seller_id, oi.price * oi.quantity as amount 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id
        ");
        $this->table(
            ['Order ID', 'Status', 'Seller ID', 'Amount'],
            array_map(fn($order) => (array)$order, $orders)
        );

        $this->info('Checking seller balances...');
        $balances = DB::select("SELECT * FROM seller_balances");
        $this->table(
            ['ID', 'Seller ID', 'Available', 'Pending', 'Total Earned', 'To Be Paid'],
            array_map(fn($balance) => [
                $balance->id,
                $balance->seller_id,
                $balance->available_balance,
                $balance->pending_balance,
                $balance->total_earned,
                $balance->balance_to_be_paid
            ], $balances)
        );
    }
}

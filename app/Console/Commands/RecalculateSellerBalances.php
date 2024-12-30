<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SellerBalance;
use App\Models\User;
use DB;

class RecalculateSellerBalances extends Command
{
    protected $signature = 'seller:recalculate-balances {seller_id?}';
    protected $description = 'Recalculate balances for all sellers or a specific seller';

    public function handle()
    {
        $sellerId = $this->argument('seller_id');

        if ($sellerId) {
            $sellerBalance = SellerBalance::where('seller_id', $sellerId)->first();
            if ($sellerBalance) {
                $this->recalculateBalance($sellerBalance);
                $this->info("Recalculated balance for seller {$sellerId}");
            } else {
                $this->error("Seller balance not found for ID {$sellerId}");
            }
        } else {
            $sellerBalances = SellerBalance::all();
            foreach ($sellerBalances as $balance) {
                $this->recalculateBalance($balance);
            }
            $this->info("Recalculated balances for all sellers");
        }
    }

    protected function recalculateBalance(SellerBalance $sellerBalance)
    {
        DB::transaction(function () use ($sellerBalance) {
            // Get all warehouse confirmed orders
            $orders = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('order_items.seller_id', $sellerBalance->seller_id)
                ->whereNotNull('orders.warehouse_confirmed_at')
                ->select(
                    'orders.id',
                    'orders.warehouse_confirmed_at',
                    DB::raw('SUM(order_items.quantity * order_items.price) as total_amount')
                )
                ->groupBy('orders.id', 'orders.warehouse_confirmed_at')
                ->get();

            // Calculate total earnings
            $totalEarnings = $orders->sum('total_amount');

            // Get all payouts
            $payouts = DB::table('payout_requests')
                ->where('seller_id', $sellerBalance->seller_id)
                ->select('status', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            // Calculate completed and pending payouts
            $completedPayouts = isset($payouts['completed']) ? $payouts['completed']->total_amount : 0;
            $pendingPayouts = isset($payouts['pending']) ? $payouts['pending']->total_amount : 0;

            // Calculate balances
            $balanceToBePaid = $totalEarnings - $completedPayouts;
            $availableBalance = $balanceToBePaid - $pendingPayouts;

            // Update database directly
            DB::statement("
                UPDATE seller_balances 
                SET 
                    total_earned = ?,
                    balance_to_be_paid = ?,
                    available_balance = ?,
                    pending_balance = ?,
                    updated_at = NOW()
                WHERE seller_id = ?
            ", [
                $totalEarnings,
                $balanceToBePaid,
                $availableBalance,
                $pendingPayouts,
                $sellerBalance->seller_id
            ]);

            // Output the results
            $this->info("Seller {$sellerBalance->seller_id} balances:");
            $this->info("Total Earned: {$totalEarnings}");
            $this->info("Completed Payouts: {$completedPayouts}");
            $this->info("Pending Payouts: {$pendingPayouts}");
            $this->info("Available Balance: {$availableBalance}");
            $this->info("Pending Balance: {$pendingPayouts}");
            $this->info("Balance to be Paid: {$balanceToBePaid}");

            // Log the calculations
            \Log::info('Balance calculation details for seller ' . $sellerBalance->seller_id, [
                'raw_values' => [
                    'total_earnings' => $totalEarnings,
                    'completed_payouts' => $completedPayouts,
                    'pending_payouts' => $pendingPayouts
                ],
                'calculations' => [
                    'balance_to_be_paid' => "{$totalEarnings} - {$completedPayouts} = {$balanceToBePaid}",
                    'available_balance' => "{$balanceToBePaid} - {$pendingPayouts} = {$availableBalance}"
                ],
                'final_balances' => [
                    'total_earned' => $totalEarnings,
                    'balance_to_be_paid' => $balanceToBePaid,
                    'available_balance' => $availableBalance,
                    'pending_balance' => $pendingPayouts
                ]
            ]);
        });
    }
}

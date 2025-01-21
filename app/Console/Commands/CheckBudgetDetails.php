<?php

namespace App\Console\Commands;

use App\Models\BudgetTracking;
use App\Models\Order;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckBudgetDetails extends Command
{
    protected $signature = 'budget:check-details';
    protected $description = 'Check budget tracking details including orders and cycle dates';

    public function handle()
    {
        $now = Carbon::parse('2025-01-04T18:30:36+05:30');
        $this->info('Checking budget tracking details...');

        // Get active budget tracking
        $budgetTracking = BudgetTracking::where('cycle_end_date', '>', $now)->first();
        
        if (!$budgetTracking) {
            $this->error('No active budget tracking found!');
            return;
        }

        // Display Budget Tracking Details
        $this->info("\nBudget Tracking Details:");
        $this->table(
            ['ID', 'User ID', 'Total Amount', 'Spent Amount', 'Cycle Start', 'Cycle End'],
            [[
                $budgetTracking->id,
                $budgetTracking->user_id,
                number_format($budgetTracking->total_amount, 2),
                number_format($budgetTracking->spent_amount, 2),
                $budgetTracking->cycle_start_date,
                $budgetTracking->cycle_end_date,
            ]]
        );

        // Check all orders for this user
        $allOrders = Order::where('user_id', $budgetTracking->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->info("\nAll Orders for User (including those outside budget cycle):");
        if ($allOrders->isEmpty()) {
            $this->warn('No orders found for this user.');
        } else {
            $this->table(
                ['Order ID', 'Created At', 'Total Amount', 'Status', 'In Current Cycle'],
                $allOrders->map(function ($order) use ($budgetTracking) {
                    $inCycle = $order->created_at->between(
                        $budgetTracking->cycle_start_date,
                        $budgetTracking->cycle_end_date
                    );
                    return [
                        $order->id,
                        $order->created_at,
                        number_format($order->total_amount, 2),
                        $order->status,
                        $inCycle ? 'Yes' : 'No'
                    ];
                })
            );
        }

        // Check orders specifically within the budget cycle
        $cycleOrders = Order::where('user_id', $budgetTracking->user_id)
            ->where('created_at', '>=', $budgetTracking->cycle_start_date)
            ->where('created_at', '<=', $budgetTracking->cycle_end_date)
            ->get();

        $this->info("\nOrders Within Current Budget Cycle:");
        if ($cycleOrders->isEmpty()) {
            $this->warn('No orders found within the current budget cycle.');
        } else {
            $totalSpent = $cycleOrders->where('status', '!=', 'cancelled')->sum('total_amount');
            $this->table(
                ['Order ID', 'Created At', 'Total Amount', 'Status', 'Counted in Budget'],
                $cycleOrders->map(function ($order) {
                    return [
                        $order->id,
                        $order->created_at,
                        number_format($order->total_amount, 2),
                        $order->status,
                        $order->status != 'cancelled' ? 'Yes' : 'No'
                    ];
                })
            );
            $this->info("\nTotal amount that should be counted: " . number_format($totalSpent, 2));
        }

        // Time check
        $this->info("\nTime Check:");
        $this->line("Current Time: " . $now->format('Y-m-d H:i:s'));
        $this->line("Cycle Start: " . $budgetTracking->cycle_start_date->format('Y-m-d H:i:s'));
        $this->line("Cycle End: " . $budgetTracking->cycle_end_date->format('Y-m-d H:i:s'));
    }
}

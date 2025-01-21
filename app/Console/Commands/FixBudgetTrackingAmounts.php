<?php

namespace App\Console\Commands;

use App\Models\BudgetTracking;
use App\Models\Order;
use Illuminate\Console\Command;
use Carbon\Carbon;

class FixBudgetTrackingAmounts extends Command
{
    protected $signature = 'budget:fix-amounts';
    protected $description = 'Fix spent and remaining amounts for all active budget tracking records';

    public function handle()
    {
        $this->info('Starting to fix budget tracking amounts...');
        $now = Carbon::parse('2025-01-04T18:30:02+05:30');

        $budgetTrackings = BudgetTracking::where('cycle_end_date', '>', $now)->get();
        $bar = $this->output->createProgressBar(count($budgetTrackings));
        $bar->start();

        foreach ($budgetTrackings as $tracking) {
            // Calculate total spent from orders in this cycle
            $spentAmount = Order::where('user_id', $tracking->user_id)
                ->where('created_at', '>=', $tracking->cycle_start_date)
                ->where('created_at', '<=', $tracking->cycle_end_date)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            
            // Update the tracking record
            $tracking->spent_amount = $spentAmount;
            $tracking->remaining_amount = max(0, $tracking->total_amount - $spentAmount);
            $tracking->save();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Budget tracking amounts have been fixed successfully!');
        
        // Show summary of updates
        $this->table(
            ['Budget ID', 'User ID', 'Total Amount', 'Spent Amount', 'Remaining Amount'],
            $budgetTrackings->map(function ($tracking) {
                return [
                    $tracking->id,
                    $tracking->user_id,
                    number_format($tracking->total_amount, 2),
                    number_format($tracking->spent_amount, 2),
                    number_format($tracking->remaining_amount, 2),
                ];
            })
        );
    }
}

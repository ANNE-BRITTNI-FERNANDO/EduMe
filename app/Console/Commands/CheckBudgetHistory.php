<?php

namespace App\Console\Commands;

use App\Models\BudgetHistory;
use App\Models\BudgetTracking;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckBudgetHistory extends Command
{
    protected $signature = 'budget:check-history';
    protected $description = 'Check budget history records';

    public function handle()
    {
        $now = Carbon::parse('2025-01-04T18:35:48+05:30');
        $this->info('Checking budget history records...');

        // Get all budget history records
        $histories = BudgetHistory::orderBy('created_at', 'desc')->get();
        
        if ($histories->isEmpty()) {
            $this->warn('No budget history records found.');
            
            // Check if we have any budget tracking records
            $trackings = BudgetTracking::orderBy('created_at', 'desc')->get();
            if ($trackings->isNotEmpty()) {
                $this->info("\nFound Budget Tracking records that should have history:");
                $this->table(
                    ['ID', 'User ID', 'Total Amount', 'Spent Amount', 'Cycle Start', 'Cycle End', 'Created At'],
                    $trackings->map(function ($tracking) {
                        return [
                            $tracking->id,
                            $tracking->user_id,
                            number_format($tracking->total_amount, 2),
                            number_format($tracking->spent_amount, 2),
                            $tracking->cycle_start_date,
                            $tracking->cycle_end_date,
                            $tracking->created_at,
                        ];
                    })
                );

                // Create missing history records
                foreach ($trackings as $tracking) {
                    BudgetHistory::create([
                        'user_id' => $tracking->user_id,
                        'total_budget' => $tracking->total_amount,
                        'spent_amount' => $tracking->spent_amount,
                        'cycle_type' => $tracking->budget->cycle_type,
                        'cycle_start_date' => $tracking->cycle_start_date,
                        'cycle_end_date' => $tracking->cycle_end_date,
                        'status' => $now->gt($tracking->cycle_end_date) ? 'completed' : 'active'
                    ]);
                }
                $this->info('Created missing budget history records.');
            } else {
                $this->info('No budget tracking records found either.');
            }
        } else {
            $this->info("\nExisting Budget History records:");
            $this->table(
                ['ID', 'User ID', 'Total Budget', 'Spent Amount', 'Cycle Type', 'Cycle Start', 'Cycle End', 'Status'],
                $histories->map(function ($history) {
                    return [
                        $history->id,
                        $history->user_id,
                        number_format($history->total_budget, 2),
                        number_format($history->spent_amount, 2),
                        $history->cycle_type,
                        $history->cycle_start_date,
                        $history->cycle_end_date,
                        $history->status,
                    ];
                })
            );
        }
    }
}

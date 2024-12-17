<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PayoutRequest;

return new class extends Migration
{
    public function up()
    {
        // Fix any completed payouts that don't have completed_at or transaction_id
        $completedPayouts = PayoutRequest::where('status', 'completed')
            ->whereNull('completed_at')
            ->get();

        foreach ($completedPayouts as $payout) {
            $payout->update([
                'completed_at' => $payout->processed_at ?? $payout->updated_at,
                'transaction_id' => $payout->transaction_id ?? 'TXN-' . str_pad($payout->id, 6, '0', STR_PAD_LEFT),
                'completed_by' => $payout->processed_by ?? 1
            ]);
        }
    }

    public function down()
    {
        // This migration is not reversible
    }
};

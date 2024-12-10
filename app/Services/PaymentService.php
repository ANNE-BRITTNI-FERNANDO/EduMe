<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\SellerBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function allocatePayment(Order $order)
    {
        DB::transaction(function () use ($order) {
            // Group order items by seller
            $sellerTotals = [];
            foreach ($order->items as $item) {
                $sellerId = $item->product->user_id;
                if (!isset($sellerTotals[$sellerId])) {
                    $sellerTotals[$sellerId] = 0;
                }
                $sellerTotals[$sellerId] += $item->total_price;
            }

            // Allocate earnings to each seller
            foreach ($sellerTotals as $sellerId => $amount) {
                $sellerBalance = SellerBalance::firstOrCreate(
                    ['user_id' => $sellerId],
                    [
                        'available_balance' => 0,
                        'pending_balance' => 0,
                        'total_earned' => 0
                    ]
                );

                $sellerBalance->addEarnings($amount);
            }
        });
    }

    public function requestPayout(User $seller, $amount, array $bankDetails)
    {
        return DB::transaction(function () use ($seller, $amount, $bankDetails) {
            // Get seller balance
            $sellerBalance = $seller->sellerBalance;

            // Validate seller has sufficient available balance
            if (!$sellerBalance || $sellerBalance->available_balance < $amount) {
                throw new \Exception('Insufficient available balance for payout');
            }

            // Create payout request
            $payoutRequest = new PayoutRequest([
                'user_id' => $seller->id,
                'amount' => $amount,
                'bank_name' => $bankDetails['bank_name'],
                'account_number' => $bankDetails['account_number'],
                'account_holder_name' => $bankDetails['account_holder_name'],
                'status' => 'pending'
            ]);

            // Save payout request
            $payoutRequest->save();

            // Deduct the amount from available balance and move to pending
            $sellerBalance->available_balance -= $amount;
            $sellerBalance->pending_balance += $amount;
            $sellerBalance->save();

            Log::info('Payout request created', [
                'seller_id' => $seller->id,
                'amount' => $amount,
                'payout_request_id' => $payoutRequest->id
            ]);

            return $payoutRequest;
        });
    }

    public function approvePayout(PayoutRequest $payoutRequest, User $admin)
    {
        return DB::transaction(function () use ($payoutRequest, $admin) {
            // Verify payout request is pending
            if ($payoutRequest->status !== 'pending') {
                throw new \Exception('Payout request is not in pending status');
            }

            // Get seller balance
            $sellerBalance = $payoutRequest->user->sellerBalance;
            
            // Verify seller has sufficient pending balance
            if ($sellerBalance->pending_balance < $payoutRequest->amount) {
                throw new \Exception('Insufficient pending balance');
            }

            // Update payout request status
            $payoutRequest->approve($admin);

            // Deduct from pending balance
            $sellerBalance->pending_balance -= $payoutRequest->amount;
            $sellerBalance->save();

            Log::info('Payout request approved', [
                'payout_request_id' => $payoutRequest->id,
                'seller_id' => $payoutRequest->user_id,
                'admin_id' => $admin->id,
                'amount' => $payoutRequest->amount
            ]);

            return $payoutRequest;
        });
    }

    public function completePayout(PayoutRequest $payoutRequest)
    {
        return DB::transaction(function () use ($payoutRequest) {
            // Verify payout request is approved
            if ($payoutRequest->status !== 'approved') {
                throw new \Exception('Payout request must be approved first');
            }

            // Update payout request status
            $payoutRequest->complete();

            Log::info('Payout request completed', [
                'payout_request_id' => $payoutRequest->id,
                'seller_id' => $payoutRequest->user_id,
                'amount' => $payoutRequest->amount
            ]);

            return $payoutRequest;
        });
    }

    public function rejectPayout(PayoutRequest $payoutRequest, User $admin, $notes = null)
    {
        return DB::transaction(function () use ($payoutRequest, $admin, $notes) {
            // Verify payout request is pending
            if ($payoutRequest->status !== 'pending') {
                throw new \Exception('Payout request is not in pending status');
            }

            // Get seller balance
            $sellerBalance = $payoutRequest->user->sellerBalance;

            // Return amount from pending to available balance
            $sellerBalance->pending_balance -= $payoutRequest->amount;
            $sellerBalance->available_balance += $payoutRequest->amount;
            $sellerBalance->save();

            // Update payout request status
            $payoutRequest->reject($admin, $notes);

            Log::info('Payout request rejected', [
                'payout_request_id' => $payoutRequest->id,
                'seller_id' => $payoutRequest->user_id,
                'admin_id' => $admin->id,
                'amount' => $payoutRequest->amount,
                'notes' => $notes
            ]);

            return $payoutRequest;
        });
    }

    public function releasePendingBalance(User $seller)
    {
        $sellerBalance = $seller->sellerBalance;
        if ($sellerBalance) {
            $sellerBalance->releasePendingBalance();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PayoutRequest;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutRequestController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_holder_name' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $user = Auth::user();
        $sellerBalance = $user->sellerBalance;

        if (!$sellerBalance || $sellerBalance->available_balance < $validated['amount']) {
            return back()->with('error', 'Insufficient available balance');
        }

        $payoutRequest = PayoutRequest::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_holder_name' => $validated['account_holder_name'],
            'notes' => $validated['notes'],
            'status' => 'pending'
        ]);

        // Deduct from available balance when request is created
        $sellerBalance->decrement('available_balance', $validated['amount']);

        return back()->with('success', 'Payout request submitted successfully');
    }

    public function approve(PayoutRequest $payoutRequest)
    {
        try {
            $this->payoutService->handlePayoutRequest($payoutRequest, 'approved');
            return back()->with('success', 'Payout request approved successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, PayoutRequest $payoutRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255'
        ]);

        try {
            $this->payoutService->handlePayoutRequest(
                $payoutRequest, 
                'rejected',
                $validated['rejection_reason']
            );
            return back()->with('success', 'Payout request rejected successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

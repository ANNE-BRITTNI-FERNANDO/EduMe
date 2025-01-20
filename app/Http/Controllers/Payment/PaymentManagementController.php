<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\BankTransfer;
use Illuminate\Http\Request;

class PaymentManagementController extends Controller
{
    public function index()
    {
        // Get pending payments (uploaded but not confirmed/rejected)
        $pendingPayments = BankTransfer::whereHas('conversation', function ($query) {
                $query->where('seller_id', auth()->id());
            })
            ->where('status', 'pending')
            ->with(['conversation.buyer'])
            ->latest()
            ->get();

        // Get recent payments (confirmed or rejected)
        $recentPayments = BankTransfer::whereHas('conversation', function ($query) {
                $query->where('seller_id', auth()->id());
            })
            ->whereIn('status', ['confirmed', 'rejected'])
            ->with(['conversation.buyer'])
            ->latest()
            ->take(10)
            ->get();

        return view('payments.manage', [
            'pendingPayments' => $pendingPayments,
            'recentPayments' => $recentPayments,
        ]);
    }
}

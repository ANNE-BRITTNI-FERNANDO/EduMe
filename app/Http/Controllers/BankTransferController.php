<?php

namespace App\Http\Controllers;

use App\Models\BankTransfer;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PaymentSlipUploaded;
use App\Notifications\PaymentConfirmed;
use App\Notifications\PaymentRejected;

class BankTransferController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        try {
            $request->validate([
                'bank_details' => 'required|string',
                'payment_slip' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
                'amount' => 'required|numeric|min:0',
            ]);

            // Ensure user is the buyer
            if ($conversation->buyer_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only the buyer can upload payment slips.',
                ], 403);
            }

            // Check if there's already a pending payment
            $pendingPayment = $conversation->bankTransfers()
                ->where('status', 'pending')
                ->first();

            if ($pendingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'There is already a pending payment for this conversation.',
                ], 400);
            }

            // Store the payment slip
            if (!$request->hasFile('payment_slip')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment slip file not found in request.',
                ], 400);
            }

            $file = $request->file('payment_slip');
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file upload.',
                ], 400);
            }

            // Store with original filename
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('payment-slips', $filename, 'public');
            
            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store the file. Please try again.',
                ], 500);
            }

            // Create bank transfer record
            $bankTransfer = BankTransfer::create([
                'conversation_id' => $conversation->id,
                'amount' => $request->amount,
                'bank_details' => $request->bank_details,
                'payment_slip_path' => $path,
                'status' => 'pending',
            ]);

            // Create a message in the conversation
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'content' => '✅ Payment slip uploaded for $' . number_format($request->amount, 2) . '. Waiting for seller confirmation.',
            ]);

            // Notify the seller
            try {
                $seller = $conversation->seller;
                Notification::send($seller, new PaymentSlipUploaded($bankTransfer));
            } catch (\Exception $e) {
                // Log notification error but don't fail the request
                \Log::error('Failed to send notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment slip uploaded successfully',
                'bank_transfer' => $bankTransfer,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Bank transfer error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
                'debug_message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function confirm(Request $request, Conversation $conversation)
    {
        // Find the pending bank transfer for this conversation
        $bankTransfer = $conversation->bankTransfers()
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();
        
        // Ensure user is the seller
        if ($conversation->seller_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the seller can confirm payments.',
            ], 403);
        }

        if ($bankTransfer->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been ' . $bankTransfer->status,
            ], 400);
        }

        $bankTransfer->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Create a message in the conversation
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'content' => '✅ Payment of $' . number_format($bankTransfer->amount, 2) . ' has been confirmed. Transaction completed successfully.',
        ]);

        // Notify the buyer
        $buyer = $conversation->buyer;
        Notification::send($buyer, new PaymentConfirmed($bankTransfer));

        // Mark the original payment slip notification as read for the seller
        auth()->user()
            ->notifications()
            ->where('type', 'App\\Notifications\\PaymentSlipUploaded')
            ->where('data->conversation_id', $conversation->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully',
        ]);
    }

    public function reject(Request $request, Conversation $conversation)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Find the pending bank transfer for this conversation
        $bankTransfer = $conversation->bankTransfers()
            ->where('status', 'pending')
            ->latest()
            ->firstOrFail();

        // Ensure user is the seller
        if ($conversation->seller_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the seller can reject payments.',
            ], 403);
        }

        if ($bankTransfer->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This payment has already been ' . $bankTransfer->status,
            ], 400);
        }

        $bankTransfer->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'rejected_at' => now(),
        ]);

        // Create a message in the conversation
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'content' => '❌ Payment of $' . number_format($bankTransfer->amount, 2) . ' has been rejected. Reason: ' . $request->reason,
        ]);

        // Notify the buyer
        $buyer = $conversation->buyer;
        Notification::send($buyer, new PaymentRejected($bankTransfer));

        // Mark the original payment slip notification as read for the seller
        auth()->user()
            ->notifications()
            ->where('type', 'App\\Notifications\\PaymentSlipUploaded')
            ->where('data->conversation_id', $conversation->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Payment rejected successfully',
        ]);
    }
}

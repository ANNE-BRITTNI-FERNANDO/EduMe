<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['product', 'bundle'])
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function createBankTransferOrder(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'bank_transfer_details' => 'required|string',
        ]);

        // Determine if this is for a product or bundle
        $itemType = $conversation->product_id ? 'product' : 'bundle';
        $itemId = $conversation->product_id ?? $conversation->bundle_id;

        $order = Order::create([
            'user_id' => Auth::id(),
            'seller_id' => $conversation->seller_id,
            'item_id' => $itemId,
            'item_type' => $itemType,
            'amount' => $validated['total_amount'],
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
            'bank_transfer_details' => $validated['bank_transfer_details'],
        ]);

        // Send a system message in the conversation about the order
        $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'content' => "🏦 Bank transfer order created! Order #" . $order->id,
            'is_system_message' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bank transfer order created successfully',
            'order' => $order
        ]);
    }

    public function confirmOrder(Request $request, Order $order)
    {
        // Only the seller can confirm the order
        if ($order->seller_id !== Auth::id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        $order->update([
            'status' => 'confirmed',
            'confirmed_at' => now()
        ]);

        // Send a system message in the conversation
        if ($order->conversation) {
            $order->conversation->messages()->create([
                'sender_id' => Auth::id(),
                'content' => "✅ Order #" . $order->id . " has been confirmed by the seller!",
                'is_system_message' => true
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order confirmed successfully',
            'order' => $order
        ]);
    }

    public function getOrderDetails(Order $order)
    {
        // Check if user is either buyer or seller
        if ($order->user_id !== Auth::id() && $order->seller_id !== Auth::id()) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'order' => $order->load(['user', 'seller'])
        ]);
    }
}

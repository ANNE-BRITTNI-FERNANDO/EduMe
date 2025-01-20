<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\DonationRequest;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewDonationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonationChatController extends Controller
{
    public function show(DonationRequest $donationRequest)
    {
        // Load the donation relationship
        $donationRequest->load('donationItem');

        // Check if the donation exists
        if (!$donationRequest->donationItem) {
            abort(404, 'The requested donation no longer exists.');
        }

        // Check if user is either the donor or recipient
        if (Auth::id() !== $donationRequest->user_id && Auth::id() !== $donationRequest->donationItem->user_id) {
            abort(403, 'You are not authorized to view this chat.');
        }

        // Get or create conversation
        $conversation = Conversation::firstOrCreate(
            ['donation_request_id' => $donationRequest->id],
            [
                'buyer_id' => $donationRequest->user_id, // recipient
                'seller_id' => $donationRequest->donationItem->user_id, // donor
                'last_message_at' => now(),
            ]
        );

        // Mark messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('donor.chat', [
            'conversation' => $conversation->load(['messages.sender', 'donationRequest.donationItem']),
            'otherUser' => $conversation->getOtherParticipant(Auth::user()),
        ]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        // Load the donation request and donation
        $conversation->load('donationRequest.donationItem');

        // Check if the donation still exists
        if (!$conversation->donationRequest || !$conversation->donationRequest->donationItem) {
            abort(404, 'The requested donation no longer exists.');
        }

        // Check if user is authorized to send messages in this conversation
        if (Auth::id() !== $conversation->buyer_id && Auth::id() !== $conversation->seller_id) {
            abort(403, 'You are not authorized to send messages in this conversation.');
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Create the message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'content' => $request->message,
        ]);

        // Update conversation's last message timestamp
        $conversation->update([
            'last_message_at' => now()
        ]);

        // Get the other participant
        $otherUser = $conversation->getOtherParticipant(Auth::user());

        // Notify the other user
        if ($otherUser) {
            $otherUser->notify(new NewDonationMessage($message));
        }

        return response()->json([
            'message' => $message->load('sender'),
            'status' => 'success'
        ]);
    }

    public function getNewMessages(Conversation $conversation, $lastMessageId)
    {
        // Check if user is authorized to view messages
        if (Auth::id() !== $conversation->buyer_id && Auth::id() !== $conversation->seller_id) {
            abort(403, 'You are not authorized to view messages in this conversation.');
        }

        // Get new messages
        $messages = Message::where('conversation_id', $conversation->id)
            ->where('id', '>', $lastMessageId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read if they're not from the current user
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages,
            'status' => 'success'
        ]);
    }
}

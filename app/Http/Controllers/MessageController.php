<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Conversation;
use App\Notifications\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        $request->validate([
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'content' => $request->content
        ]);

        // Handle attachment if present
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('chat-attachments', 'public');
            $message->attachment_path = $path;
            $message->attachment_type = $file->getMimeType();
            $message->save();
        }

        // Load the necessary relationships
        $message->load(['sender', 'conversation.product', 'conversation.bundle']);

        // Determine the recipient (the other user in the conversation)
        $recipient = $conversation->buyer_id === Auth::id() 
            ? $conversation->seller 
            : $conversation->buyer;

        // Send notification to the recipient
        try {
            $recipient->notify(new NewMessage($message));
        } catch (\Exception $e) {
            \Log::error('Failed to send message notification: ' . $e->getMessage());
        }

        // Update conversation's last_message_at
        $conversation->update(['last_message_at' => now()]);

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message,
            'sender' => $message->sender
        ]);
    }

    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        // Check if user is part of the conversation
        if ($conversation->buyer_id !== auth()->id() && $conversation->seller_id !== auth()->id()) {
            abort(403);
        }

        $messages = $conversation->messages()->with('sender')->get()->map(function ($message) {
            return [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'content' => $message->content,
                'attachment_path' => $message->attachment_path,
                'attachment_type' => $message->attachment_type,
                'attachment_url' => $message->attachment_path ? Storage::url($message->attachment_path) : null,
                'created_at' => $message->created_at,
                'read_at' => $message->read_at,
            ];
        });

        return response()->json(['messages' => $messages]);
    }

    public function markAllRead($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        // Check if user is part of the conversation
        if ($conversation->buyer_id !== auth()->id() && $conversation->seller_id !== auth()->id()) {
            abort(403);
        }

        // Mark all messages from the other user as read
        $conversation->messages()
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}

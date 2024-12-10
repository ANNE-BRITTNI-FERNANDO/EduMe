<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function startConversation(Request $request, $id = null)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            // Get the route parameters and determine type
            $routeName = $request->route()->getName();
            $type = str_contains($routeName, 'bundle') ? 'bundle' : 'product';

            \Log::info('Starting conversation', [
                'route_name' => $routeName,
                'id' => $id,
                'type' => $type,
                'user_id' => Auth::id()
            ]);

            // Find the item based on type
            if ($type === 'product') {
                $item = Product::with('user')->findOrFail($id);
            } else {
                $item = Bundle::with('user')->findOrFail($id);
            }

            \Log::info('Item found', [
                'type' => $type,
                'item' => $item->toArray(),
                'user_id' => $item->user_id,
                'user' => $item->user ? $item->user->toArray() : null
            ]);

            // Prevent seller from chatting with themselves
            if ($item->user_id === Auth::id()) {
                return back()->with('error', 'You cannot start a conversation with yourself.');
            }

            // Find existing conversation or create new one
            $conditions = [
                'seller_id' => $item->user_id,
                'buyer_id' => Auth::id()
            ];

            if ($type === 'product') {
                $conditions['product_id'] = $item->id;
                $conditions['bundle_id'] = null;
            } else {
                $conditions['bundle_id'] = $item->id;
                $conditions['product_id'] = null;
            }

            \Log::info('Searching for conversation with conditions', [
                'conditions' => $conditions
            ]);

            $conversation = Conversation::where($conditions)->first();

            if (!$conversation) {
                $data = $conditions;
                $data['last_message_at'] = now();

                \Log::info('Creating new conversation', [
                    'data' => $data
                ]);

                $conversation = Conversation::create($data);
            }

            \Log::info('Conversation ready', [
                'conversation_id' => $conversation->id,
                'type' => $type,
                'bundle_id' => $conversation->bundle_id,
                'product_id' => $conversation->product_id,
                'redirect_url' => route('chat.show', ['conversation' => $conversation->id])
            ]);

            // Load the conversation with its relationships
            $conversation->load(['messages', 'seller', 'buyer']);
            if ($type === 'product') {
                $conversation->load('product');
            } else {
                $conversation->load('bundle');
            }

            // Return the view directly with debug information
            \Log::info('Rendering chat view', [
                'conversation_id' => $conversation->id,
                'type' => $type,
                'messages_count' => $conversation->messages->count(),
                'seller' => $conversation->seller->toArray(),
                'buyer' => $conversation->buyer->toArray(),
                $type => $conversation->{$type}->toArray()
            ]);

            return view('chat.show', [
                'conversation' => $conversation,
                'messages' => $conversation->messages
            ])->with('debug', true); // Add debug flag
        } catch (\Exception $e) {
            \Log::error('Error in startConversation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'route' => $request->route()->getName(),
                'parameters' => $request->route()->parameters()
            ]);
            return back()->with('error', 'An error occurred while starting the conversation. Please try again.');
        }
    }

    public function startBundleConversation(Request $request, Bundle $bundle)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                \Log::error('User not authenticated');
                return redirect()->route('login');
            }

            \Log::info('Starting bundle conversation', [
                'bundle_id' => $bundle->id,
                'user_id' => Auth::id(),
                'request_url' => $request->url(),
                'request_method' => $request->method()
            ]);

            // Prevent seller from chatting with themselves
            if ($bundle->user_id === Auth::id()) {
                \Log::warning('User trying to chat with themselves', [
                    'user_id' => Auth::id(),
                    'bundle_user_id' => $bundle->user_id
                ]);
                return back()->with('error', 'You cannot start a conversation with yourself.');
            }

            // Find existing conversation or create new one
            $conversation = Conversation::where([
                'seller_id' => $bundle->user_id,
                'buyer_id' => Auth::id(),
                'bundle_id' => $bundle->id,
                'product_id' => null
            ])->first();

            if (!$conversation) {
                \Log::info('Creating new bundle conversation', [
                    'bundle_id' => $bundle->id,
                    'seller_id' => $bundle->user_id,
                    'buyer_id' => Auth::id()
                ]);

                $conversation = Conversation::create([
                    'seller_id' => $bundle->user_id,
                    'buyer_id' => Auth::id(),
                    'bundle_id' => $bundle->id,
                    'product_id' => null,
                    'last_message_at' => now()
                ]);
            }

            \Log::info('Bundle conversation ready', [
                'conversation_id' => $conversation->id,
                'bundle_id' => $conversation->bundle_id,
                'redirect_url' => route('test.chat.show', ['conversation' => $conversation->id])
            ]);

            // Use test route for debugging
            return redirect()->route('test.chat.show', ['conversation' => $conversation->id]);

        } catch (\Exception $e) {
            \Log::error('Error in startBundleConversation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'bundle_id' => $bundle->id ?? null
            ]);
            return back()->with('error', 'An error occurred while starting the conversation. Please try again.');
        }
    }

    public function showConversation(Conversation $conversation)
    {
        \Log::info('Entering showConversation', [
            'conversation_id' => $conversation->id,
            'product_id' => $conversation->product_id,
            'bundle_id' => $conversation->bundle_id,
            'seller_id' => $conversation->seller_id,
            'buyer_id' => $conversation->buyer_id,
            'request_url' => request()->url(),
            'request_method' => request()->method()
        ]);

        // Check if user is participant
        if (!$this->isParticipant($conversation)) {
            \Log::warning('Unauthorized conversation access attempt', [
                'user_id' => Auth::id(),
                'conversation_id' => $conversation->id
            ]);
            return redirect()->route('chat.index')->with('error', 'You do not have access to this conversation.');
        }

        try {
            // Load messages with sender information
            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Mark unread messages as read
            $conversation->messages()
                ->whereNull('read_at')
                ->where('sender_id', '!=', Auth::id())
                ->update(['read_at' => now()]);

            // Load related models based on conversation type
            if ($conversation->product_id) {
                $conversation->load(['product', 'buyer', 'seller']);
            } else {
                $conversation->load(['bundle', 'buyer', 'seller']);
            }

            \Log::info('Conversation loaded successfully', [
                'has_product' => $conversation->product_id !== null,
                'has_bundle' => $conversation->bundle_id !== null,
                'message_count' => $messages->count(),
                'view_name' => 'chat.show'
            ]);

            return view('chat.show', compact('conversation', 'messages'));
        } catch (\Exception $e) {
            \Log::error('Error in showConversation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'conversation_id' => $conversation->id
            ]);
            return redirect()->route('chat.index')->with('error', 'An error occurred while loading the conversation.');
        }
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        try {
            if (!$this->isParticipant($conversation)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'content' => 'required|string|max:1000',
            ]);

            $message = $conversation->messages()->create([
                'sender_id' => Auth::id(),
                'content' => $validated['content'],
            ]);

            $conversation->update(['last_message_at' => now()]);

            $message->load('sender');

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending message:', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'error' => 'Failed to send message. Please try again.'
            ], 500);
        }
    }

    private function isParticipant(Conversation $conversation): bool
    {
        return Auth::id() === $conversation->buyer_id || Auth::id() === $conversation->seller_id;
    }

    public function listConversations()
    {
        $conversations = Conversation::where('buyer_id', Auth::id())
            ->orWhere('seller_id', Auth::id())
            ->with(['product', 'bundle', 'buyer', 'seller', 'messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return view('chat.index', compact('conversations'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function startConversation(Request $request, $id)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            \Log::info('Starting conversation', [
                'product_id' => $id,
                'user_id' => Auth::id()
            ]);

            $product = Product::with('user')->findOrFail($id);
            
            \Log::info('Product found', [
                'product' => $product->toArray()
            ]);

            // Prevent seller from chatting with themselves
            if ($product->user_id === Auth::id()) {
                \Log::warning('User trying to chat with themselves', [
                    'user_id' => Auth::id(),
                    'product_user_id' => $product->user_id
                ]);
                return back()->with('error', 'You cannot start a conversation with yourself.');
            }

            // Find existing conversation or create new one
            $conversation = Conversation::where([
                'seller_id' => $product->user_id,
                'buyer_id' => Auth::id(),
                'product_id' => $product->id
            ])->first();

            \Log::info('Existing conversation check', [
                'found' => $conversation ? true : false,
                'conversation_id' => $conversation ? $conversation->id : null
            ]);

            if (!$conversation) {
                $conversation = Conversation::create([
                    'seller_id' => $product->user_id,
                    'buyer_id' => Auth::id(),
                    'product_id' => $product->id,
                    'last_message_at' => now()
                ]);
                \Log::info('Created new conversation', [
                    'conversation_id' => $conversation->id
                ]);
            }

            \Log::info('Redirecting to chat', [
                'route' => 'chat.show',
                'conversation_id' => $conversation->id
            ]);

            return redirect()->route('chat.show', ['conversation' => $conversation->id]);
        } catch (\Exception $e) {
            \Log::error('Error in startConversation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while starting the conversation.');
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
                'user_id' => Auth::id()
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
                'bundle_id' => $bundle->id
            ])->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'seller_id' => $bundle->user_id,
                    'buyer_id' => Auth::id(),
                    'bundle_id' => $bundle->id,
                    'last_message_at' => now()
                ]);

                \Log::info('Created new bundle conversation', [
                    'conversation_id' => $conversation->id
                ]);
            }

            return redirect()->route('chat.show', ['conversation' => $conversation->id]);

        } catch (\Exception $e) {
            \Log::error('Error in startBundleConversation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while starting the conversation.');
        }
    }

    public function showConversation(Conversation $conversation)
    {
        if (!$this->isParticipant($conversation)) {
            return redirect()->route('home')->with('error', 'You are not authorized to view this conversation.');
        }

        $conversation->load(['messages', 'seller', 'buyer', 'product', 'bundle.categories']);

        return view('chat.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Message store request', [
            'has_file' => $request->hasFile('attachment'),
            'content' => $request->content,
            'conversation_id' => $request->conversation_id,
            'all_data' => $request->all(),
            'files' => $request->allFiles()
        ]);

        try {
            // Validate the request
            $rules = [
                'conversation_id' => 'required|exists:conversations,id',
            ];

            // Only require content if there's no attachment
            if (!$request->hasFile('attachment')) {
                $rules['content'] = 'required|string|max:1000';
            } else {
                $rules['content'] = 'nullable|string|max:1000';
                $rules['attachment'] = 'required|file|max:10240|mimes:jpeg,png,gif,pdf,doc,docx';
            }

            $request->validate($rules);

            $conversation = Conversation::findOrFail($request->conversation_id);
            
            if ($conversation->buyer_id !== auth()->id() && $conversation->seller_id !== auth()->id()) {
                throw new \Exception('Unauthorized');
            }

            $messageData = [
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'content' => $request->content ?? ''
            ];

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                
                \Log::info('Processing file upload', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'error' => $file->getError()
                ]);

                // Ensure the storage directory exists
                $storage_path = storage_path('app/public/chat-attachments');
                if (!file_exists($storage_path)) {
                    mkdir($storage_path, 0755, true);
                }

                // Store the file
                $path = $file->store('chat-attachments', 'public');
                
                if (!$path) {
                    throw new \Exception('Failed to store file');
                }

                $messageData['attachment_path'] = $path;
                $messageData['attachment_type'] = $file->getMimeType();
                
                \Log::info('File uploaded', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'stored_path' => $path,
                    'full_path' => storage_path('app/public/' . $path)
                ]);
            }

            $message = Message::create($messageData);
            $conversation->update(['last_message_at' => now()]);

            \Log::info('Message created', [
                'message_id' => $message->id,
                'has_attachment' => !empty($message->attachment_path),
                'attachment_path' => $message->attachment_path,
                'attachment_type' => $message->attachment_type
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error in store method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function getMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        if ($conversation->buyer_id !== auth()->id() && $conversation->seller_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                $message->attachment_url = $message->attachment_path ? asset('storage/' . $message->attachment_path) : null;
                return $message;
            });

        return response()->json(['messages' => $messages]);
    }

    public function markAllRead($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        if ($conversation->buyer_id !== auth()->id() && $conversation->seller_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark all messages from the other user as read
        $conversation->messages()
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);
        
        // Only mark as read if the current user is the recipient
        if (($message->conversation->buyer_id === auth()->id() || $message->conversation->seller_id === auth()->id()) 
            && $message->sender_id !== auth()->id()
            && !$message->read_at) {
            
            $message->update(['read_at' => now()]);
            broadcast(new MessageRead($message))->toOthers();
        }

        return response()->json(['success' => true]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        if (!$this->isParticipant($conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = new Message([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'content' => $request->message,
            'read_at' => null
        ]);

        $message->save();
        $conversation->update(['last_message_at' => now()]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    private function isParticipant(Conversation $conversation)
    {
        return Auth::id() === $conversation->buyer_id || Auth::id() === $conversation->seller_id;
    }

    public function listConversations()
    {
        $conversations = Conversation::where('buyer_id', Auth::id())
            ->orWhere('seller_id', Auth::id())
            ->with(['seller', 'buyer', 'product'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return view('chat.list', ['conversations' => $conversations]);
    }
}

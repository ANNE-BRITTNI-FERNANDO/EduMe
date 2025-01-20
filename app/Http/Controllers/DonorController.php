<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DonationItem;
use App\Models\DonationRequest;
use App\Models\DonationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DonorController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get received donation requests (where the user is the donor)
        $receivedRequests = DonationRequest::whereHas('donationItem', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['donationItem', 'user'])->orderBy('created_at', 'desc')->get();

        // Get sent donation requests (where the user is the requester)
        $sentRequests = DonationRequest::with(['donationItem', 'donationItem.user'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('donation', [
            'receivedRequests' => $receivedRequests,
            'sentRequests' => $sentRequests
        ]);
    }

    public function history()
    {
        $user = auth()->user();
        
        // Get donations made by the user
        $donations = DonationItem::where('user_id', $user->id)
            ->with(['donationRequests', 'donationRequests.user'])
            ->latest()
            ->get();

        // Get requests sent by the user
        $sentRequests = DonationRequest::where('user_id', $user->id)
            ->with('donationItem')
            ->latest()
            ->get();

        return view('donation-history', compact('donations', 'sentRequests'));
    }

    public function showChat(DonationRequest $request)
    {
        $user = auth()->user();
        
        // Check if user is either the donor or requester
        if ($user->id !== $request->user_id && $user->id !== $request->donationItem->user_id) {
            abort(403, 'Unauthorized access to chat');
        }

        $request->load(['user', 'donationItem', 'donationItem.user']);
        $messages = $request->messages()->with(['sender'])->orderBy('created_at', 'asc')->get();

        // Determine the other user in the conversation
        $otherUser = $user->id === $request->user_id 
            ? $request->donationItem->user 
            : $request->user;

        // Create a conversation object that matches the blade's expectations
        $conversation = new \stdClass();
        $conversation->id = $request->id;
        $conversation->title = "Donation Request: {$request->donationItem->item_name}";
        $conversation->donationRequest = $request;
        $conversation->messages = $messages->map(function($message) {
            return (object)[
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender' => $message->sender,
                'content' => $message->message,
                'created_at' => $message->created_at,
                'read_at' => $message->is_read ? $message->updated_at : null
            ];
        });

        return view('donor.chat', [
            'conversation' => $conversation,
            'otherUser' => $otherUser
        ]);
    }

    public function create()
    {
        return view('donation-form');
    }

    public function available(Request $request)
    {
        $query = DonationItem::where('status', 'approved')
            ->where('available_quantity', '>', 0)
            ->with('user');

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Apply education level filter
        if ($request->filled('education_level')) {
            $query->where('education_level', $request->education_level);
        }

        $donations = $query->latest()->paginate(12);

        // Add locations for the filter
        $locations = [
            'central' => 'Central Region',
            'north' => 'Northern Region',
            'south' => 'Southern Region',
            'east' => 'Eastern Region',
            'west' => 'Western Region'
        ];

        return view('donations.available', compact('donations', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'condition' => 'required|string|in:new,like_new,good,fair',
            'description' => 'required|string',
            'education_level' => 'required|string',
            'category' => 'required|string',
            'contact_number' => 'required|string',
            'pickup_address' => 'required|string',
            'is_anonymous' => 'boolean',
            'show_contact_details' => 'boolean',
            'preferred_contact_method' => 'required|string|in:phone,email',
            'preferred_contact_times' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $donation = new DonationItem();
        $donation->user_id = Auth::id();
        $donation->item_name = $request->item_name;
        $donation->quantity = $request->quantity;
        $donation->available_quantity = $request->quantity;
        $donation->condition = $request->condition;
        $donation->description = $request->description;
        $donation->education_level = $request->education_level;
        $donation->category = $request->category;
        $donation->contact_number = $request->contact_number;
        $donation->pickup_address = $request->pickup_address;
        $donation->is_anonymous = $request->is_anonymous ?? false;
        $donation->show_contact_details = $request->show_contact_details ?? false;
        $donation->preferred_contact_method = $request->preferred_contact_method;
        $donation->preferred_contact_times = $request->preferred_contact_times;
        $donation->status = 'pending';

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('donation-images', 'public');
                $paths[] = $path;
            }
            $donation->images = $paths;
        }

        $donation->save();

        return redirect()->route('donations.index')
            ->with('success', 'Donation submitted successfully! It will be reviewed by our team.');
    }

    public function requestForm(DonationItem $donation)
    {
        // Check if user is trying to request their own donation
        if ($donation->user_id === Auth::id()) {
            return redirect()->route('donations.available')
                ->with('error', 'You cannot request your own donation.');
        }

        // Check if donation is available
        if ($donation->status !== 'approved' || $donation->available_quantity <= 0) {
            return redirect()->route('donations.available')
                ->with('error', 'This donation is no longer available.');
        }

        return view('donations.request', compact('donation'));
    }

    public function storeRequest(Request $request, DonationItem $donation)
    {
        // Check if user is trying to request their own donation
        if ($donation->user_id === Auth::id()) {
            return redirect()->route('donations.available')
                ->with('error', 'You cannot request your own donation.');
        }

        $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $donation->available_quantity
            ],
            'purpose' => 'required|string|min:10',
            'contact_number' => 'required|string',
            'notes' => 'nullable|string',
            'verification_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_type' => 'required|string|in:student_id,parent_nic,school_record,institution_id'
        ]);

        if ($donation->status !== 'approved' || $donation->available_quantity < $request->quantity) {
            return back()->with('error', 'Sorry, this donation is no longer available in the requested quantity.');
        }

        // Store verification document
        $documentPath = $request->file('verification_document')->store('verification-documents', 'public');

        // Store document details in purpose_details as JSON
        $purposeDetails = [
            'document_type' => $request->document_type,
            'document_path' => $documentPath,
            'uploaded_at' => now()->toDateTimeString()
        ];

        $donationRequest = new DonationRequest();
        $donationRequest->user_id = Auth::id();
        $donationRequest->donation_item_id = $donation->id;
        $donationRequest->quantity = $request->quantity;
        $donationRequest->purpose = $request->purpose;
        $donationRequest->purpose_details = json_encode($purposeDetails);
        $donationRequest->contact_number = $request->contact_number;
        $donationRequest->notes = $request->notes;
        $donationRequest->status = 'pending';
        $donationRequest->save();

        // Decrease available quantity
        $donation->available_quantity -= $request->quantity;
        $donation->save();

        return redirect()->route('donations.available')
            ->with('success', 'Your request has been submitted successfully! The donor will be notified.');
    }

    public function approve(DonationRequest $donationRequest)
    {
        // Check if user is authorized to approve this request
        if ($donationRequest->donationItem->user_id !== auth()->id()) {
            return back()->with('error', 'You are not authorized to approve this request.');
        }

        $donationRequest->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);

        return back()->with('success', 'Donation request has been approved.');
    }

    public function reject(DonationRequest $donationRequest)
    {
        // Check if user is authorized to reject this request
        if ($donationRequest->donationItem->user_id !== auth()->id()) {
            return back()->with('error', 'You are not authorized to reject this request.');
        }

        $donationRequest->update([
            'status' => 'rejected',
            'rejected_at' => now()
        ]);

        return back()->with('success', 'Donation request has been rejected.');
    }

    public function destroy(DonationItem $donation)
    {
        // Check if user owns this donation
        if ($donation->user_id !== auth()->id()) {
            return back()->with('error', 'You are not authorized to delete this donation.');
        }

        // Check if donation has any approved requests
        if ($donation->donationRequests()->where('status', 'approved')->exists()) {
            return back()->with('error', 'Cannot delete donation with approved requests.');
        }

        // Delete associated images
        if ($donation->images) {
            $images = is_string($donation->images) ? json_decode($donation->images, true) : $donation->images;
            if (is_array($images)) {
                foreach ($images as $image) {
                    Storage::delete($image);
                }
            }
        }

        // Delete the donation and its pending requests
        $donation->donationRequests()->where('status', 'pending')->delete();
        $donation->delete();

        return back()->with('success', 'Donation deleted successfully.');
    }

    /**
     * Show the details of a specific donation request.
     */
    public function showRequest(DonationRequest $request)
    {
        // Check if the user is authorized to view this request
        if ($request->user_id !== auth()->id() && $request->donationItem->user_id !== auth()->id()) {
            abort(403, 'You are not authorized to view this request.');
        }

        return view('donations.request-details', compact('request'));
    }

    public function sendMessage(Request $request, $donationRequestId)
    {
        $user = auth()->user();
        
        // Find the donation request
        $donationRequest = DonationRequest::with(['user', 'donationItem', 'donationItem.user'])->findOrFail($donationRequestId);
        
        // Check if user is either the donor or requester
        if ($user->id !== $donationRequest->user_id && $user->id !== $donationRequest->donationItem->user_id) {
            abort(403, 'Unauthorized access to chat');
        }

        // Get message from either JSON or form data
        $messageText = $request->input('message');
        if (!$messageText && $request->getContent()) {
            $data = json_decode($request->getContent(), true);
            $messageText = $data['message'] ?? null;
        }

        if (!$messageText) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message is required'
            ], 422);
        }

        // Determine receiver
        $receiverId = $user->id === $donationRequest->user_id 
            ? $donationRequest->donationItem->user_id 
            : $donationRequest->user_id;

        // Create message
        $message = DonationMessage::create([
            'donation_request_id' => $donationRequest->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $messageText,
            'is_read' => false
        ]);

        // Load the sender relationship for the response
        $message->load('sender');

        // Create a message object that matches the blade's expectations
        $messageObj = (object)[
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender' => $message->sender,
            'content' => $message->message,
            'created_at' => $message->created_at,
            'read_at' => null
        ];

        return response()->json([
            'status' => 'success',
            'message' => $messageObj
        ]);
    }

    public function getNewMessages(DonationRequest $request, $lastMessageId)
    {
        $user = auth()->user();
        
        // Check if user is either the donor or requester
        if ($user->id !== $request->user_id && $user->id !== $request->donationItem->user_id) {
            abort(403, 'Unauthorized access to chat');
        }

        $newMessages = $request->messages()
            ->with(['sender'])
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($message) {
                return (object)[
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender' => $message->sender,
                    'content' => $message->message,
                    'created_at' => $message->created_at,
                    'read_at' => $message->is_read ? $message->updated_at : null
                ];
            });

        return response()->json([
            'messages' => $newMessages
        ]);
    }
}

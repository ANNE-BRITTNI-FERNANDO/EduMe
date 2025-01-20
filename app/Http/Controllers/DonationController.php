<?php

namespace App\Http\Controllers;

use App\Models\DonationItem;
use App\Models\DonationRequest;
use App\Models\MonetaryDonation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DonationController extends Controller
{
    /**
     * Display the main donations page.
     */
    public function index()
    {
        $pendingRequests = DonationRequest::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with(['donationItem', 'donationItem.user'])
            ->latest()
            ->get();

        $approvedRequests = DonationRequest::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->with(['donationItem', 'donationItem.user', 'approvedBy'])
            ->latest()
            ->get();

        $rejectedRequests = DonationRequest::where('user_id', Auth::id())
            ->where('status', 'rejected')
            ->with(['donationItem', 'donationItem.user', 'rejectedBy'])
            ->latest()
            ->get();

        return view('donation', compact('pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    /**
     * Show donation history.
     */
    public function history()
    {
        $itemDonations = DonationItem::where('user_id', Auth::id())
            ->latest()
            ->paginate(12);

        return view('donation-history', compact('itemDonations'));
    }

    /**
     * Show donation history.
     */
    public function historyOld()
    {
        $donations = DonationItem::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $requests = DonationRequest::where('user_id', Auth::id())
            ->with('donationItem')
            ->latest()
            ->paginate(10);

        return view('donations.history', compact('donations', 'requests'));
    }

    /**
     * Display available donations.
     */
    public function available(Request $request)
    {
        try {
            $query = DonationItem::where('status', 'approved')
                ->where('available_quantity', '>', 0)
                ->with('user'); // Eager load user data for location info

            // Apply filters
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('education_level')) {
                $query->where('education_level', $request->education_level);
            }
            if ($request->filled('condition')) {
                $query->where('condition', $request->condition);
            }
            if ($request->filled('location')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('location', $request->location);
                });
            }

            // Get unique locations from users who have active donations
            $locationsQuery = DB::table('users')
                ->join('donation_items', 'users.id', '=', 'donation_items.user_id')
                ->where('donation_items.status', 'approved')
                ->where('donation_items.available_quantity', '>', 0)
                ->where('users.location', '!=', 'Not specified')
                ->select('users.location')
                ->distinct();

            $locations = $locationsQuery->pluck('location')->sort()->values()->toArray();

            $donations = $query->latest()->paginate(12);
            
            // Format education levels for display
            $donations->getCollection()->transform(function ($donation) {
                $donation->education_level = $this->formatEducationLevel($donation->education_level);
                return $donation;
            });
            
            // Maintain filter parameters in pagination
            $donations->appends($request->all());

            return view('donations.available', [
                'donations' => $donations,
                'locations' => $locations ?? [],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in available donations: ' . $e->getMessage());
            return view('donations.available', [
                'donations' => collect([]),
                'locations' => [],
            ])->with('error', 'An error occurred while loading donations. Please try again.');
        }
    }

    private function formatEducationLevel($level)
    {
        $levels = [
            'primary' => 'Primary School',
            'secondary' => 'Secondary School',
            'higher_secondary' => 'Higher Secondary',
            'undergraduate' => 'Undergraduate',
            'postgraduate' => 'Postgraduate'
        ];

        return $levels[$level] ?? ucfirst(str_replace('_', ' ', $level));
    }

    public function availableUpdated(Request $request)
    {
        try {
            $query = DonationItem::where('status', 'approved')
                ->where('available_quantity', '>', 0)
                ->with('user'); // Eager load user data for location info

            // Apply filters
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('education_level')) {
                $query->where('education_level', $request->education_level);
            }
            if ($request->filled('condition')) {
                $query->where('condition', $request->condition);
            }
            if ($request->filled('location')) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('location', $request->location);
                });
            }

            // Get unique locations from users who have active donations
            $locationsQuery = DB::table('users')
                ->join('donation_items', 'users.id', '=', 'donation_items.user_id')
                ->where('donation_items.status', 'approved')
                ->where('donation_items.available_quantity', '>', 0)
                ->where('users.location', '!=', 'Not specified')
                ->select('users.location')
                ->distinct();

            $locations = $locationsQuery->pluck('location')->sort()->values()->toArray();

            $donations = $query->latest()->paginate(12);
            
            // Format education levels for display
            $donations->getCollection()->transform(function ($donation) {
                $donation->education_level = $this->formatEducationLevel($donation->education_level);
                return $donation;
            });
            
            // Maintain filter parameters in pagination
            $donations->appends($request->all());

            return view('donations.available', [
                'donations' => $donations,
                'locations' => $locations ?? [],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in available donations: ' . $e->getMessage());
            return view('donations.available', [
                'donations' => collect([]),
                'locations' => [],
            ])->with('error', 'An error occurred while loading donations. Please try again.');
        }
    }

    /**
     * Show the form for creating a new donation.
     */
    public function create()
    {
        return view('donations.items.create');
    }

    /**
     * Store a newly created donation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:textbooks,stationery,devices,other',
            'education_level' => 'required|in:primary,secondary,tertiary',
            'condition' => 'required|in:new,like_new,good,fair',
            'quantity' => 'required|integer|min:1',
            'description' => 'required|string',
            'contact_number' => 'required|string',
            'preferred_contact_method' => 'required|in:phone,email,both',
            'preferred_contact_times' => 'required|array',
            'preferred_contact_times.*' => 'required|in:morning,afternoon,evening',
            'is_anonymous' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            // Create donation item with basic data
            $donationData = [
                'user_id' => Auth::id(),
                'item_name' => $validated['item_name'],
                'category' => $validated['category'],
                'education_level' => $validated['education_level'],
                'condition' => $validated['condition'],
                'quantity' => $validated['quantity'], // Set both quantity and available_quantity
                'available_quantity' => $validated['quantity'],
                'description' => $validated['description'],
                'contact_number' => $validated['contact_number'],
                'preferred_contact_method' => $validated['preferred_contact_method'],
                'preferred_contact_times' => $validated['preferred_contact_times'],
                'is_anonymous' => $request->has('is_anonymous'),
                'status' => 'pending'
            ];

            // Handle image uploads
            if ($request->hasFile('images')) {
                $uploadedImages = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('donations', 'public');
                    $uploadedImages[] = $path;
                }
                $donationData['images'] = $uploadedImages;
            }

            // Create the donation item
            $donationItem = DonationItem::create($donationData);

            // Log success
            Log::info('Donation item created successfully', ['id' => $donationItem->id]);

            return redirect()->route('donations.available')
                ->with('success', 'Your donation has been submitted and is pending approval. Thank you for your generosity!');
        } catch (\Exception $e) {
            // Log error with detailed information
            Log::error('Error creating donation item', [
                'error' => $e->getMessage(),
                'data' => $validated,
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'There was an error submitting your donation. Please try again.');
        }
    }

    /**
     * Show donation request form.
     */
    public function showRequestForm(DonationItem $donation)
    {
        return view('donations.request', compact('donation'));
    }

    /**
     * Store a donation request.
     */
    public function storeRequest(Request $request, DonationItem $donation)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $donation->available_quantity,
            'purpose' => 'required|string',
            'preferred_contact_time' => 'required|in:morning,afternoon,evening',
            'contact_message' => 'nullable|string'
        ]);

        $donationRequest = new DonationRequest();
        $donationRequest->donation_item_id = $donation->id;
        $donationRequest->user_id = Auth::id();
        $donationRequest->quantity = $validated['quantity'];
        $donationRequest->purpose = $validated['purpose'];
        $donationRequest->preferred_contact_time = $validated['preferred_contact_time'];
        $donationRequest->status = 'pending';
        $donationRequest->save();

        return redirect()->route('donations.index')
            ->with('success', 'Your donation request has been submitted.');
    }

    /**
     * Show the form for creating a new donation request.
     */
    public function createRequest($type, $id)
    {
        if ($type === 'item') {
            $donation = DonationItem::findOrFail($id);
            
            if ($donation->user_id === auth()->id()) {
                return redirect()->route('donations.available')
                    ->with('error', 'You cannot request your own donation item.');
            }
        } elseif ($type === 'monetary') {
            $donation = MonetaryDonation::findOrFail($id);
            
            if ($donation->user_id === auth()->id()) {
                return redirect()->route('donations.monetary.index')
                    ->with('error', 'You cannot request your own monetary donation.');
            }
        } else {
            abort(404);
        }

        return view('donations.request', compact('donation', 'type'));
    }

    /**
     * Store a newly created donation request in storage.
     */
    public function storeNewRequest(Request $request)
    {
        $type = $request->input('donation_type');
        $id = $request->input('donation_id');

        if ($type === 'item') {
            $donation = DonationItem::findOrFail($id);
            
            if ($donation->user_id === auth()->id()) {
                return back()->with('error', 'You cannot request your own donation item.');
            }

            if ($donation->status !== 'available') {
                return back()->with('error', 'This donation item is not available for requests.');
            }
        } elseif ($type === 'monetary') {
            $donation = MonetaryDonation::findOrFail($id);
            
            if ($donation->user_id === auth()->id()) {
                return back()->with('error', 'You cannot request your own monetary donation.');
            }

            if ($donation->status !== 'available') {
                return back()->with('error', 'This monetary donation is not available for requests.');
            }
        } else {
            abort(404);
        }

        try {
            DB::beginTransaction();

            $donationRequest = DonationRequest::create([
                'user_id' => auth()->id(),
                'donation_type' => $type,
                'donation_id' => $id,
                'status' => 'pending',
                'reason' => $request->input('reason'),
                'contact_number' => $request->input('contact_number'),
                'preferred_contact_method' => $request->input('preferred_contact_method'),
            ]);

            DB::commit();
            return redirect()->route('donations.requests')->with('success', 'Your donation request has been submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating donation request: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while submitting your request. Please try again.');
        }
    }

    /**
     * Display pending donations for admin approval.
     */
    public function pendingApprovals()
    {
        $donations = DonationItem::where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('admin.donations.pending', compact('donations'));
    }

    /**
     * Approve a donation.
     */
    public function approve(DonationItem $donation)
    {
        $donation->status = 'approved';
        $donation->approved_at = now();
        $donation->save();

        // Notify the donor
        $donation->user->notify(new \App\Notifications\DonationApproved($donation));

        return back()->with('success', 'Donation has been approved.');
    }

    /**
     * Reject a donation.
     */
    public function reject(Request $request, DonationItem $donation)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $donation->status = 'rejected';
        $donation->rejection_reason = $validated['rejection_reason'];
        $donation->save();

        // Notify the donor
        $donation->user->notify(new \App\Notifications\DonationRejected($donation));

        return back()->with('success', 'Donation has been rejected.');
    }

    /**
     * Display all donations for admin.
     */
    public function adminIndex()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // Get pending donations
        $pendingDonations = DonationItem::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        // Get approved donations (including those marked as 'available')
        $approvedDonations = DonationItem::where('status', 'approved')
            ->with('user')
            ->latest()
            ->get();

        // Get rejected donations
        $rejectedDonations = DonationItem::where('status', 'rejected')
            ->with('user')
            ->latest()
            ->get();

        // Debug information
        \Log::info('Donation counts:', [
            'pending' => $pendingDonations->count(),
            'approved' => $approvedDonations->count(),
            'rejected' => $rejectedDonations->count()
        ]);

        return view('admin.donations.index', [
            'pendingDonations' => $pendingDonations,
            'approvedDonations' => $approvedDonations,
            'rejectedDonations' => $rejectedDonations,
        ]);
    }

    /**
     * Show specific donation for admin.
     */
    public function adminShow(DonationItem $donation)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.donations.show', compact('donation'));
    }

    /**
     * Approve a donation.
     */
    public function adminApprove(DonationItem $donation)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $donation->update(['status' => 'approved']);

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donation approved successfully');
    }

    /**
     * Reject a donation.
     */
    public function adminReject(DonationItem $donation)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $donation->update(['status' => 'rejected']);

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donation rejected successfully');
    }

    /**
     * Delete a donation.
     */
    public function delete(DonationItem $donation)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // Delete associated images
        if ($donation->images) {
            foreach ($donation->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $donation->delete();

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donation deleted successfully');
    }

    /**
     * Update contact sharing preference.
     */
    public function updateSharing(Request $request, DonationRequest $donation)
    {
        $donation->update([
            'show_contact_details' => $request->has('show_contact_details')
        ]);
        
        return back()->with('success', 'Contact sharing preferences updated successfully.');
    }
}
